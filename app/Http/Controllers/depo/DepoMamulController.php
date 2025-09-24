<?php

namespace App\Http\Controllers\depo;

use App\Http\Controllers\Controller;
use App\Models\Emir;
use App\Models\StokHrkt;
use App\Models\IskrtHrkt;
use App\Models\User;
use App\Models\Mamul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\LogService;

use function Laravel\Prompts\search;

class DepoMamulController extends Controller
{

    public function UrunSorgula(Request $request)
    {
        // Log::info($request->all());

        $barkod = $request->itemID;

        $item = DB::connection('pgsql')->table('uyumsoft.invd_item as ii')
            ->leftJoin('uyumsoft.invd_item_barcode as ba', 'ii.item_id', '=', 'ba.item_id')
            ->select('ii.item_code', 'ii.item_name', 'ii.item_id', 'ii.item_name2')
            ->where('ii.item_code', $barkod)
            ->orWhere('ba.barcode', $barkod)
            ->first();

        if (!$item) {
            $item = DB::connection('pgsql')->table('uyumsoft.invd_serial_m as im')
                ->leftJoin('uyumsoft.invd_item as ii', 'im.item_id', '=', 'ii.item_id')
                ->select('ii.item_code', 'ii.item_name', 'ii.item_id', 'im.serial_no', 'ii.item_name2')
                ->where('im.serial_no', $barkod)
                ->first();
        }

        if (!$item) {
            return response()->json(['error' => 'Ürün bulunamadı'], 404);
        }

        $stoklar = DB::connection('pgsql')
            ->table('uyumsoft.invd_bwh_item as itm')
            ->join('uyumsoft.invd_whouse as whs', 'itm.whouse_id', '=', 'whs.whouse_id')
            ->leftJoin('uyumsoft.gnld_branch as brc', 'itm.branch_id', '=', 'brc.branch_id')
            ->leftJoin('uyumsoft.invd_item as it', 'it.item_id', '=', 'itm.item_id')
            ->leftJoin('uyumsoft.invd_unit as un', 'it.unit_id', '=', 'un.unit_id')
            ->select(
                'brc.branch_desc',
                'itm.qty_prm',
                'whs.whouse_code',
                'whs.description',
                'whs.whouse_width',
                'un.unit_code',
                'itm.add_string01',
            )
            ->where('itm.item_id', $item->item_id)
            ->where('itm.qty_prm', '>', 0)
            ->whereIn('whs.whouse_width', [1, 2, 3, 4, 5])
            ->get();

        $toplama = $stoklar->where('qty_prm', '>', 0)->where('whouse_width', '1')->sum('qty_prm');
        $toplami = $stoklar->where('qty_prm', '>', 0)->where('whouse_width', '2')->sum('qty_prm');



        $detay = $this->SayimBilgileri($item->item_id);
        $toplamSayim = $detay->where('qty_prm', '>', 0)->sum('qty_prm');

        return response()->json([
            'urun' => $item,
            'stoklar' => $stoklar,
            'toplama' => $toplama,
            'toplami' => $toplami,
            'detay' => $detay,
            'toplamSayim' => $toplamSayim,
        ]);
    }

    public function SayimBilgileri($itemId)
    {
        $results = DB::connection('pgsql')
            ->table('uyumsoft.invt_cycle_count_d as cd')
            ->leftJoin('uyumsoft.invt_cycle_count_m as cm', 'cd.cycle_count_m_id', '=', 'cm.cycle_count_m_id')
            ->leftJoin('uyumsoft.invd_item as ii', 'cd.item_id', '=', 'ii.item_id')
            ->leftJoin('uyumsoft.invd_whouse as iw', 'cd.whouse_id', '=', 'iw.whouse_id')
            ->select([
                DB::raw('row_number() OVER (ORDER BY cd.cycle_count_d_id DESC) AS id'),
                'cm.doc_no',
                'cm.note1 as sorumlu',
                'iw.whouse_code',
                'iw.description',
                'cd.create_date',
                'cd.serial_m_id',
                'cd.barcode',
                'ii.item_id',
                'ii.item_code',
                'ii.item_name',
                'cd.qty_prm',
                'cd.qty',
                'cd.qty_free_prm',
                'cd.qty_free_sec',
                'cd.create_user_id',
                'cd.update_user_id',
                'cd.update_date',
                'cd.whouse_id',
                'cd.location_id',
                'cd.us_id',
                'cd.unit_id',
                'cd.cycle_count_m_id',
                'cd.cycle_count_d_id',
                'cd.note1',
                'cd.co_id',
                'cd.branch_id',
                'cm.is_passive',
            ])
            ->where('cm.is_passive', 0)
            ->where('cd.item_id', $itemId)
            ->orderByDesc('cd.cycle_count_d_id')
            ->get();

        return $results;
    }

