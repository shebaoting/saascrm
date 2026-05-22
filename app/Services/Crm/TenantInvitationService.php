<?php

namespace App\Services\Crm;

use App\Models\TenantInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TenantInvitationService
{
    /**
     * @param  array{name?: string, email?: string, password?: string}  $data
     */
    public function accept(TenantInvitation $invitation, array $data = [], ?User $user = null): User
    {
        $this->assertCanAccept($invitation, $user, $data);

        return DB::transaction(function () use ($invitation, $data, $user): User {
            $user ??= User::query()->firstOrCreate([
                'email' => $invitation->email ?: $data['email'],
            ], [
                'name' => $data['name'] ?? $invitation->email ?? '新成员',
                'password' => Hash::make($data['password']),
                'status' => true,
            ]);

            if (! $user->status) {
                $user->forceFill(['status' => true])->save();
            }

            $invitation->tenant->users()->syncWithoutDetaching([
                $user->id => [
                    'member_name' => $user->name,
                    'is_owner' => false,
                    'is_admin' => false,
                    'status' => 'active',
                    'joined_at' => now(),
                ],
            ]);

            foreach ($invitation->role_ids ?: [] as $roleId) {
                $user->roles()->syncWithoutDetaching([
                    (int) $roleId => [
                        'tenant_id' => $invitation->tenant_id,
                        'model_type' => User::class,
                    ],
                ]);
            }

            foreach ($invitation->department_ids ?: [] as $departmentId) {
                DB::table('department_user')->updateOrInsert([
                    'tenant_id' => $invitation->tenant_id,
                    'department_id' => (int) $departmentId,
                    'user_id' => $user->id,
                ], [
                    'is_leader' => false,
                    'main_department' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $invitation->forceFill([
                'status' => 'accepted',
                'accepted_at' => now(),
            ])->save();

            app(AuditLogService::class)->record('tenant_invitation_accepted', $invitation, [
                'status' => 'pending',
            ], [
                'status' => 'accepted',
                'user_id' => $user->id,
            ]);

            return $user;
        });
    }

    private function assertCanAccept(TenantInvitation $invitation, ?User $user, array $data): void
    {
        if ($invitation->status !== 'pending') {
            throw ValidationException::withMessages([
                'invitation' => '该邀请已经处理，不能重复接受。',
            ]);
        }

        if ($invitation->expires_at?->isPast()) {
            $invitation->forceFill(['status' => 'expired'])->save();

            throw ValidationException::withMessages([
                'invitation' => '该邀请已过期，请联系管理员重新发送。',
            ]);
        }

        if ($invitation->email && $user && strcasecmp($invitation->email, $user->email) !== 0) {
            throw ValidationException::withMessages([
                'email' => '当前登录账号与邀请邮箱不一致。',
            ]);
        }

        if (! $user && blank($invitation->email) && blank($data['email'] ?? null)) {
            throw ValidationException::withMessages([
                'email' => '请输入邮箱后接受邀请。',
            ]);
        }

        if (! $user && blank($data['password'] ?? null) && ! User::where('email', $invitation->email ?: $data['email'])->exists()) {
            throw ValidationException::withMessages([
                'password' => '新成员需要设置登录密码。',
            ]);
        }
    }
}
