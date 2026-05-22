<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>接受成员邀请</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <main class="mx-auto flex min-h-screen max-w-xl items-center px-6 py-10">
        <section class="w-full rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="mb-6">
                <p class="text-sm text-gray-500">成员邀请</p>
                <h1 class="mt-1 text-2xl font-semibold">{{ $invitation->tenant->name }}</h1>
                <p class="mt-2 text-sm text-gray-600">
                    {{ $invitation->email ?: '你' }} 将加入该公司工作台。
                </p>
            </div>

            @if ($invitation->status !== 'pending')
                <div class="rounded-md bg-gray-100 p-4 text-sm text-gray-700">该邀请已经处理。</div>
            @elseif ($invitation->expires_at?->isPast())
                <div class="rounded-md bg-red-50 p-4 text-sm text-red-700">该邀请已过期，请联系管理员重新发送。</div>
            @else
                @if ($errors->any())
                    <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="post" action="{{ route('tenant-invitations.accept', $invitation->token) }}" class="space-y-4">
                    @csrf

                    @unless ($user)
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">姓名</span>
                            <input name="name" value="{{ old('name') }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2" required>
                        </label>

                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">邮箱</span>
                            <input name="email" type="email" value="{{ old('email', $invitation->email) }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2" {{ $invitation->email ? 'readonly' : '' }} required>
                        </label>

                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">密码</span>
                            <input name="password" type="password" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2" minlength="8">
                        </label>
                    @else
                        <div class="rounded-md bg-gray-100 p-4 text-sm text-gray-700">
                            将使用当前账号 {{ $user->email }} 接受邀请。
                        </div>
                    @endunless

                    <button type="submit" class="w-full rounded-md bg-teal-600 px-4 py-2 text-sm font-semibold text-white">
                        接受邀请
                    </button>
                </form>
            @endif
        </section>
    </main>
</body>
</html>
