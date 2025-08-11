<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\personel\UsersController;
use App\Http\Controllers\planlama\Emirler;
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
    Route::get('debug/{id?}', [AuthController::class, 'debugUser']);
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


// Users API routes
Route::group(['prefix' => 'users', 'middleware' => 'auth:sanctum'], function () {
  Route::get('/permissions', [AuthController::class, 'permissions']);
  Route::get('/', [UsersController::class, 'index']);
  Route::get('/{id}', [UsersController::class, 'show']);
  Route::get('/{id}/permissions', [UsersController::class, 'getUserPermissions']);
  Route::post('/{id}/permissions/{permissionId}', [UsersController::class, 'assignUserPermission']);
  Route::delete('/{id}/permissions/{permissionId}', [UsersController::class, 'removeUserPermission']);
  Route::post('/', [UsersController::class, 'store']);
  Route::put('/{id}', [UsersController::class, 'update']);
  Route::delete('/{id}', [UsersController::class, 'destroy']);
  // Toggle user active/passive state
  Route::put('/durumdegistir/{id}', [UsersController::class, 'durumDegistir']);
});

// Work centers and stations (protected)
Route::middleware('auth:sanctum')->group(function () {
  // Route::get('/merkezal', [UsersController::class, 'getWorkCenters']);
  // Route::get('/istasyonal', [UsersController::class, 'getStations']);
  Route::get('/istasyonlaral', [Emirler::class, 'getIstasyonlar']);
  Route::get('/istasyonal', [Emirler::class, 'getIstasyon']);
  Route::get('/merkezal', [Emirler::class, 'getMerkezler']);
  // Password reset and activate helpers used by UI
  Route::put('/sifresifirla/{id}', [UsersController::class, 'sifreSifirla']);
  Route::put('/userpasif/{id}', [UsersController::class, 'durumDegistir']);
});

// Planning endpoints (protected) used by is-emirleri-montaj.vue
Route::middleware('auth:sanctum')->group(function () {
  // Data grids and details
  Route::get('/data', [Emirler::class, 'getData']);
  Route::get('/aktifleri-al', [Emirler::class, 'AktifleriAl']);
  Route::get('/isEmriDetay', [Emirler::class, 'getIsEmriDetay']);
  Route::get('/digerdepobakiyeleri', [Emirler::class, 'getDepoBakiyeleri']);

  // Updates
  Route::post('/istasyonKaydet', [Emirler::class, 'istasyonKaydet']);
  Route::post('/aksesuarKaydet', [Emirler::class, 'AksesuarKaydet']);
  Route::post('/updatePlanBaslangic', [Emirler::class, 'updatePlanlananBaslangic']);
  Route::post('/planNotKaydet', [Emirler::class, 'notKaydet']);
  Route::post('/saveGrup', [Emirler::class, 'saveGrup']);
  Route::post('/ralguncelle', [Emirler::class, 'RalGuncelle']);
});
