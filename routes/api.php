<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\kalite\KaliteController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\personel\UsersController;
use App\Http\Controllers\planlama\EmirlerController;
use App\Http\Controllers\planlama\KapasiteController;
use App\Http\Controllers\planlama\IhtiyacController;
use App\Http\Controllers\planlama\UretimMontajController;
use App\Http\Controllers\satis\SatisController;
use App\Http\Controllers\satinalma\SatinalmaController;
use App\Http\Controllers\PhotoController;
use Illuminate\Support\Facades\DB;

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
  Route::post('/', [UsersController::class, 'store']);

  Route::put('/{id}', [UsersController::class, 'update']);
  Route::put('/durumdegistir/{id}', [UsersController::class, 'durumDegistir']);
  Route::put('/sifresifirla/{id}', [UsersController::class, 'sifreSifirla']);
  Route::put('/userpasif/{id}', [UsersController::class, 'durumDegistir']);

  Route::delete('/{id}', [UsersController::class, 'destroy']);
  Route::delete('/{id}/permissions/{permissionId}', [UsersController::class, 'removeUserPermission']);
});

// planlama-montaj
Route::middleware('auth:sanctum')->group(function () {
  Route::get('/data', [EmirlerController::class, 'getData']);
  Route::get('/aktifleri-al', [EmirlerController::class, 'AktifleriAl']);
  Route::get('/isEmriDetay', [EmirlerController::class, 'getIsEmriDetay']);
  Route::get('/digerdepobakiyeleri', [EmirlerController::class, 'getDepoBakiyeleri']);

  Route::post('/istasyonKaydet', [EmirlerController::class, 'istasyonKaydet']);
  Route::post('/aksesuarKaydet', [EmirlerController::class, 'AksesuarKaydet']);
  Route::post('/updatePlanBaslangic', [EmirlerController::class, 'updatePlanlananBaslangic']);
  Route::post('/planNotKaydet', [EmirlerController::class, 'notKaydet']);
  Route::post('/saveGrup', [EmirlerController::class, 'saveGrup']);
  Route::post('/ralguncelle', [EmirlerController::class, 'RalGuncelle']);
});

// kapasite-hesapla
Route::middleware('auth:sanctum')->group(function () {
  Route::get('/kapasite-param', [KapasiteController::class, 'getKapasiteParam']);
  Route::get('/get-kapasite-data', [KapasiteController::class, 'getKapasiteData']);
  Route::get('/kapasite-planlama', [KapasiteController::class, 'kapasitePlanla']);
  Route::get('/kapasite-takvim', [KapasiteController::class, 'getTakvim']);
  Route::get('/kapasite-hafta', [KapasiteController::class, 'getKapasiteHaftalar']);

  Route::post('/kapasite-guncelle', [KapasiteController::class, 'kapasiteGuncelle']);
});

// ihtiyac-listesi
Route::middleware('auth:sanctum')->group(function () {
  Route::get('/istasyon-ihtiyaclar', [IhtiyacController::class, 'IhtiyacHesapla']);
  Route::get('/merkezal', [IhtiyacController::class, 'getMerkezler']);
  Route::get('/istasyonal', [IhtiyacController::class, 'getIstasyon']);
  Route::get('/isEmriAcilmislar', [IhtiyacController::class, 'getAcilmisIsEmirleri']);
  Route::get('/satinalmasorgu', [IhtiyacController::class, 'getSatinalmaSorgu']);
  Route::get('/taleplersorgu', [IhtiyacController::class, 'getTaleplerSorgu']);
  Route::get('/digerdepobakiyeleri', [IhtiyacController::class, 'getDepoBakiyeleri']);
});

