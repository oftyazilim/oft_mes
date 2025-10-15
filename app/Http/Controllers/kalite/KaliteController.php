<?php

namespace App\Http\Controllers\kalite;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\HataBildirimiMail;


class KaliteController extends Controller
{

    public function getIsEmri(Request $request)
    {
        // Log::info($request->all());
        $deger = (int)$request->deger;

        $data = DB::connection('pgsql')
            ->table('uyumsoft.OFTV_ISEMIRLERI_DETAY')
            ->select(
                'isemri_id',
                'isemri_miktari',
                'cari_ad',
                'IS_ISTASYONU',
                'IS_ISTASYONU_ID',
                'isemri_no',
                'stok_kodu',
                'stok_adi',
                'aksesuar',
                'siparis_belge_no',
                DB::raw("to_char(planlanan_baslangic, 'DD.MM.YYYY') || ' ... ' || to_char(planlanan_bitis_tarihi, 'DD.MM.YYYY') as plan_tarih_araligi")
            )
            ->where('isemri_id', $deger)
            ->get();

        if ($data->isEmpty()) {
            $data = DB::connection('pgsql')
                ->table('uyumsoft.OFTV_ISEMIRLERI_KAPANMISLAR')
                ->select(
                    'isemri_id',
                    'isemri_miktari',
                    'cari_ad',
                    'IS_ISTASYONU',
                    'IS_ISTASYONU_ID',
                    'isemri_no',
                    'stok_kodu',
                    'stok_adi',
                    'aksesuar',
                    'siparis_belge_no',
                    DB::raw("to_char(planlanan_baslangic, 'DD.MM.YYYY') || ' ... ' || to_char(planlanan_bitis_tarihi, 'DD.MM.YYYY') as plan_tarih_araligi")
                )
                ->where('isemri_id', $deger)
                ->get();
        }
        // Log::info($data);

        return response()->json([
            'data' => $data,
        ], 200);
    }

    public function getAktifCalismalar(Request $request)
    {
        $aktifler = DB::connection('pgsql_oft')
            ->table('oftt_kontrol_isemri')
            ->join('oftt_param_istasyon', 'oftt_param_istasyon.wstation_id', '=', 'oftt_kontrol_isemri.istasyon_id')
            ->where('oftt_kontrol_isemri.is_open', 1)
            ->where('oftt_kontrol_isemri.istasyon_id', $request->istasyonId)
            ->select(
                'oftt_kontrol_isemri.id',
                'oftt_kontrol_isemri.isemri_no',
                'oftt_kontrol_isemri.item_code',
                'oftt_kontrol_isemri.item_name',
                'oftt_kontrol_isemri.belge_no',
                'oftt_kontrol_isemri.cari_ad',
                'oftt_param_istasyon.tanim',
                'oftt_kontrol_isemri.istasyon_id',
                'oftt_kontrol_isemri.item_id',
                'oftt_kontrol_isemri.is_active',
                'oftt_kontrol_isemri.isemri_id'
            )
            ->orderBy('oftt_kontrol_isemri.id', 'desc')
            ->get();

        return response()->json($aktifler);
    }

