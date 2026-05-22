<?php

namespace App\Http\Controllers;

use App\Models\TenantInvitation;
use App\Services\Crm\TenantInvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TenantInvitationController extends Controller
{
    public function show(string $token): View
    {
        $invitation = TenantInvitation::query()
            ->where('token', $token)
            ->with('tenant')
            ->firstOrFail();

        return view('tenant-invitations.accept', [
            'invitation' => $invitation,
            'user' => Auth::user(),
        ]);
    }

    public function accept(Request $request, string $token, TenantInvitationService $service): RedirectResponse
    {
        $invitation = TenantInvitation::query()
            ->where('token', $token)
            ->with('tenant')
            ->firstOrFail();

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
        ]);

        try {
            $user = $service->accept($invitation, $data, Auth::user());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        Auth::login($user);

        return redirect('/app/tenant/'.$invitation->tenant->slug)
            ->with('status', '已加入 '.$invitation->tenant->name);
    }
}
