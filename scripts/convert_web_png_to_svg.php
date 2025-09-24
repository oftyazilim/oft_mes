<?php
// Convert public/web.png into an SVG wrapper with embedded base64 image.
// Usage: php scripts/convert_web_png_to_svg.php

$pngPath = __DIR__ . '/../public/web.png';
$svgOut  = __DIR__ . '/../resources/images/web.svg';

if (!file_exists($pngPath)) {
  fwrite(STDERR, "PNG not found at $pngPath\n");
  exit(1);
}

[$width, $height, $type] = getimagesize($pngPath);
if ($type !== IMAGETYPE_PNG) {
  fwrite(STDERR, "Input is not a PNG. Type=$type\n");
  exit(1);
}

$data = file_get_contents($pngPath);
$base64 = base64_encode($data);

$svg = <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="$width" height="$height" viewBox="0 0 $width $height" xmlns:xlink="http://www.w3.org/1999/xlink">
  <image href="data:image/png;base64,$base64" x="0" y="0" width="$width" height="$height"/>
</svg>
SVG;

if (!is_dir(dirname($svgOut))) {
  mkdir(dirname($svgOut), 0775, true);
}

file_put_contents($svgOut, $svg);
echo "Created: $svgOut ($width x $height)\n";