    public function HataKaydet(Request $request)
    {
        // Log::info('Gelen veri:', $request->all());
        // $isPhoto = $request->isPhoto ?? 0;
        // Log::info('Resim var mı:'. $isPhoto);
        $validator = Validator::make($request->all(), [
            'urun_kontrol_m_id' => 'required|integer',
            'serino'             => 'required|string|max:255',
            'hatalar'            => 'nullable|array',
            'sonuc'              => 'required|string|max:20',
            'hataOzet'           => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Tablodaki bit alanlarının defaultları
        $hataKolonlari = [
            'cc',
            'bke',
            'cm',
            'kh',
            'fb',
            'sh',
            'kah',
            'bc',
            'kpl',
            'tkh',
            'bh',
            'ieh',
            'oh',
            'h19',
            'smh',
            'th',
            'ah',
            'mh',
            'sth',
            'bks',
            'eh',
            'elk'
        ];

        // Tüm kolonları 0 yap, sadece seçilenleri 1
        $hatalar = array_fill_keys($hataKolonlari, 0);
        if (is_array($request->hatalar)) {
            foreach ($request->hatalar as $kod) {
                $k = Str::lower($kod);
                if (array_key_exists($k, $hatalar)) {
                    $hatalar[$k] = 1;
                }
            }
        }

        // Diğer alanları ekle
        $kayit = array_merge($hatalar, [
            'urun_kontrol_m_id' => $request->urun_kontrol_m_id,
            'seri_no'           => $request->serino,
            'sonuc'             => $request->sonuc,
            'kontrol_tarihi'    => now(),
            'olusturan_id'      =>  $request->user_id ?? 0,
            'note'               =>  $request->hataOzet ?? null,
            // 'is_photo'          => $isPhoto,
        ]);

        try {
            DB::connection('pgsql_oft')->table('oftt_urun_kontrol_d')->insert($kayit);
        } catch (\Throwable $e) {
            Log::error('Kayıt hatası: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }

        // Front-end'den base64 olarak gelen resimleri kk-fotolar klasörüne kaydet
        if ($request->has('resimler') && is_array($request->resimler) && count($request->resimler) > 0) {
            $serino = (string) $request->serino;
            $isemri_no = (string) $request->isemri_no;

            // Foto klasörü kökünü .env PHOTO_KK_DIR ile yapılandırılabilir yap (Windows UNC veya Linux mount)
            $photoBase = rtrim(config('app.photo_kk_dir', "\\\\192.6.2.4\\canovate_elektronik\\01_GENEL\\15_OFT\\fotolar\\kk-fotolari\\"), '\\/');
            $klasorYolu = $photoBase . DIRECTORY_SEPARATOR . $isemri_no;

            if (!File::exists($klasorYolu)) {
                // 0777: ağ paylaşımında yetki sorunlarını azaltmak için geniş izin; ortamınıza göre kısıtlayın
                File::makeDirectory($klasorYolu, 0777, true);
            }

            // Intervention Image opsiyonel: paket kurulu değilse (class bulunamazsa) orijinal base64 doğrudan yazılır.
            $interventionAvailable = class_exists(ImageManager::class) && class_exists(Driver::class);
            $manager = $interventionAvailable ? new ImageManager(new Driver()) : null; // Opsiyonel

            $sira = 1;
            $kaydedilenAdet = 0;
            foreach ($request->resimler as $resim) {
                if (!is_array($resim) || !isset($resim['base64'])) {
                    continue;
                }

                $dosyaAdi = $serino . '-' . str_pad($sira, 2, '0', STR_PAD_LEFT) . '.jpg';
                $tamYol = $klasorYolu . DIRECTORY_SEPARATOR . $dosyaAdi;

                try {
                    // Front-end base64 prefix'i kaldırıyor; yine de güvenli olsun diye olası data URL prefix'ini temizle
                    $b64 = preg_replace('/^data:image\/[a-zA-Z0-9.+-]+;base64,/', '', (string) $resim['base64']);
                    $imageData = base64_decode($b64);
                    if ($imageData === false) {
                        throw new \RuntimeException('Base64 decode başarısız.');
                    }

                    if ($manager) {
                        // Paket mevcutsa en azından yeniden encode ederek JPEG kalite uygula
                        $image = $manager->read($imageData);
                        $encoded = $image->toJpeg(82);
                        File::put($tamYol, (string) $encoded);
                    } else {
                        // Paket yok: orijinal veriyi yaz
                        File::put($tamYol, $imageData);
                    }
                    $sira++;
                    $kaydedilenAdet++;
                } catch (\Throwable $e) {
                    Log::error('Resim kaydetme hatası: ' . $e->getMessage(), [
                        'isemri_no' => $isemri_no,
                        'serino' => $serino,
                        'dosya' => $dosyaAdi ?? null,
                    ]);
                }
            }

            // En az bir foto kaydedildiyse ilgili seri için is_photo=1 yap
            if ($kaydedilenAdet > 0) {
                try {
                    DB::connection('pgsql_oft')
                        ->table('oftt_urun_kontrol_d')
                        ->where('seri_no', $serino)
                        ->update(['is_photo' => 1]);
                } catch (\Throwable $e) {
                    Log::warning('is_photo alanı güncellenemedi: ' . $e->getMessage(), [
                        'seri_no' => $serino,
                    ]);
                }
            }
        }





        return response()->json(['success' => true]);
    }

    public function kaydetKontroller(Request $request)
    {
        $veri = $request->all();
        // Log::info('Kontrolleri kaydetme isteği:', $veri);
        $mID = 0;

        $kontrol = DB::connection('pgsql_oft')
            ->table('oftt_urun_kontrol_m')
            ->where('isemri_id', $veri['isEmriId'])
            ->where('istasyon_id', $veri['istasyonID'])
            ->orderBy('revizyon', 'desc')
            ->first();

        $commonData = [
            'urun_agaci_not' => $veri['urunAgaciText'],
            'urun_agaci_ok' => $veri['urunAgaciCheck'],
            'gorunum_not' => $veri['gorunumText'],
            'gorunum_ok' => $veri['gorunumCheck'],
            'olcusel_not' => $veri['olcuselText'],
            'olcusel_ok' => $veri['olcuselCheck'],
            'paketleme_not' => $veri['paketlemeText'],
            'paketleme_ok' => $veri['paketlemeCheck'],
            'etiket_not' => $veri['etiketText'],
            'etiket_ok' => $veri['etiketCheck'],
            'dokuman_not' => $veri['dokumanText'],
            'dokuman_ok' => $veri['dokumanCheck'],
        ];

        if ($kontrol) {
            DB::connection('pgsql_oft')
                ->table('oftt_urun_kontrol_m')
                ->insert(array_merge($commonData, [
                    'isemri_id' => $veri['isEmriId'],
                    'istasyon_id' => $veri['istasyonID'],
                    'olusturan_id' => $request->user_id,
                    'inspector_id' => $veri['user_id'],
                    'kontrol_tarihi' => now(),
                    'revizyon' => (int)$kontrol->revizyon + 1, // Revizyon numarasını artır
                ]));
        } else {
            DB::connection('pgsql_oft')
                ->table('oftt_urun_kontrol_m')
                ->insert(array_merge($commonData, [
                    'isemri_id' => $veri['isEmriId'],
                    'istasyon_id' => $veri['istasyonID'],
                    'olusturan_id' => $request->user_id,
                    'inspector_id' => $veri['user_id'],
                    'kontrol_tarihi' => now(),
                ]));
        }
        $mID = DB::connection('pgsql_oft')
            ->table('oftt_urun_kontrol_m')
            ->where('isemri_id', $veri['isEmriId'])
            ->where('istasyon_id', $veri['istasyonID'])
            ->orderByDesc('id')
            ->value('id') ?? 0;

        return response()->json(['message' => 'Kayıt başarıyla işlendi.', 'mID' => $mID]);
    }

    public function KontrolListesiAl(Request $request)
    {
        $aktifler = DB::connection('pgsql_oft')
            ->table('oftt_kontrol_isemri')
            ->join('oftt_param_istasyon', 'oftt_param_istasyon.wstation_id', '=', 'oftt_kontrol_isemri.istasyon_id')
            ->join('users', 'users.id', '=', 'oftt_kontrol_isemri.olusturan_id')
            ->select(
                'oftt_kontrol_isemri.id',
                'oftt_kontrol_isemri.isemri_no',
                'oftt_kontrol_isemri.is_active',
                'oftt_kontrol_isemri.item_code',
                'oftt_kontrol_isemri.item_name',
                'oftt_kontrol_isemri.belge_no',
                'oftt_kontrol_isemri.cari_ad',
                'oftt_kontrol_isemri.created_at',
                'oftt_param_istasyon.tanim',
                'oftt_kontrol_isemri.istasyon_id',
                'oftt_kontrol_isemri.item_id',
                'oftt_kontrol_isemri.is_open',
                'oftt_kontrol_isemri.isemri_id',
                'oftt_kontrol_isemri.is_use_quality',
                'oftt_kontrol_isemri.is_check_quality_opr',
                'users.name',
            )
            ->get();

        return response()->json($aktifler);
    }

    public function KontrolleriAl(Request $request)
    {
        // Log::info('KontrolleriAl request:', $request->all());

        $kontroller = DB::connection('pgsql_oft')
            ->table('oftt_urun_kontrol_m')
            ->where('isemri_id', $request->isEmriId)
            ->where('istasyon_id', $request->istasyon)
            ->orderBy('revizyon', 'desc')
            ->first();

        if (!$kontroller) {
            return response()->json([
                'kontroller' => null,
                'kontrollerd' => [],
                'revizyonlar' => [],
                'message' => 'Kontrol kaydı bulunamadı',
            ], 200);
        }

        $revizyonlar = DB::connection('pgsql_oft')
            ->table('oftt_urun_kontrol_m')
            ->where('isemri_id', $request->isEmriId)
            ->where('istasyon_id', $request->istasyon)
            // ->whereNot('revizyon', 0)
            ->orderBy('revizyon', 'desc')
            ->get();

        if (!$kontroller) {
            return response()->json([
                'kontroller' => null,
                'kontrollerd' => [],
                'message' => 'Kontrol kaydı bulunamadı',
            ], 404);
        }

        $kontrollerd = DB::connection('pgsql_oft')
            ->table('oftt_urun_kontrol_d')
            ->whereRaw('LEFT(seri_no, 12) = ?', [$request->isEmriNo])
            // ->where('urun_kontrol_m_id', $kontroller->id)
            ->orderBy('seri_no', 'desc')
            ->get();

        if (!$kontrollerd) {
            $kontrollerd = [];
        }

        return response()->json([
            'kontroller' => $kontroller,
            'kontrollerd' => $kontrollerd,
            'revizyonlar' => $revizyonlar,
        ], 200);
    }

    public function UrunAgaciKaydet(Request $request)
    {
        $urunKodu = $request->input('urunKodu');
        $selectedIds = $request->input('selected_ids', []);
        $userId = (int) ($request->userId ?? 0);

        try {
            DB::connection('pgsql_oft')->beginTransaction();

            // Önce bu ürün için eski kayıtları sil
            DB::connection('pgsql_oft')
                ->table('oftt_urun_agaci_kontrol')
                ->where('urun_kodu', $urunKodu)
                ->delete();

            // Bazı ortamlarda sequence geride kalabiliyor. Primary key çakışmasını önlemek için senkronize et.
            // Not: pg_get_serial_sequence tablo için doğru sequence adını otomatik bulur.
            DB::connection('pgsql_oft')->statement(<<<'SQL'
                SELECT setval(
                    pg_get_serial_sequence('oftt_urun_agaci_kontrol','id'),
                    COALESCE((SELECT MAX(id) FROM oftt_urun_agaci_kontrol), 0)
                )
            SQL);

            // Yeni kayıtları ekle
            foreach ($selectedIds as $itemId) {
                DB::connection('pgsql_oft')
                    ->table('oftt_urun_agaci_kontrol')
                    ->insert([
                    'urun_kodu'  => $urunKodu,
                    'item_id'    => $itemId,
                    'created_at' => now(),
                    'updated_at' => now(),
                        'personel_id' => $userId,
                    ]);
            }

            DB::connection('pgsql_oft')->commit();
            return response()->json(['status' => 'ok']);
        } catch (\Throwable $e) {
            DB::connection('pgsql_oft')->rollBack();
            Log::error('UrunAgaciKaydet hata: ' . $e->getMessage(), [
                'urun_kodu' => $urunKodu,
                'selected_ids' => $selectedIds,
                'userId' => $userId,
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Ürün ağacı seçimleri kaydedilemedi.',
                'error_code' => (int) $e->getCode(),
            ], 500);
        }
    }

    public function KontrolAcKaydet(Request $request)
    {
        // Beklenen parametreler: isEmriID (zorunlu), istasyonID (opsiyonel fakat tavsiye), personelID || userId
        $isEmriID   = $request->input('isEmriID');
        $istasyonID = $request->input('istasyonID');
        $personelID = $request->input('personelID', $request->input('userId')); // Front-end userId gönderiyordu

        if (!$isEmriID) {
            return response()->json(['status' => 'error', 'message' => 'isEmriID zorunlu.'], 422);
        }

        // Log::info('KontrolAcKaydet request', [
        //     'isEmriID' => $isEmriID,
        //     'istasyonID' => $istasyonID,
        //     'personelID' => $personelID,
        // ]);

        try {
            DB::connection('pgsql_oft')->beginTransaction();

            $qry = DB::connection('pgsql_oft')
                ->table('oftt_kontrol_isemri')
                ->where('isemri_id', $isEmriID);

            if ($istasyonID) {
                $qry->where('istasyon_id', $istasyonID);
            }

            // Güncellenecek kayıt var mı?
            $varMi = (clone $qry)->count();
            if ($varMi === 0) {
                DB::connection('pgsql_oft')->rollBack();
                return response()->json(['status' => 'error', 'message' => 'Eşleşen kayıt yok.'], 404);
            }

            $guncellendi = $qry->update([
                'is_open' => 1, // integer kullan (sorgularda 1/0 ile kontrol ediliyor)
                'guncelleyen_id' => $personelID,
                'updated_at' => now(),
            ]);

            if ($guncellendi > 0) {
                DB::connection('pgsql_oft')->commit();
                return response()->json([
                    'status' => 'ok',
                    'affected' => $guncellendi,
                ]);
            }

            // Hiç satır etkilenmediyse muhtemelen kayıt zaten açık.
            DB::connection('pgsql_oft')->rollBack();
            return response()->json([
                'status' => 'noop',
                'message' => 'Kayıt zaten açık olabilir (değişiklik olmadı).'
            ], 200);
        } catch (\Exception $e) {
            DB::connection('pgsql_oft')->rollBack();
            Log::error('KontrolAcKaydet hata: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function KontrolAktifKaydet(Request $request)
    {
        // Log::info('KontrolAktifKaydet request:', $request->all());
        try {
            DB::connection('pgsql_oft')->beginTransaction();

            DB::connection('pgsql_oft')
                ->table('oftt_kontrol_isemri')
                ->where('istasyon_id', $request->istasyonID)
                ->update(['is_active' => 0]);

            if ($request->durum === 'pasif') {
                $guncellendi = DB::connection('pgsql_oft')
                    ->table('oftt_kontrol_isemri')
                    ->where('isemri_id', $request->isEmriID)
                    ->where('istasyon_id', $request->istasyonID)
                    ->update([
                        'is_open' => 0,
                        'guncelleyen_id' => $request->personelID,
                        'updated_at' => now(),
                    ]);
            } else {
                $guncellendi = DB::connection('pgsql_oft')
                    ->table('oftt_kontrol_isemri')
                    ->where('isemri_id', $request->isEmriID)
                    ->where('istasyon_id', $request->istasyonID)
                    ->update([
                        'is_active' => 1,
                        'guncelleyen_id' => $request->personelID,
                        'updated_at' => now(),
                    ]);
            }

            if ($guncellendi) {
                DB::connection('pgsql_oft')->commit();
                return response()->json(['status' => 'ok']);
            }

            DB::connection('pgsql_oft')->rollBack();
            return response()->json(['status' => 'error', 'message' => 'Kayıt bulunamadı'], 404);
        } catch (\Exception $e) {
            DB::connection('pgsql_oft')->rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // public function KontrolGerekKaydet(Request $request)
    // {
    //     // Log::info('KontrolGerekKaydet request:', $request->all());

    //     $veri = $request->all();

    //     $kayitVarmi = DB::connection('pgsql_oft')
    //         ->table('oftt_kontrol_isemri')
    //         ->where('isemri_id', $veri['isEmriID'])
    //         ->where('istasyon_id', $veri['istasyonID'])
    //         ->exists();

    //     if ($kayitVarmi === true) {
    //         Log::info('Bu istasyon için kontrol kaydı zaten var.');
    //         return response()->json(['status' => 'error', 'message' => 'Bu istasyon için kontrol kaydı zaten var.'], 400);
    //     }


    //     $kontrol_gerekli = DB::connection('pgsql')
    //         ->table('uyumsoft.invd_branch_item')
    //         ->select('is_use_quality')
    //         ->where('item_id', $veri['itemID'])
    //         ->first();

    //     $kaydet = [
    //         'isemri_id' => $veri['isEmriID'],
    //         'isemri_no' => $veri['isEmriNo'],
    //         'item_code' => $veri['urunKodu'],
    //         'item_name' => $veri['urunAdi'],
    //         'belge_no' => $veri['belgeNo'],
    //         'cari_ad' => $veri['cariAd'],
    //         'istasyon_id' => $veri['istasyonID'],
    //         'item_id' => $veri['itemID'],
    //         'is_use_quality' => $kontrol_gerekli->is_use_quality,
    //         'is_check_quality_opr' => $kontrol_gerekli->is_use_quality === 1 ? 1 : 0,
    //         'olusturan_id' => $veri['userID'],
    //         'created_at' => now(),
    //         'updated_at' => now(),
    //     ];
    //     DB::connection('pgsql_oft')
    //         ->table('oftt_kontrol_isemri')
    //         ->insert($kaydet);

    //     return response()->json(['status' => 'ok']);
    // }

    public function KontrolAktifAl(Request $request)
    {
        $ids = DB::connection('pgsql_oft')
            ->table('oftt_kontrol_isemri')
            ->select('id', 'isemri_id', 'isemri_no')
            ->where('olusturan_id', $request->userID)
            ->where('is_active', 1)
            ->first('isemri_id');

        return response()->json(['aktif' => $ids]);
    }

    public function UrunAgaciYukle(Request $request)
    {
        // Log::info($request->all());
        $urunKodu = $request->query('urunKodu');

        $ids = DB::connection('pgsql_oft')
            ->table('oftt_urun_agaci_kontrol')
            ->where('urun_kodu', $urunKodu)
            ->pluck('item_id');


        $data = DB::connection('pgsql_oft')
            ->table('oftt_urun_agaci_kontrol as kontrol')
            ->leftJoin('users as u', 'u.id', '=', 'kontrol.personel_id')
            ->select('kontrol.created_at', 'kontrol.personel_id', 'u.name as personel_adi')
            ->where('kontrol.urun_kodu', $urunKodu)
            ->first();

        return response()->json(['selected_ids' => $ids, 'data' => $data]);
    }

    public function topluKaydet(Request $request)
    {
        $veriler = $request->all(); // JSON array bekleniyor

        if (!is_array($veriler)) {
            return response()->json(['success' => false, 'message' => 'Geçersiz veri formatı. Dizi bekleniyor.'], 422);
        }

        $eklendi = 0;
        $hatali = [];

        foreach ($veriler as $index => $veri) {
            if (!is_array($veri)) {
                $hatali[] = $index;
                continue;
            }

            // Zorunlu alanlar kontrolü
            $gerekli = ['urun_kontrol_m_id', 'user_id', 'serino', 'sonuc'];
            $eksik = array_diff($gerekli, array_keys($veri));
            if (!empty($eksik)) {
                $hatali[] = $index;
                continue;
            }

            // Not alanı her zaman string olmalı
            $rawNote = $veri['aciklama'] ?? null;
            if (is_array($rawNote)) {
                // Basitçe virgülle birleştir (iç içe diziler varsa JSON'a çevir)
                $flat = [];
                foreach ($rawNote as $k => $v) {
                    if (is_scalar($v) || $v === null) {
                        $flat[] = (string) $v;
                    } else {
                        $flat[] = json_encode($v, JSON_UNESCAPED_UNICODE);
                    }
                }
                $note = implode(', ', array_filter($flat, fn($x) => $x !== ''));
            } elseif (is_object($rawNote)) {
                $note = json_encode($rawNote, JSON_UNESCAPED_UNICODE);
            } else {
                $note = $rawNote; // string ya da null
            }

            try {
                DB::connection('pgsql_oft')->table('oftt_urun_kontrol_d')->insert([
                    'urun_kontrol_m_id' => (int) $veri['urun_kontrol_m_id'],
                    'olusturan_id'      => (int) $veri['user_id'],
                    'seri_no'           => (string) $veri['serino'],
                    'sonuc'             => (string) $veri['sonuc'],
                    'note'              => $note,
                    'kontrol_tarihi'    => now(),
                ]);
                $eklendi++;
            } catch (\Throwable $e) {
                Log::error('Toplu kaydet hata: satır=' . $index . ' mesaj=' . $e->getMessage());
                $hatali[] = $index;
            }
        }

        return response()->json([
            'success' => empty($hatali),
            'message' => 'Toplu kayıt tamamlandı.',
            'eklenen' => $eklendi,
            'hatali_indeksler' => $hatali,
        ]);
    }

    public function KontrolSil($id)
    {
        // Log::info('Kontrol silme isteği', ['id' => $id]);
        try {
            DB::connection('pgsql_oft')->table('oftt_urun_kontrol_d')->where('id', $id)->delete();

            return response()->json(['success' => true, 'message' => 'Kayıt başarıyla silindi.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Silme işlemi başarısız.', 'error' => $e->getMessage()], 500);
        }
    }

    public function guncelleIsCheckQualityOpr(Request $request)
    {
        // Log::info($request->all());
        $guncellendi = DB::connection('pgsql_oft')
            ->table('oftt_kontrol_isemri')
            ->where('isemri_id', $request->isEmriID)
            ->where('istasyon_id', $request->istasyonID)
            ->update([
                'is_check_quality_opr' => $request->is_check_quality_opr === '1' ? true : false,
                'guncelleyen_id' => $request->personelID,
                'updated_at' => now(),
            ]);

        if ($guncellendi) {
            return response()->json(['status' => 'ok']);
        }

        return response()->json(['status' => 'error', 'message' => 'Kayıt bulunamadı'], 404);
    }

    public function gosterPdf(Request $request)
    {
        $kaynak = '\\\\192.6.2.4\\canovate_elektronik\\12_DOKUMANTASYON\\YAYINLI RESİMLER\\ÜRETİM\\00\\00 0000\\000392P00.PDF';
        $hedefKlasor = storage_path('app/public/temp');
        $hedef = $hedefKlasor . '/000392P00.PDF';

        // Hedef klasör yoksa oluştur
        if (!File::exists($hedefKlasor)) {
            File::makeDirectory($hedefKlasor, 0755, true);
        }

        // Kopyala
        if (!file_exists($hedef)) {
            if (!copy($kaynak, $hedef)) {
                return response()->json(['error' => 'Dosya kopyalanamadı.'], 500);
            }
        }

        // PDF response’u ver
        $response = response()->file($hedef, [
            'Content-Type' => 'application/pdf',
        ]);

        // PDF gösterildikten sonra silinsin (response bittikten sonra)
        register_shutdown_function(function () use ($hedef) {
            if (file_exists($hedef)) {
                unlink($hedef); // dosyayı sil
            }
        });

        return $response;
    }

    public function hataBildir(Request $request)
    {
        $validated = $request->validate([
            'hata_sebebi_id' => 'required|integer',
            'note' => 'nullable|string',
            'alicilar' => 'required|array|min:1',
            'gonderen_id' => 'required|integer',
            'is_emri_no' => 'required|string',
            'urun_kodu' => 'required|string',
            'urun_adi' => 'required|string',
        ]);

        // Hata sebebi adı
        $hataSebebi = DB::table('oftt_param_hata_sebepleri')
            ->where('id', $validated['hata_sebebi_id'])
            ->value('tanim');

        // Gönderen kullanıcı bilgisi
        $gonderen = DB::table('users')
            ->where('id', $validated['gonderen_id'])
            ->first();

        // Bildirimi veritabanına kaydet
        $bildirimId = DB::table('oftt_param_hata_bildirimleri')->insertGetId([
            'hata_sebebi_id' => $validated['hata_sebebi_id'],
            'note' => $validated['note'],
            'is_emri_no' => $validated['is_emri_no'],
            'urun_kodu' => $validated['urun_kodu'],
            'urun_adi' => $validated['urun_adi'],
            'gonderen_id' => $validated['gonderen_id'],
            'created_at' => now(),
        ]);

        $mailData = [
            'bildirim_id' => $bildirimId,
            'hata_sebebi' => $hataSebebi,
            'note' => $validated['note'],
            'gonderen_adi' => $gonderen->name ?? 'Bilinmiyor',
            'gonderen_email' => $gonderen->email ?? 'bilinmiyor@example.com',
            'is_emri_no' => $validated['is_emri_no'],
            'urun_kodu' => $validated['urun_kodu'],
            'urun_adi' => $validated['urun_adi'],
        ];

        foreach ($validated['alicilar'] as $email) {
            Mail::to($email)->send(new HataBildirimiMail($mailData));
        }

        return response()->json(['success' => true]);
    }
}
