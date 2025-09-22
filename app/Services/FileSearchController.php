<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class FileSearchController
{
    public function findPdf(Request $request)
    {
        // Kullanıcıdan gelen dosya adı
        $fileName = '002003M00.PDF'; //$request->input('fileName');

        // Ağdaki paylaşılan ana klasör (.env: FILE_SEARCH_BASE_DIR)
        // Örn Windows: \\\\192.6.2.110\\oft\\TeknikResimler
        // Örn Linux/macOS: /mnt/oft/TeknikResimler
    $basePath = rtrim(config('app.file_search_base_dir', '\\192.6.2.110\oft\TeknikResimler'), '\\/');

        // Dosyayı arama işlemi
        $filePath = $this->searchFile($basePath, $fileName);

        // Log::info('Dosya arama işlemi: ' . $fileName . ' - ' . $filePath);
        if ($filePath) {
            return response()->json(['filePath' => $filePath]);
        } else {
            return response()->json(['error' => 'Dosya bulunamadı.'], 404);
        }
    }

    private function searchFile($directory, $fileName)
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        
        foreach ($iterator as $file) {
            if ($file->isFile() && strtolower($file->getFilename()) === strtolower($fileName)) {
                return $file->getRealPath();
            }
        }
        return null;
    }

}
