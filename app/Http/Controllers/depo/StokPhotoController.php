<?php

namespace App\Http\Controllers\depo;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

class StokPhotoController extends Controller
{
    // Varsayılan (Windows UNC) kök. Linux/macOS'ta .env'den PHOTO_SK_DIR ile override edilmelidir (örn. /mnt/oft/sk-fotolari)
    private const BASE_NETWORK_DIR = "\\\\192.6.2.4\\canovate_elektronik\\01_GENEL\\15_OFT\\fotolar\\sk-fotolari\\"; // backslash ile biter
    private const BASE_PUBLIC_URL = '/api/stok-resimler';

    private function baseDir(): string
    {
        // .env PHOTO_SK_DIR varsa onu kullan, değilse Windows UNC default'u kullan
        // Sağ ve sol ayırıcıları temizleyip platforma uygun ayırıcı ile bağlayacağız
    return rtrim(config('app.photo_sk_dir', self::BASE_NETWORK_DIR), '\\/');
    }

    private function cleanItemCode(string $code): string
    {
        $c = strtoupper($code);
        $c = preg_replace('/[^A-Z0-9_-]+/', '-', $c);
        return trim($c, '-_');
    }

    private function dirFor(string $itemCode): string
    {
        $base = $this->baseDir();
        return rtrim($base, '\\/') . DIRECTORY_SEPARATOR . $this->cleanItemCode($itemCode) . DIRECTORY_SEPARATOR;
    }

    private function nextSequentialName(string $itemCode, string $ext): string
    {
        $dir = $this->dirFor($itemCode);
        if (!File::exists($dir)) {
            try {
                File::makeDirectory($dir, 0775, true, true);
            } catch (\Throwable $e) {
                Log::error('Stok foto klasörü oluşturulamadı: ' . $e->getMessage(), ['dir' => $dir]);
                throw $e;
            }
        }
        // Accept any allowed extension when scanning so numbering is continuous
        $pattern = '/^' . preg_quote($this->cleanItemCode($itemCode), '/') . '-(\d{3})\.(?:jpe?g|png|webp)$/i';
        $max = 0; // we will start sequence at 001
        foreach (File::files($dir) as $f) {
            if (preg_match($pattern, $f->getFilename(), $m)) {
                $num = (int)$m[1];
                if ($num > $max) {
                    $max = $num;
                }
            }
        }
        $next = $max + 1; // if none found => 1
        $base = $this->cleanItemCode($itemCode) . '-' . str_pad((string)$next, 3, '0', STR_PAD_LEFT);
        $candidate = $base . '.' . $ext;
        // Simple collision avoidance (in case of concurrent writes)
        while (File::exists($dir . $candidate)) {
            $next++;
            $base = $this->cleanItemCode($itemCode) . '-' . str_pad((string)$next, 3, '0', STR_PAD_LEFT);
            $candidate = $base . '.' . $ext;
        }
        return $candidate;
    }

    public function list(Request $request): JsonResponse
    {
        $request->validate(['itemCode' => 'required|string|max:120']);
        $itemCode = $request->input('itemCode');
        $dir = $this->dirFor($itemCode);
        if (!File::exists($dir)) return response()->json([]);
        $clean = $this->cleanItemCode($itemCode);
        $files = collect(File::files($dir))
            ->filter(fn($f) => Str::startsWith(strtoupper($f->getFilename()), $clean . '-'))
            ->sortBy(fn($f) => $f->getFilename())
            ->values()
            ->map(fn($f, $idx) => [
                'name' => $f->getFilename(),
            // Relative URL + güvenli encode
            'url' => self::BASE_PUBLIC_URL . '/' . rawurlencode($clean) . '/' . rawurlencode($f->getFilename()),
                'size' => $f->getSize(),
                'index' => $idx,
            ]);
        return response()->json($files);
    }

    public function upload(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'photo' => 'required|image|max:51200',
            'itemCode' => 'required|string|max:120',
        ]);
        $itemCode = $validated['itemCode'];
        $dir = $this->dirFor($itemCode);
        if (!File::exists($dir)) File::makeDirectory($dir, 0775, true, true);

        $file = $request->file('photo');
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) $ext = 'jpg';

        $targetName = $this->nextSequentialName($itemCode, $ext);
        $targetPath = $dir . $targetName;

        try {
            // Intervention Image v3 usage
            $manager = new ImageManager(new GdDriver());
            $image = $manager->read($file->getRealPath());

            // Scale down keeping aspect ratio (there is no upsize by default, so only shrink)
            // We manually calculate dimensions
            $maxDim = 1600;
            $width = $image->width();
            $height = $image->height();
            if ($width > $maxDim || $height > $maxDim) {
                $ratio = min($maxDim / $width, $maxDim / $height);
                $newW = (int)round($width * $ratio);
                $newH = (int)round($height * $ratio);
                $image->resize($newW, $newH);
            }

            if ($ext === 'png') { // normalize to jpg for size reduction
                $ext = 'jpg';
                $targetName = pathinfo($targetName, PATHINFO_FILENAME) . '.jpg';
                $targetPath = $dir . $targetName;
            }

            // Encode & compress (quality ~70). Library provides toJpg/toWebp helpers.
            if ($ext === 'webp') {
                $encoded = $image->toWebp(70);
            } else {
                $encoded = $image->toJpeg(70); // use toJpeg (main method) instead of alias toJpg
            }
            $encoded->save($targetPath);
        } catch (\Throwable $e) {
            Log::error('Stok foto kaydetme hata: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Kaydedilemedi'], 500);
        }

        return response()->json([
            'status' => 'ok',
            'name' => $targetName,
            // Relative URL + güvenli encode
            'url' => self::BASE_PUBLIC_URL . '/' . rawurlencode($this->cleanItemCode($itemCode)) . '/' . rawurlencode($targetName),
        ]);
    }

    public function delete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'itemCode' => 'required|string|max:120',
            'name' => 'required|string',
        ]);
        $itemCode = $validated['itemCode'];
        $name = basename($validated['name']);
        if (Str::contains($name, ['..', '/', '\\'])) return response()->json(['status' => 'error', 'message' => 'Geçersiz dosya'], 400);
        $path = $this->dirFor($itemCode) . $name;
        if (File::exists($path)) {
            File::delete($path);
        }
        return response()->json(['status' => 'ok']);
    }

    public function serve(string $itemCode, string $filename)
    {
        if (Str::contains($filename, ['..', '/', '\\'])) return response()->json(['message' => 'Geçersiz'], 400);
        $path = $this->dirFor($itemCode) . $filename;
        if (!File::exists($path)) return response()->json(['message' => 'Yok'], 404);
        return Response::make(File::get($path), 200, [
            'Content-Type' => File::mimeType($path),
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
    }
}
