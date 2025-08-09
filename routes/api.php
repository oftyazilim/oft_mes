<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RolesController;
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

Route::group(['prefix' => 'auth'], function () {
  Route::post('login', [AuthController::class, 'login']);
  Route::post('register', [AuthController::class, 'register']);
  Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
  Route::post('reset-password', [AuthController::class, 'resetPassword']);

  Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user']);
  });
});

// Roles API routes
Route::group(['prefix' => 'roles', 'middleware' => 'auth:sanctum'], function () {
  Route::get('/', [RolesController::class, 'index']);
  Route::get('/users', [RolesController::class, 'users']);
  Route::get('/available', [RolesController::class, 'availableRoles']);
  Route::get('/{id}', [RolesController::class, 'show']);
  Route::post('/', [RolesController::class, 'store']);
  Route::put('/{id}', [RolesController::class, 'update']);
  Route::delete('/{id}', [RolesController::class, 'destroy']);
});

Route::get('/permissions', [AuthController::class, 'permissions']);
