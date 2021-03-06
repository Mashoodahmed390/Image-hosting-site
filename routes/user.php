<?php

use App\Http\Controllers\UserController;
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


header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Allow-Method: *');

Route::get('/test',function(){
    dd("test");
});
Route::post('signup', [UserController::class,'signup']);
Route::post('login', [UserController::class,'login']);
Route::post('forgetpassword', [UserController::class,'forgetpassword']);
Route::put('updatepassword/{email}/{token}', [UserController::class,'updatepassword']);
Route::get('/verifyEmail/{email}', [UserController::class,'verify']);

Route::middleware(['Jwt'])->group(function (){
Route::put('update', [UserController::class,'updateuser']);
Route::get('dashboard', [UserController::class,'resource']);
});
