<?php
require 'QuarkImage.php';

// Test with wider image
$Image = new QuarkImage('img/600x400.jpg');

// Test with taller image
//$Image = new QuarkImage('img/400x600.jpg');

// Test with square image
//$Image = new QuarkImage('img/400x400.jpg');

/*
 * By default the resize is proportional
 */

// Resize wider than taller
$Image->resize(300, 100);
$Image->output('out/wider.jpg');

// Resize taller than wider
$Image->resize(100, 300);
$Image->output('out/taller.jpg');

// Resize square
$Image->resize(100, 100);
$Image->output('out/square.jpg');

// No resize if max width and max height are bigger than original
$Image->resize(1280, 720);
$Image->output('out/proportional-no-stretch.jpg');

// Resize agressive, no proportional, image will be deformed.
$Image->resize(800, 100, QuarkImage::RESIZE_STRETCH);
$Image->output('out/agressive.jpg');

// Resize proportional even if max width and max height are bigger than original
$Image->resize(1280, 720,
  QuarkImage::RESIZE_STRETCH|QuarkImage::RESIZE_PROPORTIONAL
);
$Image->output('out/proportional-stretch.jpg');

/* =============================================================================
 * Images with transparency
 */

// Test with PNG24 transparency
$Image = new QuarkImage('img/png24-with-alpha.png');
$Image->resize(190, 120);
$Image->output('out/png24.png');

// Test with PNG8 transparency
$Image = new QuarkImage('img/png8-with-alpha.png');
$Image->resize(190, 120);
$Image->output('out/png8.png');

// Test with GIF
$Image = new QuarkImage('img/320x240.gif');
$Image->resize(190, 120);
$Image->output('out/the-gif.gif');

/* =============================================================================
 * Convert PNG to JPG
 */

$Image = new QuarkImage('img/angry_unicorn.png');
$Image->setOutputImageType(IMAGETYPE_JPEG);
$Image->output('out/converted.jpg');
