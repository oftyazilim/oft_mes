<?php

namespace App\Http\Controllers\Uretim;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
// Symfony Finder büyük ağaçlarda yavaş kalabildiği için kullanmıyoruz
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TechnicalDrawingController extends BaseController
{
    // Teknik resim kök klasörü (.env: TECHNICAL_DRAWINGS_DIR ile override edilir)
    // Örn Windows: \\\\192.6.2.4\\canovate_elektronik\\12_DOKUMANTASYON\\YAYINLI RESİMLER\\ÜRETİM
    // Örn Linux/macOS: /mnt/oft/teknik-resimler/URETIM
    private string $baseDir;

    public function __construct()
    {
        $this->baseDir = rtrim(config('app.technical_drawings_dir', "\\\\192.6.2.4\\canovate_elektronik\\12_DOKUMANTASYON\\YAYINLI RESİMLER\\ÜRETİM"), '\\/\u{00A0}');
    }

    public function show(Request $request)
    {
        $code = (string) $request->query('code', '');
        $code = trim($code);
        if ($code === '') {
            return response()->json(['message' => 'Parametre eksik: code'], 422);
        }

        // Güvenlik: dosya adı olarak kullanılacak kodu sadeleştir (A-Z0-9, -, _, .)
        $safeCode = preg_replace('/[^A-Za-z0-9_\-.]/', '', $code) ?? '';
        // Ürün kodunu büyük harfe normalize et (dosya adlarıyla tutarlılık)
        $safeCode = strtoupper($safeCode);
        if ($safeCode === '') {
            return response()->json(['message' => 'Geçersiz ürün kodu'], 422);
        }

        $base = rtrim($this->baseDir, "\\/\u{00A0}");

        // 1) Heuristik: İlk iki karakter klasörü (sadece pad'li "XX" kullan)
        $p0 = substr($safeCode, 0, 1) ?: '';
        $p1 = substr($safeCode, 1, 1) ?: '';
        $p2 = substr($safeCode, 2, 1) ?: '';

        $two = strtoupper($p0 . $p1);
        if (ctype_digit($two)) {
            $level1Name = str_pad((string) intval($two), 2, '0', STR_PAD_LEFT); // '4' -> '04'
        } else {
            $level1Name = $two; // 'AB' vb.
        }
        $level1Path = $base . DIRECTORY_SEPARATOR . $level1Name;

        // 2) İç klasör deseni: Öncelik "XX 0000"; ikincil: "XX Z000"
        $level2Candidates = [];
        $level2Candidates[] = $level1Path . DIRECTORY_SEPARATOR . ($level1Name . ' ' . '0000'); // Örn: 04 0000
        if ($p2 !== '') {
            $level2Candidates[] = $level1Path . DIRECTORY_SEPARATOR . ($level1Name . ' ' . strtoupper($p2) . '000'); // Örn: 04 4000
        }

        $cacheKey = 'technical_drawing_path:' . strtolower($safeCode);
        if ($cached = Cache::get($cacheKey)) {
            if (is_readable($cached)) {
                return response()->file($cached, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . basename($cached) . '"',
                    'Cache-Control' => 'private, max-age=600',
                ]);
            }
            Cache::forget($cacheKey);
        }

        $candidates = [];
        $exts = ['PDF', 'pdf'];

        // Yalnızca doğrudan yol kontrolleri — "XX\\XX 0000\\KOD.pdf"
        foreach ($level2Candidates as $lvl2) {
            foreach ($exts as $ext) {
                $path = rtrim($lvl2, "\\/\u{00A0}") . DIRECTORY_SEPARATOR . $safeCode . '.' . $ext;
                $alt  = str_replace('\\', '/', $path); // forward slash varyantı
                Log::info('Checking file: ' . $path);
                $exists = @file_exists($path) || @file_exists($alt);
                $isFile = $exists && (@is_file($path) || @is_file($alt));
                $readOk = $isFile && (@is_readable($path) || @is_readable($alt));
                Log::info('Exists=' . ($exists ? '1' : '0') . ' IsFile=' . ($isFile ? '1' : '0') . ' Readable=' . ($readOk ? '1' : '0'));
                if ($readOk || $isFile) {
                    $candidates[] = $exists ? ($isFile ? ($readOk ? $path : $path) : $path) : $path;
                    break 2;
                }
            }
        }

        if (empty($candidates)) {
            // Tarama yok: UNC paylaşımlarda directory enumeration uzun sürebiliyor; direkt yol bulunamazsa 404 dön.
            return response()->json(['message' => 'Teknik resim bulunamadı (direct-check)'], 404);
        }

        // Bulunan dosyayı döndür
        $file = $candidates[0];
        Cache::put($cacheKey, $file, now()->addHours(12));
        // PDF inline görüntülensin
        return response()->file($file, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($file) . '"',
            'Cache-Control' => 'private, max-age=600',
        ]);
    }
}
