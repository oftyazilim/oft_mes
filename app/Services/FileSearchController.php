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

        // Ağdaki paylaşılan ana klasör (UNC yolu veya lokal test için bir dizin)
         $basePath = '\\\\192.6.2.110\\oft\\TeknikResimler'; // Ağdaki klasör
        //$basePath = '\\\\192.6.2.4\\canovate_elektronik\\12_DOKUMANTASYON\\YAYINLI RESİMLER\\ÜRETİM\\06\\06 6000'; // Ağdaki klasör
        // $basePath = 'C:\\Users\\Public\\Documents'; // Lokal test için

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
