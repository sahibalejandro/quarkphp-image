<?php
require 'ImageResizer.php';

// Test with wider image
$Image = new ImageResizer('img/600x400.jpg');

// Test with taller image
//$Image = new ImageResizer('img/400x600.jpg');

// Test with square image
//$Image = new ImageResizer('img/400x400.jpg');

// Test with PNG24 transparency (change file extensions to .png in outputs)
//$Image = new ImageResizer('img/png24-with-alpha.png');

// Test with PNG8 transparency (change file extensions to .png in outputs)
//$Image = new ImageResizer('img/png8-with-alpha.png');

// Test with GIF (change file extensions to .gif in outputs)
// $Image = new ImageResizer('img/320x240.gif');

// Resize wider than taller
$Image->resize(300, 100, ImageResizer::RESIZE_PROPORTIONAL);
$Image->output('out/wider.jpg');

// Resize taller than wider
$Image->resize(100, 300, ImageResizer::RESIZE_PROPORTIONAL);
$Image->output('out/taller.jpg');

// Resize square
$Image->resize(100, 100, ImageResizer::RESIZE_PROPORTIONAL);
$Image->output('out/square.jpg');

// No resize if max width and max height are bigger than original
$Image->resize(1280, 720, ImageResizer::RESIZE_PROPORTIONAL);
$Image->output('out/proportional-no-stretch.jpg');

// Resize proportional even if max width and max height are bigger than original
$Image->resize(1280, 720,
  ImageResizer::RESIZE_STRETCH|ImageResizer::RESIZE_PROPORTIONAL
);
$Image->output('out/proportional-stretch.jpg');

// Resize agressive, no proportional
$Image->resize(800, 100, ImageResizer::RESIZE_STRETCH);
$Image->output('out/agressive.jpg');
