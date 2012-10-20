<?php
require 'ImageResizer.php';

/*
 * Wider to all
 */

// Resize wider to wider
$ImageWider = new ImageResizer('png-with-alpha.png');
$ImageWider->resize(800, 600, ImageResizer::RESIZE_PROPORTIONAL);
// Convert this image to GIF
$ImageWider->setOutputImageType(IMAGETYPE_GIF);
$ImageWider->output('out/wider-to-wider.gif');

// Resize wider to taller
$ImageWider->resize(1024, 768, ImageResizer::RESIZE_PROPORTIONAL);
$ImageWider->output('out/wider-to-taller.png');

// Resize wider to square
$ImageWider->resize(100, 100, ImageResizer::RESIZE_PROPORTIONAL);
$ImageWider->output('out/wider-to-square.png');

/*
 * Taller to all
 */

// Resize taller to wider
$ImageTaller = new ImageResizer('400x600.jpg');
$ImageTaller->resize(300, 100, ImageResizer::RESIZE_PROPORTIONAL);
$ImageTaller->output('out/taller-to-wider.jpg');

// Resize taller to taller
$ImageTaller->resize(100, 300, ImageResizer::RESIZE_PROPORTIONAL);
$ImageTaller->output('out/taller-to-taller.jpg');

// Resize taller to square
$ImageTaller->resize(100, 100, ImageResizer::RESIZE_PROPORTIONAL);
$ImageTaller->output('out/taller-to-square.jpg');

/*
 * Square to all
 */

// Resize square to wider
$ImageSquare = new ImageResizer('400x400.jpg');
$ImageSquare->resize(300, 100, ImageResizer::RESIZE_PROPORTIONAL);
$ImageSquare->output('out/square-to-wider.jpg');

// Resize square to taller
$ImageSquare->resize(100, 300, ImageResizer::RESIZE_PROPORTIONAL);
$ImageSquare->output('out/square-to-taller.jpg');

// Resize square to square
$ImageSquare->resize(100, 100, ImageResizer::RESIZE_PROPORTIONAL);
$ImageSquare->output('out/square-to-square.jpg');
