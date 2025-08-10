<?php
// Simple converter: make near-white background transparent for OFT-Logo.png
// Usage: php scripts/convert_logo.php

declare(strict_types=1);

$source = __DIR__ . '/../public/images/svg/OFT-Logo.png';
$target = __DIR__ . '/../public/images/svg/OFT-Logo_transparent.png';

if (!file_exists($source)) {
    fwrite(STDERR, "Source not found: {$source}\n");
    exit(1);
}

$img = imagecreatefrompng($source);
if (!$img) {
    fwrite(STDERR, "Failed to load PNG: {$source}\n");
    exit(1);
}

// Ensure truecolor for alpha ops
if (!imageistruecolor($img)) {
    $true = imagecreatetruecolor(imagesx($img), imagesy($img));
    imagesavealpha($true, true);
    $fill = imagecolorallocatealpha($true, 0, 0, 0, 127);
    imagefill($true, 0, 0, $fill);
    imagecopy($true, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));
    imagedestroy($img);
    $img = $true;
}

imagealphablending($img, false);
imagesavealpha($img, true);

$w = imagesx($img);
$h = imagesy($img);

// Allocate a fully transparent color once
$transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);

// Threshold to detect near-white background (tune if needed)
$threshold = 248; // 0..255, higher means stricter white

for ($y = 0; $y < $h; $y++) {
    for ($x = 0; $x < $w; $x++) {
        $rgba = imagecolorat($img, $x, $y);
        $r = ($rgba >> 16) & 0xFF;
        $g = ($rgba >> 8) & 0xFF;
        $b = $rgba & 0xFF;

        if ($r >= $threshold && $g >= $threshold && $b >= $threshold) {
            imagesetpixel($img, $x, $y, $transparent);
        }
    }
}

// Save with alpha preserved
if (!imagepng($img, $target, 9)) {
    fwrite(STDERR, "Failed to write target: {$target}\n");
    imagedestroy($img);
    exit(1);
}

imagedestroy($img);
fwrite(STDOUT, "Wrote transparent logo: {$target}\n");
