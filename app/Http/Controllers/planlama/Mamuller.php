<?php

namespace App\Http\Controllers\planlama;

use App\Http\Controllers\Controller;
use App\Models\Mamul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Mamuller extends Controller
{
  public function getData(Request $request)
  {
    $filterValue = $request->filterValue;

    $conn = $request->fabrika == "a" ? DB::connection('sqlsrva') : DB::connection('sqlsrvs');

    $mamuller = $conn
      ->table('mamuller')
      ->select('mamul')
      ->where('tip', 'Boru')
      ->orWhere('tip', 'Profil')
      ->distinct()
      ->get();

    $neviler = $conn
      ->table('mamuller')
      ->select('nevi')
      ->where('tip', 'Boru')
      ->orWhere('tip', 'Profil')
      ->distinct()
      ->get();

    $hatlar = $conn
      ->table('caldurum')
      ->select('hat')
      ->distinct()
      ->get();

    return response()->json([
      'mamuller' => $mamuller,
      'neviler' => $neviler,
      'hatlar' => $hatlar,
    ], 200);
  }

  public function MamulNevi(Request $request)
  {
    $conn = $request->fabrika == "a" ? DB::connection('sqlsrva') : DB::connection('sqlsrvs');
    $mamuller = $conn
    ->table('oftv_mmlstokgrp')
    ->where('nevi', $request->nevi)
    ->distinct()
    ->get();

    return response()->json([
      'mamuller' => $mamuller,
    ], 200);
  }

  public function getVeri()
  {
    $istasyon = DB::table('OFTT_01_ISTASYONLAR')->select('ID', 'TANIM')->distinct()->get();
    $mamulOz1 = DB::table('OFTT_01_TANIMLAR')->select('DEGER')->where('TANIM', 'ÖZELLİK 1')->orderBy('DEGER')->get();
    $mamulOz2 = DB::table('OFTT_01_TANIMLAR')->select('DEGER')->where('TANIM', 'ÖZELLİK 2')->orderBy('DEGER')->get();
    $mamulOz3 = DB::table('OFTT_01_TANIMLAR')->select('DEGER')->where('TANIM', 'ÖZELLİK 3')->orderBy('DEGER')->get();
    $mamulGrup = DB::table('OFTT_01_TANIMLAR')->select('DEGER')->where('TANIM', 'GRUP KODU')->orderBy('DEGER')->get();
    $mamulSinif = DB::table('OFTT_01_TANIMLAR')->select('DEGER')->where('TANIM', 'SINIF')->orderBy('DEGER')->get();

    return response()->json([
      'istasyon' => $istasyon,
      'mamulGrup' => $mamulGrup,
      'mamulSinif' => $mamulSinif,
      'mamulOz1' => $mamulOz1,
      'mamulOz2' => $mamulOz2,
      'mamulOz3' => $mamulOz3,
      'message' => 'Veriler başarıyla alındı',
      'success' => true,
    ]);
  }

  public function store(Request $request)
  {
    // Log::info($request);
    $kayitid = (int)$request->ID;
    $isAktif = $request->AKTIF;
    $operatorID = $request->header('userID');
    $istasyonID = DB::table('OFTT_01_ISTASYONLAR')->where('TANIM', $request->STGRPKOD)->select('ID')->first();
    $mamul = Mamul::updateOrCreate(
      ['ID' => $kayitid], // Güncelleme için eşleştirilecek koşul
      [
        'KOD' => $request->KOD,
        'TANIM' => $request->TANIM,
        'STGRPKOD' => $request->STGRPKOD,
        'MMLGRPKOD' => $request->MMLGRPKOD,
        'SINIF' => $request->SINIF,
        'OZELLIKKOD1' => $request->OZELLIKKOD1,
        'OZELLIKKOD2' => $request->OZELLIKKOD2,
        'OZELLIKKOD3' => $request->OZELLIKKOD3,
        'ISTASYONID' => $istasyonID->ID,
        'AKTIF' => $isAktif,
        'DUZENLEYENID' => $operatorID, // Örnek olarak operatör ID burada sabit
        'SONDRMTARIH' => now()
      ]
    );

    return response()->json($mamul->wasRecentlyCreated ? 'Created' : 'Updated');
  }

  public function edit(string $id)
  {
    $yMamuller = Mamul::distinct()->where('ID', $id)->get();
    return response()->json($yMamuller);
  }

  public function kayitSil(Request $request, string $id)
  {
    $kayitid = (int)$id;
    $operatorID = $request->header('userID');
    $mamul = Mamul::updateOrCreate(
      ['ID' => $kayitid],
      [
        'SILINDI' => 1,
        'SILENID' => $operatorID,
        'SILINMETARIH' => now(),
      ]
    );

    return response()->json($kayitid);
  }

  public function exportExcel(Request $request)
  {
    $search = $request->input('search');
    if (empty($search)) {
      $mamulVeriler = Mamul::where('SILINDI', false)->get();
    } else {
      $mamulVeriler = Mamul::where('SILINDI', false)
        ->where('KOD', 'LIKE', "%{$search}%")
        ->orWhere('TANIM', 'LIKE', "%{$search}%")
        ->orWhere('STGRPKOD', 'LIKE', "%{$search}%")
        ->orWhere('MMLGRPKOD', 'LIKE', "%{$search}%")
        ->orWhere('SINIF', 'LIKE', "%{$search}%")
        ->get();
    }

    // JSON formatında döndürün
    return response()->json($mamulVeriler);
  }
}
