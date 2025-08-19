<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Photo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class PhotoController extends Controller
{
    public function index(Request $request)
    {
        if ($request->filled('itemID')) {
            return Photo::where('item_id', $request->input('itemID'))->get();
        }

        return Photo::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:5120', // 5MB
            'itemID' => 'required|integer',
        ]);

        $itemID = $request->input('itemID');

        // $path = $request->file('photo')->store('', 'local_photos');
        $path = $request->file('photo')->store('', 'network_photos');
        $filename = basename($path);
        // $url = 'http://10.211.55.4/fotolar/' . $filename;
        $url = 'http://192.6.2.110:8080/photos/' . $filename;

        $photo = Photo::create([
            'file_path' => $path,
            'url' => $url,
            'item_id' => $itemID
        ]);

        // Log::info($photo);

        return response()->json(['success' => true, 'photo' => $photo]);
    }

    public function destroy($id)
    {
        $photo = Photo::findOrFail($id);
        Storage::delete($photo->file_path);
        $photo->delete();
        return response()->json(['success' => true]);
    }

    public function generatePdf()
    {
        $photos = Photo::all();

        $html = '<h1>Fotoğraflar</h1>';
        foreach ($photos as $photo) {
            $imagePath = public_path('storage/' . str_replace('public/', '', $photo->file_path));
            $html .= '<div><img src="' . $imagePath . '" width="300"></div>';
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        return $pdf->download('photos.pdf');
    }

    public function getFotolar(Request $request)
    {
        $serino = $request->serino;
        $isemri_no = $request->isEmriNo;

        $klasorYolu = "\\\\192.6.2.4\\canovate_elektronik\\01_GENEL\\15_OFT\\fotolar\\kk-fotolari\\$isemri_no";

        // Laravel'in Storage yerine native File facade kullanmak gerekebilir
        if (!File::exists($klasorYolu)) {
            Log::info("Klasör bulunamadı: $klasorYolu");
            return response()->json([]);
        }

        $dosyalar = File::files($klasorYolu);


        // $fotolar = collect($dosyalar)->map(function ($dosya) use ($isemri_no) {
        //     $dosyaAdi = $dosya->getFilename();
        //     return [
        //         'dosya_adi' => $dosyaAdi,
        //         'url' => "/api/oft-resimler/$isemri_no/$dosyaAdi",
        //     ];
        // });

        // Sadece serino'nun ilk 13 karakteriyle başlayan dosyaları filtrele
        $fotolar = collect($dosyalar)->filter(function ($dosya) use ($serino) {
            return str_starts_with($dosya->getFilename(), substr($serino, 0, 18));
        })->map(function ($dosya) use ($isemri_no) {
            return [
                'dosya_adi' => $dosya->getFilename(),
                'url' => url("/api/oft-resimler/$isemri_no/" . $dosya->getFilename())
            ];
        })->values();

        Log::info("Bulunan dosyalar: " . json_encode($fotolar));

        return response()->json($fotolar);
    }

    public function serveImage($isemri_no, $filename)
    {
        $dosyaYolu = "\\\\192.6.2.4\\canovate_elektronik\\01_GENEL\\15_OFT\\fotolar\\kk-fotolari\\$isemri_no\\$filename";

        // Log::info("Dosya yolu: $dosyaYolu");
        if (!File::exists($dosyaYolu)) {
            return response()->json(['message' => 'Dosya bulunamadı'], 404);
        }

        return Response::make(File::get($dosyaYolu), 200, [
            'Content-Type' => File::mimeType($dosyaYolu),
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
    }

    public function deleteFoto(Request $request)
    {
        $url = $request->url;
        $path = parse_url($url, PHP_URL_PATH);
        $dosyaAdi = basename($path);
        $isemri_no = $request->isEmriNo; // İEN-25021477 gibi

        $klasor = "\\\\192.6.2.4\\canovate_elektronik\\01_GENEL\\15_OFT\\fotolar\\kk-fotolari\\$isemri_no";
        $tamYol = $klasor . "\\" . $dosyaAdi;

        if (!File::exists($tamYol)) {
            return response()->json(['status' => 'not_found'], 404);
        }

        try {
            File::delete($tamYol);

            // Dosya adından serino'yu çıkar (format: SERINO-XX.jpg)
            $nameOnly = pathinfo($dosyaAdi, PATHINFO_FILENAME); // SERINO-01
            $seriNo = $nameOnly;
            if (preg_match('/^(.*)-(\d{2})$/', $nameOnly, $m)) {
                $seriNo = $m[1];
            }

            // Aynı serino'ya bağlı başka foto kaldı mı?
            $kalan = collect(File::files($klasor))
                ->filter(function ($f) use ($seriNo) {
                    return str_starts_with($f->getFilename(), $seriNo . '-');
                })
                ->count();

            if ($kalan === 0) {
                // Tablo: oftt_urun_kontrol_d, kolon: seri_no & is_photo
                DB::connection('pgsql_oft')
                    ->table('oftt_urun_kontrol_d')
                    ->where('seri_no', $seriNo)
                    ->update(['is_photo' => 0]);
            }

            return response()->json(['status' => 'ok', 'seri_no' => $seriNo, 'remaining' => $kalan]);
        } catch (\Throwable $e) {
            Log::error('Foto silme hatası: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Silinemedi'], 500);
        }
    }
}
