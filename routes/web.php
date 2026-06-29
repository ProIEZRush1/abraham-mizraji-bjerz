<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// This is an admin/management system: the root goes straight to the client's panel (login if needed),
// never a generic marketing page. (Authenticated → /dashboard; otherwise the auth middleware → /login.)
Route::get('/', fn () => redirect()->route('dashboard'));

// Lightweight health probe the deploy pipeline hits to verify the LIVE app + database are up,
// migrations ran and the admin was seeded (users >= 1). Public on purpose.
Route::get('/health', function () {
    try {
        return response()->json(['ok' => true, 'users' => \App\Models\User::count()]);
    } catch (\Throwable $e) {
        return response()->json(['ok' => false, 'error' => 'db'], 503);
    }
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
