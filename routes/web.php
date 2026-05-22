<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TenantInvitationController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/invitations/{token}', [TenantInvitationController::class, 'show'])->name('tenant-invitations.show');
Route::post('/invitations/{token}', [TenantInvitationController::class, 'accept'])->name('tenant-invitations.accept');
