<?php

namespace App\Services\Crm;

use App\Models\Contact;
use App\Models\Customer;
use App\Models\Export;
use App\Models\FailedImportRow;
use App\Models\Import;
use App\Models\Lead;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductSku;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DataPortService
{
    /**
     * @return array<string, string>
     */
    public static function modules(): array
    {
        return [
            'leads' => '线索',
            'customers' => '客户',
            'contacts' => '联系人',
            'products' => '商品',
            'product_skus' => 'SKU',
            'orders' => '订单',
            'payments' => '收款',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function importModules(): array
    {
        return array_intersect_key(self::modules(), array_flip(['leads', 'customers', 'contacts', 'products', 'product_skus']));
    }

    /**
     * @return array<string, string>
     */
    public static function exportModules(): array
    {
        return self::modules();
    }

    public function import(int $tenantId, User $user, string $module, string $path): Import
    {
        app(PlanLimitService::class)->assertCanImport(Tenant::findOrFail($tenantId));

        $import = Import::create([
            'tenant_id' => $tenantId,
            'file_name' => basename($path),
            'file_path' => $path,
            'importer' => $module,
            'processed_rows' => 0,
            'total_rows' => 0,
            'successful_rows' => 0,
            'user_id' => $user->id,
        ]);

        $stream = Storage::disk('local')->readStream($path);
        $headers = null;
        $rowNumber = 0;

        while (($row = fgetcsv($stream)) !== false) {
            $rowNumber++;

            if ($headers === null) {
                $headers = array_map(fn (string $header): string => trim($header), $row);

                continue;
            }

            $data = array_combine($headers, array_pad($row, count($headers), null));
            $import->increment('processed_rows');
            $import->increment('total_rows');

            try {
                $this->storeRow($tenantId, $module, $data ?: []);
                $import->increment('successful_rows');
            } catch (Throwable $exception) {
                FailedImportRow::create([
                    'tenant_id' => $tenantId,
                    'import_id' => $import->id,
                    'data' => $data ?: [],
                    'validation_error' => '第 '.$rowNumber.' 行：'.$exception->getMessage(),
                ]);
            }
        }

        if (is_resource($stream)) {
            fclose($stream);
        }

        $import->forceFill(['completed_at' => now()])->save();

        app(NotificationService::class)->send(
            $tenantId,
            $user->id,
            'import_completed',
            '导入完成',
            '成功 '.$import->successful_rows.' 行，失败 '.($import->processed_rows - $import->successful_rows).' 行。',
            $import,
        );

        app(AuditLogService::class)->record('data_imported', $import, null, [
            'module' => $module,
            'processed_rows' => $import->processed_rows,
            'successful_rows' => $import->successful_rows,
        ]);

        return $import->refresh();
    }

    public function export(int $tenantId, User $user, string $module): Export
    {
        app(PlanLimitService::class)->assertCanExport(Tenant::findOrFail($tenantId));

        $rows = $this->exportRows($tenantId, $module);
        $fileName = $module.'-'.now()->format('YmdHis').'.csv';
        $path = 'tenants/'.$tenantId.'/exports/'.$fileName;

        $handle = fopen('php://temp', 'w+');

        if ($rows !== []) {
            fputcsv($handle, array_keys($rows[0]));

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
        }

        rewind($handle);
        Storage::disk('local')->put($path, stream_get_contents($handle));
        fclose($handle);

        $export = Export::create([
            'tenant_id' => $tenantId,
            'file_disk' => 'local',
            'file_name' => $path,
            'exporter' => $module,
            'processed_rows' => count($rows),
            'total_rows' => count($rows),
            'successful_rows' => count($rows),
            'user_id' => $user->id,
            'completed_at' => now(),
        ]);

        app(NotificationService::class)->send(
            $tenantId,
            $user->id,
            'export_completed',
            '导出完成',
            "已生成 {$export->file_name}，共 {$export->total_rows} 行。",
            $export,
        );

        app(AuditLogService::class)->record('data_exported', $export, null, [
            'module' => $module,
            'total_rows' => $export->total_rows,
        ]);

        return $export;
    }

    public function retryFailedRow(FailedImportRow $row, User $user): bool
    {
        $import = $row->import;

        try {
            $this->storeRow($row->tenant_id, $import->importer, $row->data ?: []);
            $import->increment('successful_rows');
            $row->delete();

            app(AuditLogService::class)->record('failed_import_row_retried', $import, [
                'failed_row_id' => $row->id,
            ], [
                'status' => 'success',
                'user_id' => $user->id,
            ]);

            return true;
        } catch (Throwable $exception) {
            $row->forceFill([
                'validation_error' => '重试失败：'.$exception->getMessage(),
            ])->save();

            return false;
        }
    }

    /**
     * @return array<int, string>
     */
    public function templateHeaders(string $module): array
    {
        return match ($module) {
            'leads' => ['公司名称', '联系人', '电话', '邮箱', '来源'],
            'customers' => ['客户名称', '电话', '邮箱', '来源'],
            'contacts' => ['客户名称', '联系人', '电话', '邮箱', '职位', '部门'],
            'products' => ['商品分组', '商品名称', '税率'],
            'product_skus' => ['商品名称', 'SKU编码', '价格', '成本', '库存'],
            default => [],
        };
    }

    public function createTemplate(int $tenantId, string $module): string
    {
        $path = 'tenants/'.$tenantId.'/imports/templates/'.$module.'-template.csv';
        $handle = fopen('php://temp', 'w+');

        fputcsv($handle, $this->templateHeaders($module));
        rewind($handle);
        Storage::disk('local')->put($path, stream_get_contents($handle));
        fclose($handle);

        return $path;
    }

    /**
     * @return array{headers: array<int, string>, total_rows: int, missing_headers: array<int, string>}
     */
    public function precheck(string $module, string $path): array
    {
        $stream = Storage::disk('local')->readStream($path);
        $headers = [];
        $totalRows = 0;

        while (($row = fgetcsv($stream)) !== false) {
            if ($headers === []) {
                $headers = array_map(fn (string $header): string => trim($header), $row);

                continue;
            }

            $totalRows++;
        }

        if (is_resource($stream)) {
            fclose($stream);
        }

        return [
            'headers' => $headers,
            'total_rows' => $totalRows,
            'missing_headers' => array_values(array_diff($this->templateHeaders($module), $headers)),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function storeRow(int $tenantId, string $module, array $data): Model
    {
        return match ($module) {
            'leads' => $this->storeLead($tenantId, $data),
            'customers' => Customer::create([
                'tenant_id' => $tenantId,
                'name' => $data['name'] ?? $data['客户名称'] ?? throw new \InvalidArgumentException('客户名称不能为空'),
                'customer_type' => $data['customer_type'] ?? 'company',
                'lifecycle_stage' => $data['lifecycle_stage'] ?? 'new',
                'phone' => $data['phone'] ?? $data['电话'] ?? null,
                'email' => $data['email'] ?? $data['邮箱'] ?? null,
                'source' => $data['source'] ?? $data['来源'] ?? '导入',
            ]),
            'contacts' => Contact::create([
                'tenant_id' => $tenantId,
                'customer_id' => $this->customerId($tenantId, $data),
                'name' => $data['name'] ?? $data['联系人'] ?? throw new \InvalidArgumentException('联系人不能为空'),
                'phone' => $data['phone'] ?? $data['电话'] ?? null,
                'email' => $data['email'] ?? $data['邮箱'] ?? null,
                'position' => $data['position'] ?? $data['职位'] ?? null,
                'department' => $data['department'] ?? $data['部门'] ?? null,
            ]),
            'products' => Product::create([
                'tenant_id' => $tenantId,
                'group_id' => $this->productGroupId($tenantId, $data),
                'name' => $data['name'] ?? $data['商品名称'] ?? throw new \InvalidArgumentException('商品名称不能为空'),
                'tax_rate' => $data['tax_rate'] ?? $data['税率'] ?? 0,
                'is_on_sale' => true,
            ]),
            'product_skus' => ProductSku::create([
                'tenant_id' => $tenantId,
                'product_id' => $this->productId($tenantId, $data),
                'sku_code' => $data['sku_code'] ?? $data['SKU编码'] ?? throw new \InvalidArgumentException('SKU编码不能为空'),
                'price' => $data['price'] ?? $data['价格'] ?? 0,
                'cost_price' => $data['cost_price'] ?? $data['成本'] ?? 0,
                'stock' => $data['stock'] ?? $data['库存'] ?? 0,
                'is_active' => true,
            ]),
            default => throw new \InvalidArgumentException('暂不支持该模块导入'),
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function storeLead(int $tenantId, array $data): Lead
    {
        $companyName = $data['company_name'] ?? $data['公司名称'] ?? null;
        $contactName = $data['contact_name'] ?? $data['联系人'] ?? null;
        $phone = $data['phone'] ?? $data['电话'] ?? null;
        $email = $data['email'] ?? $data['邮箱'] ?? null;

        if (blank($companyName) && blank($contactName) && blank($phone) && blank($email)) {
            throw new \InvalidArgumentException('线索信息不能为空');
        }

        return Lead::create([
            'tenant_id' => $tenantId,
            'company_name' => $companyName,
            'contact_name' => $contactName,
            'phone' => $phone,
            'email' => $email,
            'source' => $data['source'] ?? $data['来源'] ?? '导入',
            'status' => $data['status'] ?? 'unassigned',
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function exportRows(int $tenantId, string $module): array
    {
        return match ($module) {
            'leads' => Lead::where('tenant_id', $tenantId)->get(['company_name', 'contact_name', 'phone', 'email', 'source', 'status'])->toArray(),
            'customers' => Customer::where('tenant_id', $tenantId)->get(['name', 'customer_type', 'lifecycle_stage', 'phone', 'email', 'source'])->toArray(),
            'contacts' => Contact::where('tenant_id', $tenantId)->get(['customer_id', 'name', 'phone', 'email', 'position', 'department'])->toArray(),
            'products' => Product::where('tenant_id', $tenantId)->get(['group_id', 'name', 'tax_rate', 'is_on_sale'])->toArray(),
            'product_skus' => ProductSku::where('tenant_id', $tenantId)->get(['product_id', 'sku_code', 'price', 'cost_price', 'stock', 'is_active'])->toArray(),
            'orders' => Order::where('tenant_id', $tenantId)->get(['order_number', 'customer_id', 'total_amount', 'payment_status', 'order_status'])->toArray(),
            'payments' => Payment::where('tenant_id', $tenantId)->get(['order_id', 'amount', 'status', 'payment_method', 'received_at'])->toArray(),
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function customerId(int $tenantId, array $data): int
    {
        if (! empty($data['customer_id'])) {
            return (int) $data['customer_id'];
        }

        $name = $data['customer_name'] ?? $data['客户名称'] ?? null;

        return Customer::where('tenant_id', $tenantId)->where('name', $name)->value('id')
            ?? throw new \InvalidArgumentException('找不到所属客户');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function productGroupId(int $tenantId, array $data): ?int
    {
        $name = $data['group_name'] ?? $data['商品分组'] ?? null;

        if (! $name) {
            return null;
        }

        return ProductGroup::firstOrCreate([
            'tenant_id' => $tenantId,
            'name' => $name,
        ])->id;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function productId(int $tenantId, array $data): int
    {
        if (! empty($data['product_id'])) {
            return (int) $data['product_id'];
        }

        $name = $data['product_name'] ?? $data['商品名称'] ?? null;

        return Product::where('tenant_id', $tenantId)->where('name', $name)->value('id')
            ?? throw new \InvalidArgumentException('找不到所属商品');
    }
}
