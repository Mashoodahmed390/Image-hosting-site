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
Route::delete('delete/{photo_id}',[PhotoController::class,'deletePhoto']);
Route::put('update/{photo_id}',[PhotoController::class,'imageUpdate']);
Route::get('display/all/picture',[PhotoController::class,'displayallpicture']);
Route::post('visibility/{photo_id}',[PhotoController::class,'makingimageprivateorpublic']);
Route::post('search',[PhotoController::class,'search']);
Route::get('shareablelink/{photo_id}',[PhotoController::class,'getshareablelink']);
// Route::get('private/{photo_id}',[PhotoController::class,'photoshare']);
});
