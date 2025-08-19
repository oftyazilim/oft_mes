<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PhotoController extends Controller
{
    // UNC ana dizin (sonunda tek ters slash olacak şekilde)
    private const BASE_NETWORK_DIR = "\\\\192.6.2.4\\canovate_elektronik\\01_GENEL\\15_OFT\\fotolar\\kk-fotolari\\"; // ends with backslash
    private const DEFAULT_PUBLIC_PHOTO_BASE = 'http://192.6.2.110:8080/photos/';

    private function fotoBaseUrl(): string
    {
        // .env'de PHOTO_BASE_URL varsa onu kullan
        return rtrim(config('app.photo_base_url', self::DEFAULT_PUBLIC_PHOTO_BASE), '/') . '/';
    }

    private function buildDir(string $isEmriNo): string
    {
        // Base'i sağdan temizle, iş emri no baş/son backslash temizle ve standardize ederek sonuna bir ayırıcı ekle
        $base = rtrim(self::BASE_NETWORK_DIR, '\\');
        $cleanIsEmri = trim($isEmriNo, '\\');
        return $base . '\\' . $cleanIsEmri . '\\';
    }

    private function buildFilePath(string $isEmriNo, string $filename): string
    {
        return $this->buildDir($isEmriNo) . $filename;
    }
    /**
     * Listeleme (opsiyonel itemID filtresi).
     */
    public function index(Request $request): JsonResponse
    {
        $data = $request->filled('itemID')
            ? Photo::where('item_id', (int) $request->input('itemID'))->get()
            : Photo::all();
        return response()->json($data);
    }

    /**
     * Fotoğraf yükle.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'photo' => 'required|image|max:5120', // 5MB
            'itemID' => 'required|integer',
        ]);

        $path = $request->file('photo')->store('', 'network_photos');
        $filename = basename($path);
        $url = $this->fotoBaseUrl() . $filename;

        $photo = Photo::create([
            'file_path' => $path,
            'url' => $url,
            'item_id' => (int) $validated['itemID'],
        ]);

        return response()->json(['success' => true, 'photo' => $photo]);
    }

    /**
     * Foto sil.
     */
    public function destroy(int $id): JsonResponse
    {
        $photo = Photo::findOrFail($id);
        Storage::delete($photo->file_path);
        $photo->delete();
        return response()->json(['success' => true]);
    }

    /**
     * İş emri ve seri numarasına göre fotoğraf listesini getir.
     */
    public function getFotolar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'serino' => 'required|string|max:255',
            'isEmriNo' => 'required|string|max:50',
        ]);

        $serino = $validated['serino'];
        $isemriNo = $validated['isEmriNo'];

        $klasorYolu = $this->buildDir($isemriNo);

        Log::info('Fotoğraf klasör yolu', ['path' => $klasorYolu, 'serino' => $serino]);

        if (!File::exists($klasorYolu)) {
            // Klasör yoksa boş sonuç dön (404 yerine boş liste mantıksal olarak UI için daha yumuşak)
            return response()->json([]);
        }

        $prefix = Str::substr($serino, 0, 18); // İlk 18 karakter (uygulamadaki mevcut varsayım)
        $fotolar = collect(File::files($klasorYolu))
            ->filter(fn($dosya) => Str::startsWith($dosya->getFilename(), $prefix))
            ->map(fn($dosya) => [
                'dosya_adi' => $dosya->getFilename(),
                'url' => url("/api/oft-resimler/$isemriNo/" . $dosya->getFilename()),
            ])
            ->values();

        return response()->json($fotolar);
    }

    /**
     * Fotoğraf serve (public route). Basit güvenlik doğrulamaları içerir.
     */
    public function serveImage(string $isemri_no, string $filename)
    {
        // Güvenlik: path traversal engelle
        if (Str::contains($filename, ['..', '/', '\\'])) {
            return response()->json(['message' => 'Geçersiz dosya adı'], 400);
        }

        $dosyaYolu = $this->buildFilePath($isemri_no, $filename);

        if (!File::exists($dosyaYolu)) {
            return response()->json(['message' => 'Dosya bulunamadı'], 404);
        }

        return Response::make(File::get($dosyaYolu), 200, [
            'Content-Type' => File::mimeType($dosyaYolu),
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
    }

    /**
     * Tekil foto silme (dosya + veritabanı is_photo güncelleme).
     */
    public function deleteFoto(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => 'required|url',
            'isEmriNo' => 'required|string|max:50',
        ]);

        $url = $validated['url'];
        $isemriNo = $validated['isEmriNo'];

        $path = parse_url($url, PHP_URL_PATH);
        $dosyaAdi = basename($path ?? '');

        if ($dosyaAdi === '' || Str::contains($dosyaAdi, ['..', '/', '\\'])) {
            return response()->json(['status' => 'error', 'message' => 'Geçersiz dosya adı'], 400);
        }

        $klasor = $this->buildDir($isemriNo);
        $tamYol = $this->buildFilePath($isemriNo, $dosyaAdi);

        if (!File::exists($tamYol)) {
            return response()->json(['status' => 'not_found'], 404);
        }

        try {
            File::delete($tamYol);

            // Dosya adından SERİNO-XX.jpg formatında seri_no çıkar
            $nameOnly = pathinfo($dosyaAdi, PATHINFO_FILENAME);
            $seriNo = $nameOnly;
            if (preg_match('/^(.*)-(\d{2})$/', $nameOnly, $m)) {
                $seriNo = $m[1];
            }

            $kalan = collect(File::files($klasor))
                ->filter(fn($f) => Str::startsWith($f->getFilename(), $seriNo . '-'))
                ->count();

            if ($kalan === 0) {
                DB::connection('pgsql_oft')
                    ->table('oftt_urun_kontrol_d')
                    ->where('seri_no', $seriNo)
                    ->update(['is_photo' => 0]);
            }

            return response()->json([
                'status' => 'ok',
                'seri_no' => $seriNo,
                'remaining' => $kalan,
            ]);
        } catch (\Throwable $e) {
            Log::error('Foto silme hatası: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Silinemedi'], 500);
        }
    }
}