// uretim-montaj
Route::middleware('auth:sanctum')->group(function () {
  Route::get('/personeller', [UretimMontajController::class, 'PersonelListesi']);
  Route::get('/aktif-ekipler', [UretimMontajController::class, 'EkipleriAl']);
  Route::get('/uretim-kartlari', [UretimMontajController::class, 'getKartlar']);
  Route::get('/uretim-haftalik', [UretimMontajController::class, 'haftalikPivot']);
  Route::get('/duruslar-istasyon', [UretimMontajController::class, 'IstasyonDuruslariniAl']);
  Route::get('/duruslar-aktif', [UretimMontajController::class, 'AktifDuruslariAl']);
  Route::get('/dataUretimEmirler', [UretimMontajController::class, 'getUretimData']);
  Route::get('/eksik-kontrolu', [UretimMontajController::class, 'EksikKontrolu']);
  Route::get('/isEmriDetay', [UretimMontajController::class, 'getIsEmriDetay']);
  Route::get('/haftalik-gunluk-paket-toplam', [UretimMontajController::class, 'getHaftalikGunlukToplamPaketMiktarlari']);
  Route::get('/dataUretimPerformans', [UretimMontajController::class, 'getUretimPerformans']);
  Route::get('/durus-sebepleri-al', [UretimMontajController::class, 'DurusSebepleriAl']);

  Route::post('/insert-workorder', [UretimMontajController::class, 'insertWorkOrder']);
  Route::post('/ekip-bitir-toplu', [UretimMontajController::class, 'bitirToplu']);
  Route::post('/aktif-ekipler', [UretimMontajController::class, 'EkipKaydet']);
  Route::post('/kontrolcu-cagir-guncelle', [UretimMontajController::class, 'guncelleIsCheckQualityOpr']);
  Route::post('/durumKaydet', [UretimMontajController::class, 'DurumKaydet']);
  Route::post('/durusKaydet', [UretimMontajController::class, 'DurusKaydet']);
  Route::post('/mola-baslat', [UretimMontajController::class, 'baslat']);
  Route::post('/kontrolGerekKaydet', [UretimMontajController::class, 'KontrolGerekKaydet']);

  Route::put('/aktif-ekipler/kapat', [UretimMontajController::class, 'EkipleriKapat']);
});

// satis
Route::middleware('auth:sanctum')->group(function () {
  Route::get('/musteri-siparisleri', [SatisController::class, 'getMusteriSiparisleri']);
  Route::get('/ciro-ozettablo', [SatisController::class, 'getCiro']);
  Route::get('/siparis-ihtiyaclar', [SatisController::class, 'IhtiyacHesaplaSiparis']);

  Route::post('/teslimtarihidegistirmusteri', [SatisController::class, 'TeslimTarihiDegistirMusteri']);
});

// satinalma
Route::middleware('auth:sanctum')->group(function () {
  Route::get('/firmalar', [SatinalmaController::class, 'getFirmalar']);
  Route::get('/satinalma-siparisleri', [SatinalmaController::class, 'getSatinalmaSiparisleri']);
  Route::get('/satinalma-talepleri', [SatinalmaController::class, 'getSatinalmaTalepleri']);

  Route::post('/teslimtarihidegistir', [SatinalmaController::class, 'TeslimTarihiDegistir']);
  Route::post('/notgir', [SatinalmaController::class, 'NotKaydet']);
});

// kalite
Route::middleware('auth:sanctum')->group(function () {
  Route::get('/isEmri', [KaliteController::class, 'getIsEmri']);
  Route::get('/aktiflerial', [KaliteController::class, 'getAktifCalismalar']);
  Route::get('/kontrolleri-al', [KaliteController::class, 'KontrolleriAl']);
  Route::get('/urun-agaci-secim/yukle', [KaliteController::class, 'UrunAgaciYukle']);
  Route::get('/kontrolAktifAl', [KaliteController::class, 'KontrolAktifAl']);
  Route::get('/gerekceler', fn() => DB::table('oftt_param_gerekceler')->get());
  Route::get('/hata-goster-resim', [PhotoController::class, 'getFotolar']);

  Route::post('/kontrolAktifKaydet', [KaliteController::class, 'KontrolAktifKaydet']);
  Route::post('/kontroller/toplu-kaydet', [KaliteController::class, 'topluKaydet']);
  Route::post('/kontrol-kaydet', [KaliteController::class, 'kaydetKontroller']);
  Route::post('/urun-agaci-secim/kaydet', [KaliteController::class, 'UrunAgaciKaydet']);
  Route::post('/hata-kaydet', [KaliteController::class, 'HataKaydet']);
  Route::post('/hata-kaydet-resim', [KaliteController::class, 'HataKaydetResim']);

  Route::delete('/hata-sil-resim', [PhotoController::class, 'deleteFoto']);
});

// PUBLIC: Tekil fotoğraf dosyasını sun (auth gerektirmez). Güvenlik gerekirse signed URL eklenebilir.
Route::get('/oft-resimler/{isemri_no}/{filename}', [PhotoController::class, 'serveImage']);
