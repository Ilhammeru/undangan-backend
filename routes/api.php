<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryInvitationController;
use App\Http\Controllers\ThemeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('/unauthenticated', function() {
    return response()->json([
        'message' => 'Harap login terlebih dahulu',
    ], 401);
})->name('unauthenticated');
Route::post('/oauth/register', [AuthController::class, 'create'])->name('auth.register');
Route::post('/oauth/login', [AuthController::class, 'login'])->name('login');
Route::get('/oauth/social/{social}', [AuthController::class, 'social'])->name('social');
Route::middleware(['auth:api'])->group(function() {
    Route::post('/set-progress', [AuthController::class, 'set_progress_setup']);
    Route::get('/oauth/user/setting', [AuthController::class, 'user_setting']);

    // begin::theme
    Route::post('/theme', [ThemeController::class, 'store'])->name('theme.store');
    Route::get('/theme', [ThemeController::class, 'index'])->name('theme.index');
    Route::delete('/theme/{id}', [ThemeController::class, 'destroy'])->name('theme.destroy');
    // end::theme


    Route::post('/oauth/setting/theme', [AuthController::class, 'update_theme'])->name('auth.setting.theme');
    Route::post('/oauth/setting/link', [AuthController::class, 'update_link'])->name('auth.setting.link');
    Route::post('/oauth/setting/couple/{type}', [AuthController::class, 'update_couple_data'])->name('auth.setting.couple');
    Route::post('/oauth/setting/couple-photo', [AuthController::class, 'update_couple_photo'])->name('auth.setting.couple-photo');
    Route::post('/oauth/setting/event/{type}', [AuthController::class, 'update_event'])->name('auth.setting.couple-photo');

    Route::resource('category', CategoryInvitationController::class);
});
