<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileManagerController extends Controller
{
    // KK ve SK klasörlerini döndür
    public function index(Request $request)
    {
        $baseDirs = [
            'kk-fotolari' => storage_path('app/public/kk-fotolari'),
            'sk-fotolari' => storage_path('app/public/sk-fotolari'),
        ];
        $result = [];
        foreach ($baseDirs as $key => $dir) {
            $result[] = $this->scanDir($dir, $key, $key);
        }
        return response()->json($result);
    }

    // Düz dizi olarak dosya ve klasörleri döndürür
    private function scanDir($dir, $label, $parentPath = '')
    {
        $items = [];
        if (!File::exists($dir)) return [
            'name' => $label,
            'isDirectory' => true,
            'items' => [],
        ];
        foreach (File::directories($dir) as $subdir) {
            $subName = basename($subdir);
            $subPath = ltrim($parentPath . '/' . $subName, '/');
            $items[] = $this->scanDir($subdir, $subName, $subPath);
        }
        foreach (File::files($dir) as $file) {
            $name = basename($file);
            $relativePath = ltrim($parentPath . '/' . $name, '/');
            $items[] = [
                'name' => $name,
                'isDirectory' => false,
                'key' => $relativePath,
                'relativeName' => $relativePath,
                'url' => asset('storage/' . $relativePath),
                // DevExtreme FileManager shows file size if 'size' is provided (bytes)
                'size' => File::size($file),
                // Provide ISO 8601 modified time for client display if needed
                'dateModified' => date('c', File::lastModified($file)),
            ];
        }
        return [
            'name' => $label,
            'isDirectory' => true,
            'items' => $items,
            'key' => $parentPath,
            'relativeName' => $parentPath,
        ];
    }

    // Tam dosya yönetimi için
    public function handle(Request $request)
    {
        $command = $request->input('command');
        $args = $request->input('arguments', []);
        $response = ['success' => false];
        // (debug logging removed)
        try {
            switch ($command) {
                case 'CreateDir':
                    $base = $this->resolveBase($args['pathInfo'] ?? []);
                    $name = $args['name'] ?? 'YeniKlasor';
                    $target = $base . DIRECTORY_SEPARATOR . $name;
                    \Illuminate\Support\Facades\File::makeDirectory($target, 0775, true, true);
                    $response = ['success' => true];
                    break;
                case 'Delete':
                    $base = $this->resolveBase($args['pathInfo'] ?? []);
                    if (\Illuminate\Support\Facades\File::isDirectory($base)) {
                        \Illuminate\Support\Facades\File::deleteDirectory($base);
                    } else {
                        \Illuminate\Support\Facades\File::delete($base);
                    }
                    $response = ['success' => true];
                    break;
                case 'Rename':
                    $base = $this->resolveBase($args['pathInfo'] ?? []);
                    $newName = $args['newName'] ?? null;
                    if ($newName) {
                        $newPath = dirname($base) . DIRECTORY_SEPARATOR . $newName;
                        \Illuminate\Support\Facades\File::move($base, $newPath);
                        $response = ['success' => true];
                    }
                    break;
                case 'Move':
                    $from = $this->resolveBase($args['from'] ?? []);
                    $to = $this->resolveBase($args['to'] ?? []);
                    \Illuminate\Support\Facades\File::move($from, $to);
                    $response = ['success' => true];
                    break;
                case 'Copy':
                    $from = $this->resolveBase($args['from'] ?? []);
                    $to = $this->resolveBase($args['to'] ?? []);
                    if (\Illuminate\Support\Facades\File::isDirectory($from)) {
                        \Illuminate\Support\Facades\File::copyDirectory($from, $to);
                    } else {
                        \Illuminate\Support\Facades\File::copy($from, $to);
                    }
                    $response = ['success' => true];
                    break;
                case 'Upload':
                    $base = $this->resolveBase($args['pathInfo'] ?? []);
                    $file = $request->file('file');
                    if ($file) {
                        $file->move($base, $file->getClientOriginalName());
                        $response = ['success' => true];
                    }
                    break;
                case 'Download':
                    // Try to resolve full file path from provided arguments.
                    if (!empty($args['key'])) {
                        $relative = ltrim($args['key'], '/');
                        $filePath = storage_path('app/public/' . $relative);
                    } else {
                        $base = $this->resolveBase($args['pathInfo'] ?? []);
                        $name = $args['name'] ?? ($args['fileName'] ?? null);
                        $filePath = $name ? $base . DIRECTORY_SEPARATOR . $name : $base;
                    }
                    $exists = File::exists($filePath);
                    $isDir = $exists ? File::isDirectory($filePath) : false;
                    $size = $exists && !$isDir ? File::size($filePath) : null;
                    if ($exists && !$isDir) {
                        // Stream the file back as a download response.
                        return response()->download($filePath);
                    } else {
                        $response = ['success' => false, 'message' => 'File not found'];
                    }
                    break;
                default:
                    $response = ['success' => false, 'message' => 'Unknown command'];
            }
        } catch (\Throwable $e) {
            $response = ['success' => false, 'message' => $e->getMessage()];
        }
        return response()->json($response);
    }

    private function resolveBase($pathInfo)
    {
        // pathInfo örneği: ['kk-fotolari', 'altklasor']
        $base = storage_path('app/public');
        foreach ($pathInfo as $segment) {
            $base .= DIRECTORY_SEPARATOR . $segment;
        }
        return $base;
    }
}
