<?php

namespace App\Services;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FileServeController
{
    public function servePdf(Request $request)
    {
        $filePath = $request->query('filePath');

        if (!$filePath || !file_exists($filePath)) {
            return response()->json(['error' => 'Dosya bulunamadı.'], 404);
        }

        return response()->file($filePath, ['Content-Type' => 'application/pdf']);
    }
}
