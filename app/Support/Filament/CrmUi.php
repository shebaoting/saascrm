<?php

namespace App\Support\Filament;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CrmUi
{
    public static function configureComponents(): void
    {
        foreach ([
            TextInput::class,
            Textarea::class,
            Select::class,
            DatePicker::class,
            DateTimePicker::class,
            Toggle::class,
            Checkbox::class,
            CheckboxList::class,
            TagsInput::class,
            KeyValue::class,
            FileUpload::class,
            RichEditor::class,
        ] as $component) {
            $component::configureUsing(function ($field): void {
                $field->label(static::label($field->getName()));

                if ($field instanceof Select) {
                    $field
                        ->native(false)
                        ->searchable()
                        ->optionsLimit(50);
                }
            });
        }

        foreach ([TextEntry::class, IconEntry::class] as $component) {
            $component::configureUsing(function ($entry): void {
                $entry->label(static::label($entry->getName()));

                if ($entry instanceof TextEntry) {
                    $name = $entry->getName();

                    $entry->placeholder('-');

                    if (static::hasValueLabels($name)) {
                        $entry->formatStateUsing(fn ($state, ?Model $record = null) => static::valueLabel($name, $state, $record));
                    }
                }
            });
        }

        foreach ([TextColumn::class, IconColumn::class] as $component) {
            $component::configureUsing(function ($column): void {
                $column->label(static::label($column->getName()));

                if ($column instanceof TextColumn) {
                    $name = $column->getName();

                    $column->placeholder('-');

                    if (static::hasValueLabels($name)) {
                        $column->formatStateUsing(fn ($state, ?Model $record = null) => static::valueLabel($name, $state, $record));
                    }
                }
            });
        }

        SelectFilter::configureUsing(fn (SelectFilter $filter): SelectFilter => $filter->label(static::label($filter->getName())));
        TrashedFilter::configureUsing(fn (TrashedFilter $filter): TrashedFilter => $filter->label('删除状态'));
    }

