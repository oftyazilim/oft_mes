<?php

namespace App\Http\Controllers\diger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\LogService;

class DigerController extends Controller
{
    public function uploadYemekListesi(Request $request)
    {
        $request->validate([
            'pdf' => 'required|mimes:pdf|max:2048',
        ]);
    
        $userID = $request->input('user_id'); // Buradan alınır
    
        $path = $request->file('pdf')->storeAs('public/yemeklistesi', 'yemeklistesi.pdf');

        return response()->json(['message' => 'Yemek listesi yüklendi.', 'path' => $path]);
    }



}
