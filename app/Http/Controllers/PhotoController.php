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
    // Artık storage/app/public/kk_fotolar ve storage/app/public/sk_fotolar kullanılacak
    // Use the hyphenated folder names that exist under storage/app/public
    private const KK_DIR = 'kk-fotolari';
    private const SK_DIR = 'sk-fotolari';
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
        if (Str::contains($safeName, ['..', '/', '\\'])) {
            abort(400);
        }

        $disk = Storage::disk('public');
        $candidates = [
            self::KK_DIR . '/' . $safeName,
            self::SK_DIR . '/' . $safeName,
            $safeName,
        ];

        foreach ($candidates as $p) {
            if ($disk->exists($p)) {
                $content = $disk->get($p);
                try {
                    $fullPath = $disk->path($p);
                    $mime = File::mimeType($fullPath) ?: 'application/octet-stream';
                } catch (\Throwable $_) {
                    $mime = 'application/octet-stream';
                }
                return Response::make($content, 200, [
                    'Content-Type' => $mime,
                    'Content-Disposition' => 'inline; filename="' . basename($p) . '"'
                ]);
            }
        }

        abort(404);
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

        $subdir = trim($isemri, '\/');
        $filename = basename($name);
        $path = $subdir . '/' . $filename;

        $php_user = null;
        if (function_exists('posix_geteuid') && function_exists('posix_getpwuid')) {
            try {
                $euid = posix_geteuid();
                $pw = @posix_getpwuid($euid);
                $php_user = $pw['name'] ?? null;
            } catch (\Throwable $e) {
                $php_user = null;
            }
        }
        $php_group = null;
        if (function_exists('posix_getegid') && function_exists('posix_getgrgid')) {
            try {
                $egid = posix_getegid();
                $gr = @posix_getgrgid($egid);
                $php_group = $gr['name'] ?? null;
            } catch (\Throwable $e) {
                $php_group = null;
            }
        }
        $exists = Storage::disk('public')->exists($path);
        $info = [
            'path' => $path,
            'exists' => $exists,
            'is_file' => $exists,
            'readable' => $exists,
            'owner' => null,
            'group' => null,
            'perms_octal' => null,
            'php_euid' => getmyuid(),
            'php_egid' => getmygid(),
            'php_user' => $php_user,
            'php_group' => $php_group,
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
                $bytes = Storage::disk('public')->get($path);
                $info['open'] = true;
                $info['read_first_bytes_hex'] = bin2hex(substr($bytes, 0, 16));
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
        // storage/app/public/kk_fotolar/{isEmriNo}/ (relative to 'public' disk)
        $cleanIsEmri = trim($isEmriNo);
        $cleanIsEmri = trim($cleanIsEmri, '\/');
        return self::KK_DIR . '/' . $cleanIsEmri . '/';
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
        // storage/app/public/sk_fotolar/{code}/ (relative to 'public' disk)
        return self::SK_DIR . '/' . $this->cleanCode($code) . '/';
    }

    private function nextSequentialNameStock(string $code, string $ext): string
    {
        $dir = $this->stockDirFor($code);
        // gerçek filesystem path
        $fullDir = Storage::disk('public')->path($dir);
        if (!File::exists($fullDir)) {
            File::makeDirectory($fullDir, 0775, true, true);
        }
        $clean = $this->cleanCode($code);
        // mevcut dosyaları tarayıp en büyük XXX'ı bul (CLEAN-XXX.ext)
        $pattern = '/^' . preg_quote($clean, '/') . '-(\d{3})\.(?:jpe?g|png|webp)$/i';
        $max = 0;
        foreach (File::files($fullDir) as $f) {
            if (preg_match($pattern, $f->getFilename(), $m)) {
                $num = (int) $m[1];
                if ($num > $max) $max = $num;
            }
        }
        $next = $max + 1;
        $base = $clean . '-' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
        $candidate = $base . '.' . $ext;
        while (File::exists($fullDir . DIRECTORY_SEPARATOR . $candidate)) {
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
        $targetName = $this->nextSequentialNameStock($itemId, $ext);

        // Intervention Image ile kalite düşür, sonra feedback mantığıyla kaydet
        try {
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
            }

            if ($ext === 'webp') {
                $encoded = $image->toWebp(70);
            } else {
                $encoded = $image->toJpeg(70);
            }
            // Dosyayı 'public' diskine kaydet
            $storedPath = $dir . $targetName;
            Storage::disk('public')->put($storedPath, (string) $encoded);
            Log::info('Stok foto store', [
                'path' => $storedPath,
                'fullPath' => Storage::disk('public')->path($storedPath),
            ]);
        } catch (\Throwable $e) {
            Log::error('Depo foto kaydetme hatası: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Kaydedilemedi'], 500);
        }

        $url = asset('storage/' . $dir . $targetName);

        $photo = Photo::create([
            'file_path' => $dir . $targetName,
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
        try {
            Storage::disk('public')->delete($photo->file_path);
        } catch (\Throwable $e) {
            Log::warning('Foto silerken hata: ' . $e->getMessage());
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
        if (!Storage::disk('public')->exists($klasorYolu)) {
            return response()->json([]);
        }
        $prefix = Str::substr($serino, 0, 18);
        $fotolar = collect(Storage::disk('public')->files($klasorYolu))
            ->filter(fn($dosya) => Str::startsWith(basename($dosya), $prefix))
            ->map(function ($dosya) use ($isemriNo) {
                $encodedName = rawurlencode(basename($dosya));
                $encodedIsemri = rawurlencode($isemriNo);
                return [
                    'dosya_adi' => basename($dosya),
                    'url' => asset('storage/' . $dosya),
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
        if (!Storage::disk('public')->exists($dosyaYolu)) {
            Log::warning('oft-resimler 404: file not found', [
                'resolved_path' => $dosyaYolu,
                'isemri_no' => $isemri_no,
                'filename' => $filename,
            ]);
            return response()->json(['message' => 'Dosya bulunamadı'], 404);
        }
        try {
            $disk = Storage::disk('public');
            $content = $disk->get($dosyaYolu);
            try {
                $fullPath = $disk->path($dosyaYolu);
                $mime = File::mimeType($fullPath) ?: 'application/octet-stream';
            } catch (\Throwable $_) {
                $mime = 'application/octet-stream';
            }

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

        if (!Storage::disk('public')->exists($tamYol)) {
            return response()->json(['status' => 'not_found'], 404);
        }

        try {
            Storage::disk('public')->delete($tamYol);

            $nameOnly = pathinfo($dosyaAdi, PATHINFO_FILENAME);
            $seriNo = $nameOnly;
            if (preg_match('/^(.*)-(\d{2})$/', $nameOnly, $m)) {
                $seriNo = $m[1];
            }

            $kalan = collect(Storage::disk('public')->files($klasor))
                ->filter(fn($f) => Str::startsWith(basename($f), $seriNo . '-'))
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