    public static function label(string $name): string
    {
        $labels = [
            'accepted_at' => '接受时间',
            'action' => '操作',
            'action_type' => '动作类型',
            'address' => '地址',
            'amount' => '金额',
            'annual_revenue' => '年营收',
            'approval_comment' => '审批意见',
            'approved_at' => '审批时间',
            'approved_by' => '审批人',
            'approvedBy.name' => '审批人',
            'approver.name' => '审批人',
            'approver_id' => '审批人',
            'area_id' => '地区编码',
            'assignee.name' => '负责人',
            'assignee_id' => '负责人',
            'author.name' => '作者',
            'automation_rule_id' => '自动化规则',
            'avatar' => '头像',
            'billing_cycle' => '计费周期',
            'cancelled_at' => '取消时间',
            'category' => '分类',
            'category.name' => '分类',
            'category_contract' => '合同',
            'category_payment_voucher' => '收款凭证',
            'category_expense_voucher' => '支出凭证',
            'closed_amount' => '成交金额',
            'closed_at' => '成交时间',
            'code' => '编码',
            'company_name' => '公司名称',
            'company_size' => '公司规模',
            'completed_at' => '完成时间',
            'conditions' => '条件',
            'contact.name' => '联系人',
            'contact_email' => '联系邮箱',
            'contact_id' => '联系人',
            'contact_name' => '联系人',
            'contact_phone' => '联系电话',
            'content' => '内容',
            'convertedCustomer.name' => '已转客户',
            'converted_at' => '转化时间',
            'converted_by' => '转化人',
            'convertedBy.name' => '转化人',
            'converted_customer_id' => '已转客户',
            'cost_price' => '成本价',
            'country_code' => '国家/地区',
            'created_at' => '创建时间',
            'created_by' => '创建人',
            'creator.name' => '创建人',
            'creator_id' => '创建人',
            'currency' => '币种',
            'custom_fields' => '自定义字段',
            'custom_department_ids' => '自定义部门',
            'custom_user_ids' => '自定义人员',
            'customer.name' => '客户',
            'customer_id' => '客户',
            'customer_level' => '客户等级',
            'customer_type' => '客户类型',
            'data' => '数据',
            'data_scope' => '数据范围',
            'deleted_at' => '删除时间',
            'department' => '部门',
            'department.name' => '部门',
            'department_id' => '部门',
            'department_ids' => '部门',
            'description' => '描述',
            'details' => '详情',
            'direction' => '方向',
            'discount_amount' => '优惠金额',
            'disk' => '存储盘',
            'duplicate' => '疑似重复',
            'due_at' => '截止时间',
            'email' => '邮箱',
            'email_verified_at' => '邮箱验证时间',
            'employee.name' => '销售人员',
            'employee_id' => '销售人员',
            'ended_at' => '结束时间',
            'ends_at' => '结束时间',
            'expected_close_date' => '预计成交日期',
            'expense_date' => '支出日期',
            'expires_at' => '过期时间',
            'exporter' => '导出器',
            'extension' => '扩展名',
            'features' => '功能',
            'field' => '字段',
            'field_name' => '重复字段',
            'field_value' => '重复值',
            'file_disk' => '文件存储',
            'file_name' => '文件名',
            'file_path' => '文件路径',
            'first_order_at' => '首次下单时间',
            'forecast_category' => '预测分类',
            'fromUser.name' => '原负责人',
            'from_user_id' => '原负责人',
            'gender' => '性别',
            'gross_profit' => '毛利',
            'group' => '分组',
            'group.name' => '分组',
            'group_id' => '分组',
            'group_name' => '分组名称',
            'guard_name' => '认证守卫',
            'id' => 'ID',
            'identifier' => '标识',
            'import.file_name' => '导入任务',
            'import.id' => '导入任务',
            'import_id' => '导入任务',
            'importer' => '导入器',
            'inactive_days' => '未跟进天数',
            'industry' => '行业',
            'internal_notes' => '内部备注',
            'invalid_reason' => '无效原因',
            'invalid_remarks' => '无效备注',
            'invited_by' => '邀请人',
            'inviter.name' => '邀请人',
            'ip_address' => 'IP 地址',
            'is_active' => '启用',
            'is_default' => '默认',
            'is_filterable' => '允许筛选',
            'is_list_visible' => '列表显示',
            'is_on_sale' => '上架',
            'is_platform_admin' => '平台管理员',
            'is_primary' => '主要联系人',
            'is_required' => '必填',
            'is_show_in_tracking' => '跟进显示',
            'is_visible' => '可见',
            'kb_category_id' => '知识库分类',
            'key' => '键',
            'label' => '显示名',
            'last_activity_at' => '最后跟进时间',
            'last_login_at' => '最后登录时间',
            'last_order_at' => '最近下单时间',
            'last_run_at' => '最后运行时间',
            'layout' => '布局',
            'lead.company_name' => '线索',
            'lead.id' => '线索',
            'lead_id' => '线索',
            'lifecycle_stage' => '生命周期',
            'locale' => '语言',
            'logo_path' => 'Logo',
            'lost_reason' => '丢单原因',
            'lost_remarks' => '丢单备注',
            'max_automation_rules' => '自动化规则上限',
            'max_claim_daily' => '每日领取上限',
            'max_custom_fields' => '自定义字段上限',
            'max_customers' => '客户上限',
            'max_leads' => '线索上限',
            'max_per_user_daily' => '人均每日上限',
            'max_storage_mb' => '存储上限(MB)',
            'max_users' => '用户上限',
            'merged_by' => '合并人',
            'mergedBy.name' => '合并人',
            'merged_fields' => '合并字段',
            'merged_relations' => '合并关联',
            'metadata' => '元数据',
            'method' => '分配方式',
            'mime_type' => '文件类型',
            'min_price' => '最低价',
            'matched_id' => '匹配记录 ID',
            'matched_type' => '匹配对象',
            'model_id' => '记录 ID',
            'model_type' => '业务对象',
            'name' => '名称',
            'new_values' => '新值',
            'next_activity_at' => '下次跟进时间',
            'next_follow_at' => '下次跟进时间',
            'notes' => '备注',
            'notifiable_id' => '通知对象 ID',
            'notifiable_type' => '通知对象类型',
            'occurred_at' => '发生时间',
            'old_values' => '旧值',
            'operated_by' => '操作人',
            'operator' => '条件',
            'operatorUser.name' => '操作人',
            'opportunity.name' => '商机',
            'opportunity_id' => '商机',
            'opportunity_name' => '商机名称',
            'order.id' => '订单',
            'order.order_number' => '订单',
            'order_id' => '订单',
            'order_number' => '订单编号',
            'order_source' => '订单来源',
            'order_status' => '订单状态',
            'ordered_at' => '下单时间',
            'original_price' => '原价',
            'outcome' => '跟进结果',
            'owner.name' => '负责人',
            'owner_user_id' => '负责人',
            'parent.name' => '上级',
            'parent_id' => '上级',
            'password' => '密码',
            'path' => '路径',
            'payload' => '动作参数',
            'payment_method' => '收款方式',
            'payment_plan_id' => '收款计划',
            'payment_status' => '收款状态',
            'pdf_path' => 'PDF 文件',
            'period_end' => '周期结束',
            'period_start' => '周期开始',
            'period_type' => '周期类型',
            'permissions' => '权限',
            'phone' => '电话',
            'pipeline.name' => '销售管道',
            'pipeline_id' => '销售管道',
            'pipeline_stage_id' => '销售阶段',
            'plan.name' => '套餐',
            'plan_date' => '计划收款日期',
            'plan_amount' => '计划金额',
            'plan_id' => '套餐',
            'pool_entered_at' => '进入公海时间',
            'position' => '职位',
            'price' => '价格',
            'priceBook.name' => '价格表',
            'price_book_id' => '价格表',
            'price_monthly' => '月付价格',
            'price_yearly' => '年付价格',
            'priority' => '优先级',
            'probability' => '赢单概率',
            'processed_rows' => '已处理行数',
            'product.name' => '商品',
            'product_id' => '商品',
            'product_name' => '商品名称',
            'product_sku_id' => 'SKU',
            'profit_margin' => '毛利率',
            'protect_days' => '保护天数',
            'published_at' => '发布时间',
            'qualification_status' => '资格状态',
            'quantity' => '数量',
            'quote.title' => '报价单',
            'quote_id' => '报价单',
            'quote_number' => '报价编号',
            'read_at' => '已读时间',
            'reason' => '原因',
            'received_at' => '收款时间',
            'received_amount' => '已收金额',
            'registered_address' => '注册地址',
            'rejected_at' => '拒绝时间',
            'requested_at' => '申请时间',
            'requested_by' => '申请人',
            'requester.name' => '申请人',
            'required_fields' => '必填字段',
            'resolved_at' => '处理时间',
            'resolved_by' => '处理人',
            'responsible.name' => '负责人',
            'responsible_user_id' => '负责人',
            'role.name' => '角色',
            'role_id' => '角色',
            'role_ids' => '角色',
            'rule.name' => '自动化规则',
            'score' => '分数',
            'settings' => '设置',
            'short_name' => '简称',
            'size' => '大小',
            'sku.sku_code' => 'SKU',
            'sku_code' => 'SKU 编码',
            'slug' => '访问标识',
            'sort_order' => '排序',
            'source' => '来源',
            'source_id' => '来源记录',
            'sourceQuote.quote_number' => '源报价',
            'source_quote_id' => '源报价',
            'specifications' => '规格',
            'stage.name' => '销售阶段',
            'stage_type' => '阶段类型',
            'start_at' => '开始时间',
            'starts_at' => '开始时间',
            'status' => '状态',
            'stock' => '库存',
            'subject' => '主题',
            'subtotal_amount' => '小计',
            'successful_rows' => '成功行数',
            'tags' => '标签',
            'target_amount' => '目标金额',
            'target_customer_count' => '目标客户数',
            'target_id' => '目标对象',
            'target_payment_amount' => '回款目标',
            'target_type' => '目标类型',
            'tax_rate' => '税率',
            'telephone' => '座机',
            'tenant.name' => '租户',
            'tenant_id' => '租户',
            'timezone' => '时区',
            'title' => '标题',
            'toUser.name' => '新负责人',
            'to_user_id' => '新负责人',
            'token' => '令牌',
            'total_amount' => '总金额',
            'total_cost' => '总成本',
            'total_profit' => '总利润',
            'total_rows' => '总行数',
            'total_tax' => '税额',
            'transaction_no' => '交易号',
            'trial_ends_at' => '试用结束时间',
            'trigger_type' => '触发类型',
            'type' => '类型',
            'unit_price' => '单价',
            'updated_at' => '更新时间',
            'updated_by' => '更新人',
            'user.name' => '用户',
            'user_agent' => 'User-Agent',
            'user_id' => '用户',
            'user_ids' => '用户',
            'valid_until' => '有效期至',
            'validation_error' => '校验错误',
            'value' => '值',
            'version' => '版本',
            'website' => '网站',
            'wechat_id' => '微信号',
        ];

        if (isset($labels[$name])) {
            return $labels[$name];
        }

        $base = Str::afterLast($name, '.');

        return $labels[$base] ?? (string) Str::of($base)->replace('_', ' ')->headline();
    }

