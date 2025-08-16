<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\personel\UsersController;
use App\Http\Controllers\planlama\EmirlerController;
use App\Http\Controllers\planlama\KapasiteController;
use App\Http\Controllers\planlama\IhtiyacController;
use App\Http\Controllers\planlama\UretimMontajController;
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
  Route::put('/durumdegistir/{id}', [UsersController::class, 'durumDegistir']);
});

// Work centers and stations (protected)
Route::middleware('auth:sanctum')->group(function () {
  Route::get('/istasyonlaral', [EmirlerController::class, 'getIstasyonlar']);
  Route::get('/istasyonal', [EmirlerController::class, 'getIstasyon']);
  Route::get('/merkezal', [EmirlerController::class, 'getMerkezler']);
  // Password reset and activate helpers used by UI
  Route::put('/sifresifirla/{id}', [UsersController::class, 'sifreSifirla']);
  Route::put('/userpasif/{id}', [UsersController::class, 'durumDegistir']);
});

// Planning endpoints (protected) used by is-emirleri-montaj.vue
Route::middleware('auth:sanctum')->group(function () {
  Route::get('/data', [EmirlerController::class, 'getData']);
  Route::get('/aktifleri-al', [EmirlerController::class, 'AktifleriAl']);
  Route::get('/isEmriDetay', [EmirlerController::class, 'getIsEmriDetay']);
  Route::get('/digerdepobakiyeleri', [EmirlerController::class, 'getDepoBakiyeleri']);

  // Updates
  Route::post('/istasyonKaydet', [EmirlerController::class, 'istasyonKaydet']);
  Route::post('/aksesuarKaydet', [EmirlerController::class, 'AksesuarKaydet']);
  Route::post('/updatePlanBaslangic', [EmirlerController::class, 'updatePlanlananBaslangic']);
  Route::post('/planNotKaydet', [EmirlerController::class, 'notKaydet']);
  Route::post('/saveGrup', [EmirlerController::class, 'saveGrup']);
  Route::post('/ralguncelle', [EmirlerController::class, 'RalGuncelle']);
});

// Planning endpoints (protected) used by ihtiyac-listesi.vue
Route::middleware('auth:sanctum')->group(function () {
  Route::get('/istasyon-ihtiyaclar', [IhtiyacController::class, 'IhtiyacHesapla']);
  Route::get('/merkezal', [IhtiyacController::class, 'getMerkezler']);
  Route::get('/istasyonal', [IhtiyacController::class, 'getIstasyon']);
  Route::get('/isEmriAcilmislar', [IhtiyacController::class, 'getAcilmisIsEmirleri']);
  Route::get('/satinalmasorgu', [IhtiyacController::class, 'getSatinalmaSorgu']);
  Route::get('/taleplersorgu', [IhtiyacController::class, 'getTaleplerSorgu']);
  Route::get('/digerdepobakiyeleri', [IhtiyacController::class, 'getDepoBakiyeleri']);
});

// Planning endpoints (protected) used by kapasite-hesapla.vue
Route::middleware('auth:sanctum')->group(function () {
  Route::get('/kapasite-param', [KapasiteController::class, 'getKapasiteParam']);
  Route::get('/get-kapasite-data', [KapasiteController::class, 'getKapasiteData']);
  Route::get('/kapasite-planlama', [KapasiteController::class, 'kapasitePlanla']);
  Route::get('/kapasite-takvim', [KapasiteController::class, 'getTakvim']);
  Route::get('/kapasite-hafta', [KapasiteController::class, 'getKapasiteHaftalar']);
  Route::post('/kapasite-guncelle', [KapasiteController::class, 'kapasiteGuncelle']);
});

// Planning endpoints (protected) used by uretim-montaj.vue
Route::middleware('auth:sanctum')->group(function () {
  Route::get('/personeller', [UretimMontajController::class, 'PersonelListesi']);
  Route::get('/aktif-ekipler', [UretimMontajController::class, 'EkipleriAl']);
  Route::post('/aktif-ekipler', [UretimMontajController::class, 'EkipKaydet']);
  Route::put('/aktif-ekipler/kapat', [UretimMontajController::class, 'EkipleriKapat']);
  // Route::post('/ekip-bitir', [UretimMontajController::class, 'bitir']);
  Route::post('/ekip-bitir-toplu', [UretimMontajController::class, 'bitirToplu']);
  Route::post('/insert-workorder', [UretimMontajController::class, 'insertWorkOrder']);
  Route::get('/uretim-kartlari', [UretimMontajController::class, 'getKartlar']);
  Route::get('/uretim-haftalik', [UretimMontajController::class, 'haftalikPivot']);
  Route::post('/mola-baslat', [UretimMontajController::class, 'baslat']);
  Route::post('/durumKaydet', [UretimMontajController::class, 'DurumKaydet']);
  Route::post('/durusKaydet', [UretimMontajController::class, 'DurusKaydet']);
  Route::post('/kontrolcu-cagir-guncelle', [UretimMontajController::class, 'guncelleIsCheckQualityOpr']);
  Route::get('/duruslar-istasyon', [UretimMontajController::class, 'IstasyonDuruslariniAl']);
  Route::get('/duruslar-aktif', [UretimMontajController::class, 'AktifDuruslariAl']);
  Route::post('/kontrolGerekKaydet', [UretimMontajController::class, 'KontrolGerekKaydet']);
  Route::get('/dataUretimEmirler', [UretimMontajController::class, 'getUretimData']);
  Route::get('/eksik-kontrolu', [UretimMontajController::class, 'EksikKontrolu']);
  Route::get('/isEmriDetay', [UretimMontajController::class, 'getIsEmriDetay']);
  Route::get('/haftalik-gunluk-paket-toplam', [UretimMontajController::class, 'getHaftalikGunlukToplamPaketMiktarlari']);
  // Route::get('/haftalik-paket-miktarlari', [UretimMontajController::class, 'getHaftalikPaketMiktarlari']);
  Route::get('/dataUretimPerformans', [UretimMontajController::class, 'getUretimPerformans']);
  Route::get('/durus-sebepleri-al', [UretimMontajController::class, 'DurusSebepleriAl']);
});