    public function rafGir(Request $request)
    {
        // Log::info($request->all());

        $raf = $request->input('raf');
        $itemID = $request->input('itemID');
        $depoID = $request->input('depoID.whouse_id');

        // Log::info("Raf: {$raf}");
        // Log::info("ItemID: {$itemID}");

        DB::beginTransaction();

        try {
            // $path = DB::connection('pgsql')->select("SHOW search_path");
            // Log::info("PostgreSQL search_path: " . json_encode($path));

            $updated = DB::connection('pgsql')
                ->table('uyumsoft.invd_branch_item')
                ->where('item_id', $itemID)
                ->update(['add_string01' => (string) $raf]);

            $updated = DB::connection('pgsql')
                ->table('uyumsoft.invd_bwh_item')
                ->where('item_id', $itemID)
                ->where('whouse_id', $depoID)
                ->update(['add_string01' => (string) $raf]);

            if ($updated === 0) {
                // Hiç satır güncellenmediyse hata döndür:
                throw new \Exception("Verilen item_id ile eşleşen kayıt bulunamadı.");
            }

            DB::commit();
            return response()->json(['message' => 'Kayıt başarıyla yapıldı.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('DB Hata: ' . $e->getMessage());

            return response()->json([
                'message' => 'Kayıt sırasında hata oluştu.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function StokParamAl()
    {
        $ktg1 = DB::connection('pgsql')
            ->table('uyumsoft.gnld_categories')
            ->select('description as kategori_ad1')
            ->where('categories_page', 3)
            ->where('step', 1)
            ->groupBy('description')
            ->orderBy('description')
            ->get();

        return response()->json([
            'ktg1' => $ktg1,
        ]);
    }

    public function StokListele(Request $request)
    {
        Log::info('StokListele request', $request->all());

        ini_set('max_execution_time', 1500); // 5 dakika
        // Büyük veri setleri için server-side paging
        $take = (int) $request->input('take', 100);
        $skip = (int) $request->input('skip', 0);
        if ($take < 1) {
            $take = 100;
        }
        // Üst sınır koymak iyi bir pratik (istemcinin aşırı yüklenmesini engellemek için)
        if ($take > 2000) {
            $take = 2000;
        }

        $sortField = $request->input('sortField', 'item_id');
        $sortOrder = strtolower($request->input('sortOrder', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = [
            'item_id',
            'item_code',
            'item_name',
            'item_name2',
            'add_string07',
            'add_string08',
            'add_dec07',
            'add_dec08',
            'kategori_ad1',
            'kategori_ad2',
            'kategori_ad3',
            'kategori_ad4',
            'kategori_ad5',
            'kategori_ad6',
            'kategori_ad7',
            'kategori_ad8',
            'kategori_ad9',
            'kategori_ad10',
            'kategori_ad11',
            'kategori_ad12',
            'kategori_ad13',
            'kategori_ad14',
            'kategori_ad15',
            'kategori_ad16',
            'kategori_ad17',
            'kategori_ad18',
            'kategori_ad19',
            'kategori_ad20',
            'ana_depo_miktar',
            'ist_depo_miktar',
            'satinalma',
            'satislar',
            'min_seviye',
            'max_seviye'
        ];
        if (!in_array($sortField, $allowedSorts, true)) {
            $sortField = 'item_id';
        }

        $query = DB::connection('pgsql')
            ->table('uyumsoft.invd_item as itm')
            ->leftJoin('uyumsoft.gnld_categories as cat1', function ($join) {
                $join->on('itm.categories1_id', '=', 'cat1.categories_id')
                    ->where('cat1.categories_page', 3)
                    ->where('cat1.step', 1);
            })
            ->leftJoin('uyumsoft.gnld_categories as cat2', function ($join) {
                $join->on('itm.categories2_id', '=', 'cat2.categories_id')
                    ->where('cat2.categories_page', 3)
                    ->where('cat2.step', 2);
            })
            ->leftJoin('uyumsoft.gnld_categories as cat3', function ($join) {
                $join->on('itm.categories3_id', '=', 'cat3.categories_id')
                    ->where('cat3.categories_page', 3)
                    ->where('cat3.step', 3);
            })
            ->leftJoin('uyumsoft.gnld_categories as cat4', function ($join) {
                $join->on('itm.categories4_id', '=', 'cat4.categories_id')
                    ->where('cat4.categories_page', 3)
                    ->where('cat4.step', 4);
            })
            ->leftJoin('uyumsoft.gnld_categories as cat5', function ($join) {
                $join->on('itm.categories5_id', '=', 'cat5.categories_id')
                    ->where('cat5.categories_page', 3)
                    ->where('cat5.step', 5);
            })
            ->leftJoin('uyumsoft.gnld_categories as cat6', function ($join) {
                $join->on('itm.categories6_id', '=', 'cat6.categories_id')
                    ->where('cat6.categories_page', 3)
                    ->where('cat6.step', 6);
            })
            ->leftJoin('uyumsoft.gnld_categories as cat7', function ($join) {
                $join->on('itm.categories7_id', '=', 'cat7.categories_id')
                    ->where('cat7.categories_page', 3)
                    ->where('cat7.step', 7);
            })
            ->leftJoin('uyumsoft.gnld_categories as cat8', function ($join) {
                $join->on('itm.categories8_id', '=', 'cat8.categories_id')
                    ->where('cat8.categories_page', 3)
                    ->where('cat8.step', 8);
            })
            ->leftJoin('uyumsoft.gnld_categories as cat9', function ($join) {
                $join->on('itm.categories9_id', '=', 'cat9.categories_id')
                    ->where('cat9.categories_page', 3)
                    ->where('cat9.step', 9);
            })
            ->leftJoin('uyumsoft.gnld_categories as cat10', function ($join) {
                $join->on('itm.categories10_id', '=', 'cat10.categories_id')
                    ->where('cat10.categories_page', 3)
                    ->where('cat10.step', 10);
            })
            ->leftJoin('uyumsoft.gnld_categories as cat11', function ($join) {
                $join->on('itm.categories11_id', '=', 'cat11.categories_id')
                    ->where('cat11.categories_page', 3)
                    ->where('cat11.step', 11);
            })
            ->leftJoin('uyumsoft.gnld_categories as cat12', function ($join) {
                $join->on('itm.categories12_id', '=', 'cat12.categories_id')
                    ->where('cat12.categories_page', 3)
                    ->where('cat12.step', 12);
            })
            ->leftJoin('uyumsoft.gnld_categories as cat13', function ($join) {
                $join->on('itm.categories13_id', '=', 'cat13.categories_id')
                    ->where('cat13.categories_page', 3)
                    ->where('cat13.step', 13);
            })
            ->leftJoin('uyumsoft.gnld_categories as cat14', function ($join) {
                $join->on('itm.categories14_id', '=', 'cat14.categories_id')
                    ->where('cat14.categories_page', 3)
                    ->where('cat14.step', 14);
            })
            ->leftJoin('uyumsoft.gnld_categories as cat15', function ($join) {
                $join->on('itm.categories15_id', '=', 'cat15.categories_id')
                    ->where('cat15.categories_page', 3)
                    ->where('cat15.step', 15);
            })
            ->leftJoin('uyumsoft.gnld_categories as cat16', function ($join) {
                $join->on('itm.categories16_id', '=', 'cat16.categories_id')
                    ->where('cat16.categories_page', 3)
                    ->where('cat16.step', 16);
            })
            ->leftJoin('uyumsoft.gnld_categories as cat17', function ($join) {
                $join->on('itm.categories17_id', '=', 'cat17.categories_id')
                    ->where('cat17.categories_page', 3)
                    ->where('cat17.step', 17);
            })
            ->leftJoin('uyumsoft.gnld_categories as cat18', function ($join) {
                $join->on('itm.categories18_id', '=', 'cat18.categories_id')
                    ->where('cat18.categories_page', 3)
                    ->where('cat18.step', 18);
            })
            ->leftJoin('uyumsoft.gnld_categories as cat19', function ($join) {
                $join->on('itm.categories19_id', '=', 'cat19.categories_id')
                    ->where('cat19.categories_page', 3)
                    ->where('cat19.step', 19);
            })
            ->leftJoin('uyumsoft.gnld_categories as cat20', function ($join) {
                $join->on('itm.categories20_id', '=', 'cat20.categories_id')
                    ->where('cat20.categories_page', 3)
                    ->where('cat20.step', 20);
            })
            ->leftJoin('uyumsoft.OFTV_ITEMS_SUMS as otm', function ($join) {
                $join->on('itm.item_id', '=', 'otm.item_id');
            })
            ->select(
                'itm.item_id',
                'itm.item_code',
                'itm.item_name',
                'itm.item_name2',
                'itm.add_string07',
                'itm.add_string08',
                'itm.add_dec07',
                'itm.add_dec08',
                'cat1.description as kategori_ad1',
                'cat2.description as kategori_ad2',
                'cat3.description as kategori_ad3',
                'cat4.description as kategori_ad4',
                'cat5.description as kategori_ad5',
                'cat6.description as kategori_ad6',
                'cat7.description as kategori_ad7',
                'cat8.description as kategori_ad8',
                'cat9.description as kategori_ad9',
                'cat10.description as kategori_ad10',
                'cat11.description as kategori_ad11',
                'cat12.description as kategori_ad12',
                'cat13.description as kategori_ad13',
                'cat14.description as kategori_ad14',
                'cat15.description as kategori_ad15',
                'cat16.description as kategori_ad16',
                'cat17.description as kategori_ad17',
                'cat18.description as kategori_ad18',
                'cat19.description as kategori_ad19',
                'cat20.description as kategori_ad20',
                'otm.ana_depo_miktar',
                'otm.ist_depo_miktar',
                'otm.satinalma',
                'otm.satislar',
                'otm.min_seviye',
                'otm.max_seviye'
            )
            // kategoriad1 null/boş/"null" (string) geldiğinde filtre uygulama; aksi halde cat1.description'a göre filtrele
            ->when((function () use ($request) {
                $val = $request->input('kategoriad1');
                if (is_string($val)) $val = trim($val);
                return !is_null($val) && $val !== '' && strtolower((string)$val) !== 'null';
            })(), function ($query) use ($request) {
                return $query->where('cat1.description', $request->input('kategoriad1'));
            });

        // count ve get için güvenli kullanım (count sırasında seçimi değiştirmemek için klonla)
        $total = (clone $query)->count();
        // Sıralama + sayfalama uygula
        $data = $query
            ->orderBy($sortField, $sortOrder)
            ->offset($skip)
            ->limit($take)
            ->get();

        Log::info('StokListele toplam', ['count' => $total]);
        // Büyük veri loglamak yerine örnek satır sayısı
        Log::info('StokListele data sample', ['rows' => count($data)]);

        return response()->json([
            'data' => $data,
            'total' => $total,
        ]);
    }

    public function KategoriAl(Request $request)
    {
        // Log::info($request->all());

        $kategoriler = DB::connection('pgsql')
            ->table('uyumsoft.gnld_categories')
            ->select('categories_id', 'description')
            ->where('categories_page', 3)
            ->where('step', $request->kategoriad)
            ->get();

        return response()->json([
            'kategoriler' => $kategoriler,
        ]);
    }

    public function DepolariAl(Request $request)
    {
        // Log::info($request->all());

        $depolar = DB::connection('pgsql')
            ->table('uyumsoft.invd_whouse')
            ->select('whouse_id', 'description')
            ->where('whouse_width', '1')
            ->orWhere('whouse_width', '2')
            ->orderBy('description')
            ->get();

        return response()->json([
            'depolar' => $depolar,
        ]);
    }

    public function KategoriGuncelle(Request $request, LogService $logService)
    {
        try {
            $updateData = $request->input('updateData');

            if (empty($updateData) || !is_iterable($updateData)) {
                return response()->json(['error' => 'Geçersiz veri!'], 400);
            }

            DB::beginTransaction();

            foreach ($updateData as $data) {
                $item_id = $data['itemID'] ?? 0;
                $alan = $data['alan'] ?? null;
                $deger = $data['deger'] ?? null;

                if (!$item_id || !$alan) {
                    Log::error("Eksik veri: " . json_encode($data));
                    continue;
                }

                $updated = DB::connection('pgsql')->table('uyumsoft.invd_item')
                    ->where('item_id', $item_id)
                    ->update([
                        $alan => $deger,
                    ]);

                if (!$updated) {
                    Log::warning("Güncelleme başarısız: item_id = $item_id, alan = $alan");
                }
            }

            $logService->LogKaydet("Stoklar", "Kategori Güncelleme", $request->userID ?? auth()->id(), $request->getClientIp());

            DB::commit();
            return response()->json(['success' => 'Veriler başarıyla güncellendi.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Hata oluştu: " . $e->getMessage());
            return response()->json(['error' => 'Bir hata oluştu: ' . $e->getMessage()], 500);
        }
    }

    public function StokDetayAl(Request $request)
    {
        // Log::info('Detay:', $request->all());

        if (!$request->has('itemID')) {
            return response()->json(['error' => 'itemID parametresi eksik'], 400);
        }

        // Log::info('StokDetayAl isteği alındı', ['itemID' => $request->itemID]);

        try {
            $siparis = DB::connection('pgsql')
                ->table('uyumsoft.zz_bk_wbservice_invoice_details')
                ->select(
                    DB::raw('CAST(fatura_tarih AS DATE) AS fatura_tarih'),
                    'fatura_no',
                    'CARI_ADI',
                    'birim',
                    'miktar',
                    'USD_NET_TUTAR',
                    DB::raw('(CASE WHEN "USD_NET_TUTAR" = 0 THEN 0 ELSE "USD_NET_TUTAR" / miktar END) AS birim_fiyat')
                )
                ->where('purchase_sales', 2) // Satış verileri
                ->where('item_id', $request->itemID)
                ->orderByDesc('fatura_tarih')
                ->get();

            // Log::info('siparis:', [$siparis]);

            $satinalma = DB::connection('pgsql')
                ->table('uyumsoft.zz_bk_wbservice_invoice_details')
                ->select(
                    DB::raw('CAST(fatura_tarih AS DATE) AS fatura_tarih'),
                    'fatura_no',
                    'CARI_ADI',
                    'birim',
                    'miktar',
                    'USD_NET_TUTAR',
                    DB::raw('(CASE WHEN "USD_NET_TUTAR" = 0 THEN 0 ELSE "USD_NET_TUTAR" / miktar END) AS birim_fiyat')
                )
                ->where('purchase_sales', 1)
                ->where('item_id', $request->itemID)
                ->orderByDesc('fatura_tarih')
                ->get();

            $toplamAlim = $satinalma->sum('miktar');
            $toplamSiparis = $siparis->sum('miktar');

            // $sarflar = DB::connection('pgsql')
            // ->table('prdt_worder_bom_d as pwb')
            // ->leftJoin('invd_whouse as wh', 'wh.whouse_id', '=', 'pwb.whouse_id')
            // ->leftJoin('gnld_company as co', 'co.co_id', '=', 'pwb.co_id')
            // ->leftJoin('prdt_worder_m as wm', 'wm.worder_m_id', '=', 'pwb.worder_m_id')
            // ->leftJoin('prdt_worder_op_d as wop', 'wop.worder_m_id', '=', 'wm.worder_m_id')
            // ->select(
            //     'co.co_desc',
            //     'pwb.create_date',
            //     'wm.worder_no',
            //     DB::raw('(pwb.qty_base_bom * wm.qty_man) AS sarf'),
            //     'wh.description'
            // )
            // ->where('pwb.item_id', $request->itemID)
            // // ->where('wm.open_close', 2)
            // ->where('wm.is_sub_worder', 0)
            // ->where('wop.is_last_opr_in_route', 1)
            // ->orderByDesc('pwb.create_date')
            // ->distinct()
            // ->get();

            $sarflar = DB::connection('pgsql')
                ->table('uyumsoft.prdt_worder_bom_d as pwb')
                ->leftJoin('uyumsoft.invd_whouse as wh', 'wh.whouse_id', '=', 'pwb.whouse_id')
                ->leftJoin('uyumsoft.gnld_company as co', 'co.co_id', '=', 'pwb.co_id')
                ->leftJoin('uyumsoft.prdt_worder_m as wm', 'wm.worder_m_id', '=', 'pwb.worder_m_id')
                ->leftJoin('uyumsoft.prdt_worder_op_d as wop', 'wop.worder_m_id', '=', 'wm.worder_m_id')
                ->leftJoin('uyumsoft.prdt_worder_sales_rel as wsr', 'wsr.worder_m_id', '=', 'wm.worder_m_id')
                ->leftJoin('uyumsoft.psmt_order_m as pom', 'pom.order_m_id', '=', 'wsr.order_m_id')
                ->leftJoin('uyumsoft.find_entity as fe', 'fe.entity_id', '=', 'pom.entity_id')
                ->select(
                    'co.co_desc',
                    'pwb.create_date',
                    'wm.worder_no',
                    DB::raw('(pwb.qty_base_bom * wm.qty_man) AS sarf'),
                    'wh.description',
                    'fe.entity_name AS cari_ad'
                )
                ->where('pwb.item_id', $request->itemID)
                ->where('wm.is_sub_worder', 0)
                ->where('wop.is_last_opr_in_route', 1)
                ->orderByDesc('pwb.create_date')
                ->distinct()
                ->get();

            $toplamSarf = $sarflar->sum('sarf');

            $plan = DB::connection('pgsql')
                ->table('uyumsoft.prdt_worder_bom_d as pwb')
                ->leftJoin('uyumsoft.invd_whouse as wh', 'wh.whouse_id', '=', 'pwb.whouse_id')
                ->leftJoin('uyumsoft.gnld_company as co', 'co.co_id', '=', 'pwb.co_id')
                ->leftJoin('uyumsoft.prdt_worder_m as wm', 'wm.worder_m_id', '=', 'pwb.worder_m_id')
                ->leftJoin('uyumsoft.prdt_worder_op_d as wop', 'wop.worder_m_id', '=', 'wm.worder_m_id')
                ->leftJoin('uyumsoft.prdt_worder_sales_rel as wsr', 'wsr.worder_m_id', '=', 'wm.worder_m_id')
                ->leftJoin('uyumsoft.psmt_order_m as pom', 'pom.order_m_id', '=', 'wsr.order_m_id')
                ->leftJoin('uyumsoft.find_entity as fe', 'fe.entity_id', '=', 'pom.entity_id')
                ->select(
                    'co.co_desc',
                    'pwb.create_date',
                    'wm.worder_no',
                    DB::raw('(pwb.qty_base_bom * (wm.qty - wm.qty_man)) AS sarf'),
                    'wh.description',
                    'fe.entity_name AS cari_ad'
                )
                ->where('pwb.item_id', $request->itemID)
                ->where('wm.open_close', 1)
                ->where('wm.is_sub_worder', 0)
                ->where('wop.is_last_opr_in_route', 1)
                ->orderByDesc('pwb.create_date')
                ->distinct()
                ->get();

            $toplamPlan = $plan->sum('sarf');

            $fisler = DB::connection('pgsql')
                ->table('uyumsoft.OFTV_SATINALMA_SIPARISLERI')
                ->select(
                    'FIRMA',
                    'teslim_tarihi',
                    'BELGE_NO',
                    'CARI_AD',
                    'KALAN_MIKTAR',
                    'USD_NET_TUTAR',
                    DB::raw('(CASE WHEN "USD_NET_TUTAR" = 0 THEN 0 ELSE "USD_NET_TUTAR" / "MIKTAR" END) AS birim_fiyat')
                )
                ->where('ACIK_KAPALI', 'Açık')
                ->where('STOK_HIZMET_ID', $request->itemID)
                ->orderBy('teslim_tarihi', 'desc')
                ->get();

            $toplamFis = $fisler->sum('KALAN_MIKTAR');


            $talepler = DB::connection('pgsql')
                ->table('uyumsoft.zz_bk_tumtalepler')
                ->where('item_id', $request->itemID)
                ->orderBy('talep_tarihi', 'desc')
                ->get();

            $toplamTalep = $talepler->sum('miktar');


            // Yıl bilgilerini al
            $simdikiYil = date('Y');
            $gecenYil = date('Y') - 1;

            $sonuc = DB::connection('pgsql')->select("
            WITH son_12_ay AS (
              SELECT
                item_id,
                DATE_TRUNC('month', doc_date)::date AS ay,
                SUM(CASE WHEN tplus_minus = 1 THEN qty ELSE 0 END) AS giren_miktar,
                SUM(CASE WHEN tplus_minus = 4 THEN qty ELSE 0 END) AS cikan_miktar
              FROM uyumsoft.invt_item_d
              WHERE item_id = :itemID
                AND doc_date >= (CURRENT_DATE - INTERVAL '12 months')
              GROUP BY item_id, DATE_TRUNC('month', doc_date)
            ),
            yillik_toplamlar AS (
              SELECT
                item_id,
                EXTRACT(YEAR FROM doc_date)::int AS yil,
                SUM(CASE WHEN tplus_minus = 1 THEN qty ELSE 0 END) AS toplam_giren,
                SUM(CASE WHEN tplus_minus = 4 THEN qty ELSE 0 END) AS toplam_cikan
              FROM uyumsoft.invt_item_d
              WHERE item_id = :itemID
                AND EXTRACT(YEAR FROM doc_date) IN (:simdikiYil, :gecenYil)
              GROUP BY item_id, EXTRACT(YEAR FROM doc_date)
            )
        
            SELECT
              s.ay,
              s.giren_miktar,
              s.cikan_miktar,
              yt_now.toplam_giren AS toplam_giren_now,
              yt_now.toplam_cikan AS toplam_cikan_now,
              yt_prev.toplam_giren AS toplam_giren_prev,
              yt_prev.toplam_cikan AS toplam_cikan_prev
            FROM son_12_ay s
            LEFT JOIN yillik_toplamlar yt_now
              ON yt_now.yil = :simdikiYil AND yt_now.item_id = s.item_id
            LEFT JOIN yillik_toplamlar yt_prev
              ON yt_prev.yil = :gecenYil AND yt_prev.item_id = s.item_id
            ORDER BY s.ay DESC
        ", [
                'itemID' => $request->itemID,
                'simdikiYil' => $simdikiYil,
                'gecenYil' => $gecenYil
            ]);


            $girisSatiri = [
                'hareket' => 'Giriş',
                strval(date('Y')) => 0, // Örn: 2025
                strval(date('Y') - 1) => 0, // Örn: 2024
            ];

            $cikisSatiri = [
                'hareket' => 'Çıkış',
                strval(date('Y')) => 0,
                strval(date('Y') - 1) => 0,
            ];

            // Ay formatları: "YY-MM"
            for ($i = 11; $i >= 0; $i--) {
                $date = now()->startOfMonth()->subMonths($i);
                $key = $date->format('y-m');
                $girisSatiri[$key] = 0;
                $cikisSatiri[$key] = 0;
            }

            // Şimdi $sonuc içindeki verileri satırlara yazalım
            foreach ($sonuc as $row) {
                $ayKey = date('y-m', strtotime($row->ay));

                $girisSatiri[$ayKey] = $row->giren_miktar ?? 0;
                $cikisSatiri[$ayKey] = $row->cikan_miktar ?? 0;

                $girisSatiri[strval(date('Y'))] = $row->toplam_giren_now ?? $girisSatiri[strval(date('Y'))];
                $girisSatiri[strval(date('Y') - 1)] = $row->toplam_giren_prev ?? $girisSatiri[strval(date('Y') - 1)];

                $cikisSatiri[strval(date('Y'))] = $row->toplam_cikan_now ?? $cikisSatiri[strval(date('Y'))];
                $cikisSatiri[strval(date('Y') - 1)] = $row->toplam_cikan_prev ?? $cikisSatiri[strval(date('Y') - 1)];
            }

            // Son olarak tek dizide toplayalım
            $veri = [
                $girisSatiri,
                $cikisSatiri
            ];

            // Log::info('sonuc:', [$veri]);
            $aylikHareket = [];

            foreach ($girisSatiri as $key => $girisDeger) {
                if (preg_match('/^\d{2}-\d{2}$/', $key)) {
                    $cikisDeger = $cikisSatiri[$key] ?? 0;

                    $aylikHareket[] = [
                        'ay' => $key,
                        'girisDeger' => (float) $girisDeger,
                        'cikisDeger' => (float) $cikisDeger,
                    ];
                }
            }

            $acikSiparisler = DB::connection('pgsql')
                ->table('uyumsoft.OFTV_MUSTERI_SIPARISLERI')
                ->select(
                    'BELGE_NO',
                    'BELGE_TARIHI',
                    'teslim_tarihi',
                    'CARI_AD',
                    'CARI_TIPI',
                    'ODEME_VADESI',
                    'satici_adi',
                    'sip_olusturan',
                    'BIRIM_KOD',
                    'MIKTAR',
                    'KALAN_MIKTAR',
                    'USD_NET_TUTAR',
                    DB::raw('(CASE WHEN "USD_NET_TUTAR" = 0 THEN 0 ELSE "USD_NET_TUTAR" / "MIKTAR" END) AS birim_fiyat')
                )
                ->where('ACIK_KAPALI', 'Açık')
                ->where('STOK_HIZMET_ID', $request->itemID)
                ->orderByDesc('BELGE_TARIHI')
                ->get();

            $toplamSatisFis = $acikSiparisler->sum('KALAN_MIKTAR');

            // Log::info('aylık hareket:', [$aylikHareket]);

            return response()->json([
                'siparis' => $siparis,
                'satinalma' => $satinalma,
                'toplamAlim' => $toplamAlim,
                'toplamSiparis' => $toplamSiparis,
                'sarflar' => $sarflar,
                'toplamSarf' => $toplamSarf,
                'plan' => $plan,
                'toplamPlan' => $toplamPlan,
                'fisler' => $fisler,
                'toplamFis' => $toplamFis,
                'talepler' => $talepler,
                'toplamTalep' => $toplamTalep,
                'veri' => $veri,
                'aylikHareket' => $aylikHareket,
                'acikSiparisler' => $acikSiparisler,
                'toplamSatisFis' => $toplamSatisFis,
            ], 200);
        } catch (\Exception $e) {
            Log::error('StokDetayAl hata', [
                'itemID' => $request->itemID,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Detayı alınırken hata oluştu!',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