    public static function options(string $key): array
    {
        return [
            'activity.direction' => [
                'incoming' => '客户来访',
                'outgoing' => '主动联系',
            ],
            'activity.type' => [
                'note' => '记录',
                'call' => '电话',
                'email' => '邮件',
                'meeting' => '会议',
                'visit' => '拜访',
                'wechat' => '微信',
                'quote' => '报价',
                'order' => '订单',
                'system' => '系统事件',
            ],
            'attachment.category' => [
                'contract' => '合同',
                'payment_voucher' => '收款凭证',
                'expense_voucher' => '支出凭证',
                'other' => '其他',
            ],
            'assignment.method' => [
                'round_robin' => '轮询分配',
                'least_busy' => '按空闲度分配',
                'manual' => '手动分配',
            ],
            'approval.status' => [
                'pending' => '待审批',
                'approved' => '已通过',
                'rejected' => '已拒绝',
            ],
            'automation.action_type' => [
                'create_task' => '创建任务',
                'send_notification' => '发送通知',
                'assign_owner' => '分配负责人',
                'move_to_pool' => '移入公海',
                'update_field' => '更新字段',
            ],
            'automation.trigger_type' => [
                'lead_created' => '线索创建',
                'customer_created' => '客户创建',
                'opportunity_stage_changed' => '商机阶段变更',
                'quote_approved' => '报价审批通过',
                'order_completed' => '订单完成',
            ],
            'customer.customer_type' => [
                'company' => '企业客户',
                'individual' => '个人客户',
                'channel' => '渠道伙伴',
            ],
            'customer.lifecycle_stage' => [
                'new' => '新客户',
                'active' => '活跃客户',
                'pooled' => '公海',
                'won' => '成交客户',
                'dormant' => '沉睡客户',
                'inactive' => '沉睡客户',
                'churned' => '流失客户',
            ],
            'duplicate.status' => [
                'pending' => '待处理',
                'ignored' => '已忽略',
                'merged' => '已合并',
            ],
            'custom_field.type' => [
                'text' => '单行文本',
                'textarea' => '多行文本',
                'number' => '数字',
                'date' => '日期',
                'datetime' => '日期时间',
                'select' => '单选',
                'multi_select' => '多选',
                'boolean' => '开关',
            ],
            'forecast_category' => [
                'pipeline' => '管道预测',
                'best_case' => '最佳情况',
                'commit' => '承诺成交',
                'closed' => '已成交',
            ],
            'gender' => [
                'male' => '男',
                'female' => '女',
                'unknown' => '未知',
            ],
            'lead.qualification_status' => [
                'unqualified' => '未确认',
                'qualified' => '有效',
                'nurturing' => '培育中',
                'disqualified' => '无效',
            ],
            'lead.status' => [
                'new' => '新线索',
                'unassigned' => '待分配',
                'assigned' => '已分配',
                'working' => '跟进中',
                'pooled' => '公海',
                'contacted' => '已联系',
                'converted' => '已转客户',
                'disqualified' => '无效',
                'lost' => '已流失',
            ],
            'kb.status' => [
                'draft' => '草稿',
                'published' => '已发布',
                'archived' => '已归档',
            ],
            'pool.action' => [
                'claim' => '领取',
                'release' => '释放',
                'transfer' => '转移',
                'auto_recycle' => '自动回收',
            ],
            'order.order_source' => [
                'sales_entry' => '销售录入',
                'quote' => '报价转订单',
                'manual' => '手动创建',
            ],
            'order.order_status' => [
                'draft' => '草稿',
                'confirmed' => '已确认',
                'processing' => '处理中',
                'completed' => '已完成',
                'cancelled' => '已取消',
            ],
            'order_expense.status' => [
                'pending' => '待审批',
                'approved' => '已通过',
                'rejected' => '已拒绝',
            ],
            'payment.method' => [
                'bank_transfer' => '银行转账',
                'cash' => '现金',
                'wechat' => '微信',
                'alipay' => '支付宝',
                'card' => '银行卡',
                'other' => '其他',
            ],
            'payment.status' => [
                'pending' => '待处理',
                'completed' => '已完成',
                'failed' => '失败',
                'cancelled' => '已取消',
            ],
            'payment_plan.status' => [
                'pending' => '待收款',
                'partial' => '部分收款',
                'paid' => '已收清',
                'overdue' => '已逾期',
            ],
            'payment_status' => [
                'unpaid' => '未收款',
                'partial_paid' => '部分收款',
                'partial' => '部分收款',
                'paid' => '已收款',
                'refunded' => '已退款',
            ],
            'period_type' => [
                'week' => '周',
                'month' => '月',
                'quarter' => '季度',
                'year' => '年',
            ],
            'pipeline.stage_type' => [
                'open' => '进行中',
                'won' => '赢单',
                'lost' => '输单',
                'invalid' => '无效',
            ],
            'quote.status' => [
                'draft' => '草稿',
                'pending_approval' => '待审批',
                'sent' => '已发送',
                'approved' => '已审批',
                'accepted' => '已接受',
                'rejected' => '已拒绝',
                'expired' => '已过期',
            ],
            'quote_approval.status' => [
                'pending' => '待审批',
                'approved' => '已通过',
                'rejected' => '已拒绝',
            ],
            'subscription.billing_cycle' => [
                'manual' => '手动',
                'monthly' => '月付',
                'yearly' => '年付',
            ],
            'subscription.status' => [
                'trialing' => '试用中',
                'active' => '有效',
                'past_due' => '逾期',
                'cancelled' => '已取消',
                'expired' => '已过期',
            ],
            'task.priority' => [
                'low' => '低',
                'normal' => '普通',
                'high' => '高',
                'urgent' => '紧急',
            ],
            'task.status' => [
                'not_started' => '未开始',
                'in_progress' => '进行中',
                'completed' => '已完成',
                'ignored' => '已忽略',
                'cancelled' => '已取消',
            ],
            'target_type' => [
                'lead' => '线索',
                'customer' => '客户',
                'contact' => '联系人',
                'opportunity' => '商机',
                'quote' => '报价单',
                'order' => '订单',
                'user' => '员工',
                'department' => '部门',
                'tenant' => '全公司',
            ],
            'tenant.status' => [
                'trial' => '试用中',
                'active' => '有效',
                'suspended' => '已停用',
            ],
            'tenant_invitation.status' => [
                'pending' => '待接受',
                'accepted' => '已接受',
                'expired' => '已过期',
                'cancelled' => '已取消',
            ],
        ][$key] ?? [];
    }

