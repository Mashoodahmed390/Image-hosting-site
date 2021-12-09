<?php

use App\Http\Controllers\PhotoController;
use App\Http\Controllers\UserController;
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

Route::middleware(['Jwt'])->group(function (){
Route::post('upload',[PhotoController::class,'uploadPhoto']);
Route::delete('delete',[PhotoController::class,'deletePhoto']);
Route::get('display/all/picture',[PhotoController::class,'displayallpicture'])->middleware("publicPhoto");
Route::get('storage/images',[PhotoController::class,'displayallpicture'])->middleware("publicPhoto");
});
