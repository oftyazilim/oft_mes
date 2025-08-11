<?php

namespace App\Http\Controllers\planlama;

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

class Uretimler extends Controller
{

  public function getData()
  {
    $data = DB::table('OFTV_01_STOKHRKT')
      ->orderBy('ID', 'desc')
      ->get();

    return response()->json([
      'data' => $data,
    ], 200);
  }

  public function getDataIskarta()
  {
    $data = DB::table('OFTV_01_ISKRTHRKT')
      ->orderBy('ID', 'desc')
      ->get();

    return response()->json([
      'data' => $data,
    ], 200);
  }

  public function kayitSilIskarta(Request $request, $id)
  {
    $kayitid = (int)$id;
    $userID = $request->header('userID');

    $hrkt = IskrtHrkt::select('ISEMRIID', 'MIKTAR', 'STOKID')->where('ID', $kayitid)->first();
    try {

      $emir = Emir::find($hrkt->ISEMRIID);
      $emir->HURDAMIKTAR -= (int)$hrkt->MIKTAR;
      $emir->save();

      $hrkte = IskrtHrkt::updateOrCreate(
        ['ID' => $kayitid],
        [
          'SILINDI' => 1,
          'SILENID' => $userID,
          'SILINMETARIH' => now(),
        ]
      );

      return response()->json(['success' => true]);
    } catch (\Exception $e) {
      return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
  }

  public function kayitSil(Request $request, $id)
  {
    $kayitid = (int)$id;
    $userID = $request->header('userID');

    $hrkt = StokHrkt::select('ISEMRIID', 'MIKTAR', 'STOKID')->where('ID', $kayitid)->first();
    try {

      $emir = Emir::find($hrkt->ISEMRIID);
      $emir->URETIMMIKTAR -= (int)$hrkt->MIKTAR;
      $emir->save();

      $mml = Mamul::find($hrkt->STOKID);
      $mml->MEVCUT -= (int)$hrkt->MIKTAR;
      $mml->GIREN -= (int)$hrkt->MIKTAR;
      $mml->save();

      $hrkte = StokHrkt::updateOrCreate(
        ['ID' => $kayitid],
        [
          'SILINDI' => 1,
          'SILENID' => $userID,
          'SILINMETARIH' => now(),
        ]
      );

      return response()->json(['success' => true]);
    } catch (\Exception $e) {
      return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
  }

  public function uretimKaydet(Request $request)
  {

    $miktarTemp = $request->miktarTemp;
    $isEmriID = $request->ieID;
    $kayitid = (int)$request->kayitID;
    $operatorId = (int)$request->userID;
    $miktar = (int)$request->miktar - (int)$miktarTemp;

    // Log::info($miktarTemp);
    // Log::info($isEmriID);
    // Log::info($operatorId);
    // Log::info($kayitid);
    // Log::info($miktar);

    $hrkt = StokHrkt::select('ISEMRIID', 'MIKTAR', 'STOKID')->where('ID', $kayitid)->first();

    $emir = Emir::find($isEmriID);
    try {

      $emir->URETIMMIKTAR += (int)$miktar;
      $emir->save();

      $mml = Mamul::find($hrkt->STOKID);
      $mml->MEVCUT += (int)$miktar;
      $mml->GIREN += (int)$miktar;
      $mml->save();

      $hrkt = StokHrkt::find($kayitid);
      $hrkt->MIKTAR = (int)$request->MIKTAR;
      $hrkt->DUZENLEYENID = $operatorId;
      $hrkt->DUZENTARIH = now();
      $hrkt->save();

      return response()->json(['success' => true]);
    } catch (\Exception $e) {
      return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
  }

  // public function uretimKaydet(Request $request)
  // {
  //   $kayitid = (int)$request->kayitID;
  //   $operatorId = (int)$request->userID;
  //   $miktar = (int)$request->miktar - (int)$request->miktarTemp;
  //   Log::info($kayitid);


  //   $hrkt = StokHrkt::select('ISEMRIID', 'MIKTAR', 'STOKID')->where('ID', $kayitid)->first();

  //   $emir = Emir::find($request->ieID);
  //   try {

  //     $emir->URETIMMIKTAR += (int)$request->miktar - (int)$request->miktarTemp;
  //     $emir->save();

  //     $mml = Mamul::find($hrkt->STOKID);
  //     $mml->MEVCUT += (int)$request->miktar - (int)$request->miktarTemp;
  //     $mml->GIREN += (int)$request->miktar - (int)$request->miktarTemp;
  //     $mml->save();

  //     $hrkt = StokHrkt::find($kayitid);
  //     $hrkt->MIKTAR = (int)$request->miktar;
  //     $hrkt->DUZENLEYENID = $operatorId;
  //     $hrkt->DUZENTARIH = now();
  //     $hrkt->save();

  //     return response()->json(['success' => true]);
  //   } catch (\Exception $e) {
  //     return response()->json(['success' => false, 'message' => $e->getMessage()]);
  //   }
  // }


  public function exportExcel(Request $request)
  {
    $istasyon = $request->input('grupSecimi');
    $search = $request->input('search');

    if (empty($search)) {
      $emirVeriler = DB::table('OFTV_01_STOKHRKT')->get();
    } else {

      $emirVeriler = DB::table('OFTV_01_STOKHRKT')
        ->where(function ($query) use ($istasyon) {
          $query->where('ISTKOD',  'LIKE', "%{$istasyon}%"); // ISTKOD alanı için mutlak eşleşme
        })
        ->where(function ($query) use ($search) {
          $query->where('KOD', 'LIKE', "%{$search}%")
            ->orWhere('TANIM', 'LIKE', "%{$search}%")
            ->orWhere('MMLGRPKOD', 'LIKE', "%{$search}%")
            ->orWhere('ID', 'LIKE', "%{$search}%")
            ->orWhere('NOTLAR', 'LIKE', "%{$search}%")
            ->orWhere('URETIMTARIH', 'LIKE', "%{$search}%");
        })
        ->orderBy('ID', 'desc')
        ->get();
    }

    return response()->json($emirVeriler);
  }
}