    public static function hasValueLabels(string $name): bool
    {
        return in_array(Str::afterLast($name, '.'), [
            'action',
            'action_type',
            'billing_cycle',
            'customer_type',
            'direction',
            'forecast_category',
            'gender',
            'lifecycle_stage',
            'method',
            'model_type',
            'order_source',
            'order_status',
            'payment_method',
            'payment_status',
            'period_type',
            'priority',
            'qualification_status',
            'stage_type',
            'status',
            'target_type',
            'trigger_type',
            'type',
        ], true);
    }

    public static function valueLabel(string $name, mixed $state, ?Model $record = null): ?string
    {
        if (blank($state)) {
            return null;
        }

        $state = (string) $state;

        foreach (static::valueOptionKeys($name, $record) as $key) {
            $options = static::options($key);

            if (array_key_exists($state, $options)) {
                return $options[$state];
            }
        }

        return $state;
    }

    private static function valueOptionKeys(string $name, ?Model $record): array
    {
        $model = $record ? class_basename($record) : null;
        $field = Str::afterLast($name, '.');

        return array_values(array_filter([
            match ($model.'.'.$field) {
                'Activity.type' => 'activity.type',
                'Activity.direction' => 'activity.direction',
                'Attachment.category' => 'attachment.category',
                'AssignmentRule.method' => 'assignment.method',
                'AutomationAction.action_type' => 'automation.action_type',
                'AutomationRule.trigger_type' => 'automation.trigger_type',
                'Customer.customer_type' => 'customer.customer_type',
                'Customer.lifecycle_stage' => 'customer.lifecycle_stage',
                'CustomField.type' => 'custom_field.type',
                'Lead.status' => 'lead.status',
                'Lead.qualification_status' => 'lead.qualification_status',
                'KbArticle.status' => 'kb.status',
                'Opportunity.forecast_category' => 'forecast_category',
                'Order.order_source' => 'order.order_source',
                'Order.order_status' => 'order.order_status',
                'Order.payment_status' => 'payment_status',
                'OrderPaymentPlan.status' => 'payment_plan.status',
                'Payment.status' => 'payment.status',
                'Payment.payment_method' => 'payment.method',
                'PipelineStage.stage_type' => 'pipeline.stage_type',
                'Quote.status' => 'quote.status',
                'QuoteApprovalRequest.status' => 'quote_approval.status',
                'OrderExpense.status' => 'order_expense.status',
                'Task.priority' => 'task.priority',
                'Task.status' => 'task.status',
                'Tenant.status' => 'tenant.status',
                'TenantInvitation.status' => 'tenant_invitation.status',
                'TenantSubscription.status' => 'subscription.status',
                'TenantSubscription.billing_cycle' => 'subscription.billing_cycle',
                'CustomerPoolHistory.action' => 'pool.action',
                default => null,
            },
            match ($field) {
                'forecast_category' => 'forecast_category',
                'gender' => 'gender',
                'payment_status' => 'payment_status',
                'period_type' => 'period_type',
                'model_type' => 'target_type',
                'target_type' => 'target_type',
                default => null,
            },
        ]));
    }
}
