<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileUploadController;

/*
|--------------------------------------------------------------------------
| File Upload Routes
|--------------------------------------------------------------------------
|
| Add these routes to your web.php or api.php file
|
*/

// For web routes (web.php) - with CSRF protection
Route::post('/upload', [FileUploadController::class, 'upload'])->name('file.upload');
Route::get('/files', [FileUploadController::class, 'index'])->name('file.index');
Route::delete('/files', [FileUploadController::class, 'delete'])->name('file.delete');

// For API routes (api.php) - without CSRF, add auth middleware as needed
// Route::middleware('auth:sanctum')->group(function () {
//     Route::post('/upload', [FileUploadController::class, 'upload']);
//     Route::get('/files', [FileUploadController::class, 'index']);
//     Route::delete('/files', [FileUploadController::class, 'delete']);
// });
