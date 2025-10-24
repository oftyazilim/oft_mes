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
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

class PhotoController extends Controller
{
    // Varsayılan UNC kökleri (Windows). Linux/macOS'ta .env ile override edilebilir.
    private const BASE_NETWORK_DIR = "\\\\192.6.2.4\\canovate_elektronik\\01_GENEL\\15_OFT\\fotolar\\kk-fotolari\\"; // backslash ile biter
    private const BASE_SK_NETWORK_DIR = "\\\\192.6.2.4\\canovate_elektronik\\01_GENEL\\15_OFT\\fotolar\\sk-fotolari\\"; // backslash ile biter
    // private const DEFAULT_PUBLIC_PHOTO_BASE = 'http://192.6.2.110:8080/photos/';

    // private function fotoBaseUrl(): string
    // {
    //     // .env'de PHOTO_BASE_URL varsa onu kullan
    //     return rtrim(config('app.photo_base_url', env('PHOTO_BASE_URL', self::DEFAULT_PUBLIC_PHOTO_BASE)), '/') . '/';
    // }

    public function show(Request $request, $name)
    {
        // Yetki kontrolü (örnek: auth middleware üstte var)
        // Ek kontroller gerekiyorsa burada yap:
        // if (! $request->user()->can('view-photo')) abort(403);

        // Güvenlik: sadece dosya adı al, path traversal önle
        $safeName = basename($name);

        // Eğer Storage disk tanımlıysa kullan:
        // $disk = Storage::disk('fotolar');
        // $path = $disk->path($safeName);

        // Veya sabit mount yolu:
        $base = Storage::disk('fotolar_disk')->path('fotolar/sk-fotolari');
        $path = $base . DIRECTORY_SEPARATOR . $safeName;

        if (! file_exists($path) || ! is_file($path)) {
            abort(404);
        }

        // MIME tipini otomatik al
        $mime = mime_content_type($path) ?: 'application/octet-stream';

        // Inline göster (tarayıcı görüntü için)
        return response()->file($path, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.basename($path).'"'
        ]);
    }

    /**
     * Tanılama: PHP-FPM kullanıcı bağlamında bir foto dosyasını gerçekten okuyabiliyor muyuz?
     * Sadece debug modda kullanılabilir.
     */
    public function diagPhotoRead(Request $request)
    {
        if (!config('app.debug')) {
            return response()->json(['error' => 'disabled'], 403);
        }

        $isemri = $request->query('isemri');
        $name = $request->query('name');
        if (!$isemri || !$name) {
            return response()->json(['error' => 'isemri and name required'], 400);
        }

        $dir = rtrim(config('app.photo_kk_dir', self::BASE_NETWORK_DIR), '\/');
        $path = $dir . DIRECTORY_SEPARATOR . trim($isemri, '\/') . DIRECTORY_SEPARATOR . basename($name);

        $info = [
            'path' => $path,
            'exists' => file_exists($path),
            'is_file' => is_file($path),
            'readable' => is_readable($path),
            'owner' => null,
            'group' => null,
            'perms_octal' => null,
            'php_euid' => getmyuid(),
            'php_egid' => getmygid(),
            'php_user' => function_exists('posix_geteuid') ? posix_getpwuid(posix_geteuid())['name'] ?? null : null,
            'php_group' => function_exists('posix_getegid') ? posix_getgrgid(posix_getegid())['name'] ?? null : null,
        ];

        if ($info['exists']) {
            clearstatcache(false, $path);
            $stat = @stat($path);
            if ($stat) {
                $info['perms_octal'] = substr(sprintf('%o', $stat['mode']), -4);
                if (function_exists('posix_getpwuid')) {
                    $pw = @posix_getpwuid($stat['uid']);
                    $info['owner'] = $pw['name'] ?? $stat['uid'];
                }
                if (function_exists('posix_getgrgid')) {
                    $gr = @posix_getgrgid($stat['gid']);
                    $info['group'] = $gr['name'] ?? $stat['gid'];
                }
            }

            // Dosyayı açıp ilk birkaç baytı okumayı deneyelim
            try {
                $fh = @fopen($path, 'rb');
                if ($fh === false) {
                    $info['open'] = false;
                    $info['open_error'] = error_get_last()['message'] ?? 'unknown';
                } else {
                    $info['open'] = true;
                    $bytes = @fread($fh, 16);
                    $info['read_first_bytes_hex'] = bin2hex($bytes ?: '');
                    @fclose($fh);
                }
            } catch (\Throwable $e) {
                $info['open'] = false;
                $info['open_exception'] = $e->getMessage();
            }
        }

        // namei -l benzeri traverse testi: her klasörde x izni gerekiyor; sadece debug amaçlı ipucu
        $parts = explode(DIRECTORY_SEPARATOR, rtrim($path, DIRECTORY_SEPARATOR));
        $walk = [];
        $cur = DIRECTORY_SEPARATOR;
        foreach ($parts as $p) {
            if ($p === '') continue;
            $cur = rtrim($cur, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $p;
            $ok = is_dir($cur) ? is_readable($cur) : is_readable($cur);
            $walk[] = ['path' => $cur, 'exists' => file_exists($cur), 'readable' => $ok, 'is_dir' => is_dir($cur)];
        }
        $info['walk'] = $walk;

        return response()->json($info);
    }
    
    private function buildDir(string $isEmriNo): string
    {
        // Base'i config(app.photo_kk_dir) ile al, ayırıcıları normalize et
        $base = rtrim(config('app.photo_kk_dir', self::BASE_NETWORK_DIR), '\\/');
        // Olası baş/son boşlukları ve ayırıcıları temizle
        $cleanIsEmri = trim($isEmriNo);
        $cleanIsEmri = trim($cleanIsEmri, '\\/');
        return rtrim($base, '\\/') . DIRECTORY_SEPARATOR . $cleanIsEmri . DIRECTORY_SEPARATOR;
    }

    private function buildFilePath(string $isEmriNo, string $filename): string
    {
        return $this->buildDir($isEmriNo) . $filename;
    }

    // ---- STOK/DEPO FOTO HELPERLARI (sk-fotolari) ----
    private function cleanCode(string $code): string
    {
        $c = strtoupper($code);
        // Sadece A-Z 0-9 _ - kalsın
        $c = preg_replace('/[^A-Z0-9_-]+/', '-', $c);
        return trim($c, '-_');
    }

    private function stockDirFor(string $code): string
    {
        $base = rtrim(config('app.photo_sk_dir', self::BASE_SK_NETWORK_DIR), '\\/');
        return $base . DIRECTORY_SEPARATOR . $this->cleanCode($code) . DIRECTORY_SEPARATOR;
    }

    private function nextSequentialNameStock(string $code, string $ext): string
    {
        $dir = $this->stockDirFor($code);
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0775, true, true);
        }
        $clean = $this->cleanCode($code);
        // mevcut dosyaları tarayıp en büyük XXX'ı bul (CLEAN-XXX.ext)
        $pattern = '/^' . preg_quote($clean, '/') . '-(\d{3})\.(?:jpe?g|png|webp)$/i';
        $max = 0;
        foreach (File::files($dir) as $f) {
            if (preg_match($pattern, $f->getFilename(), $m)) {
                $num = (int) $m[1];
                if ($num > $max) $max = $num;
            }
        }
        $next = $max + 1;
        $base = $clean . '-' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
        $candidate = $base . '.' . $ext;
        while (File::exists($dir . $candidate)) {
            $next++;
            $base = $clean . '-' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
            $candidate = $base . '.' . $ext;
        }
        return $candidate;
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
        // Depo yüklemelerini sk-fotolari UNC paylaşımına yaz
        // Büyük cihaz fotoğrafları için limiti artır (25MB)
        $validated = $request->validate([
            'photo' => 'required|image|max:51200', // 50MB
            'itemID' => 'required|integer',
        ]);

        $itemId = (string) ((int) $validated['itemID']);
        $file = $request->file('photo');
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $ext = 'jpg';
        }

        $dir = $this->stockDirFor($itemId);
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0775, true, true);
        }

        $targetName = $this->nextSequentialNameStock($itemId, $ext);
        $targetPath = $dir . $targetName;

        try {
            // Intervention Image ile yeniden boyutlandır/kalite düşürerek kaydet
            $manager = new ImageManager(new GdDriver());
            $image = $manager->read($file->getRealPath());
            $maxDim = 1600;
            $width = $image->width();
            $height = $image->height();
            if ($width > $maxDim || $height > $maxDim) {
                $ratio = min($maxDim / $width, $maxDim / $height);
                $newW = (int) round($width * $ratio);
                $newH = (int) round($height * $ratio);
                $image->resize($newW, $newH);
            }

            // PNG'yi JPG'e çevirerek boyut kazan
            if ($ext === 'png') {
                $ext = 'jpg';
                $targetName = pathinfo($targetName, PATHINFO_FILENAME) . '.jpg';
                $targetPath = $dir . $targetName;
            }

            if ($ext === 'webp') {
                $encoded = $image->toWebp(70);
            } else {
                $encoded = $image->toJpeg(70);
            }
            $encoded->save($targetPath);
        } catch (\Throwable $e) {
            Log::error('Depo foto kaydetme hatası: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Kaydedilemedi'], 500);
        }

        $url = url('/api/stok-resimler/' . $this->cleanCode($itemId) . '/' . $targetName);

        $photo = Photo::create([
            'file_path' => $targetPath, // tam UNC yolu sakla
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
        // Önce doğrudan dosya sisteminden silmeyi dene (UNC)
        try {
            if ($photo->file_path && File::exists($photo->file_path)) {
                File::delete($photo->file_path);
            } else {
                // Eski kayıtlar Storage üzerinde olabilir
                Storage::delete($photo->file_path);
            }
        } catch (\Throwable $e) {
            Log::warning('Foto silerken hata, Storage fallback denenecek: ' . $e->getMessage());
            try {
                Storage::delete($photo->file_path);
            } catch (\Throwable $e2) {
            }
        }
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
            ->map(function ($dosya) use ($isemriNo) {
                // APP_URL bağımlılığını kaldır: relative URL dön.
                // Özel karakter/boşluk içeren dosya adları için güvenli encode uygula
                $encodedName = rawurlencode($dosya->getFilename());
                $encodedIsemri = rawurlencode($isemriNo);
                return [
                    'dosya_adi' => $dosya->getFilename(),
                'url' => "/api/oft-resimler/{$encodedIsemri}/{$encodedName}",
            ];
        })
            ->values();

        return response()->json($fotolar);
    }

    /**
     * Fotoğraf serve (public route). Basit güvenlik doğrulamaları içerir.
     */
    public function serveImage(string $isemri_no, string $filename)
    {
        // URL-encoded parametreleri gerçek değerlere çevir
        $isemri_no = rawurldecode($isemri_no);
        $filename = rawurldecode($filename);

        // Güvenlik: path traversal engelle
        if (Str::contains($filename, ['..', '/', '\\'])) {
            return response()->json(['message' => 'Geçersiz dosya adı'], 400);
        }

        $dosyaYolu = $this->buildFilePath($isemri_no, $filename);

        if (!File::exists($dosyaYolu)) {
            // 404 durumunda resolved path’i loglayalım (izin/yol hatası teşhisi için)
            Log::warning('oft-resimler 404: file not found', [
                'resolved_path' => $dosyaYolu,
                'isemri_no' => $isemri_no,
                'filename' => $filename,
                'photo_kk_dir' => config('app.photo_kk_dir')
            ]);
            return response()->json(['message' => 'Dosya bulunamadı'], 404);
        }
        try {
            $content = File::get($dosyaYolu);
            $mime = File::mimeType($dosyaYolu) ?: 'application/octet-stream';
            return Response::make($content, 200, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="' . $filename . '"'
            ]);
        } catch (\Throwable $e) {
            Log::error('oft-resimler read error', [
                'path' => $dosyaYolu,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Dosya okunamadı'], 500);
        }
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
