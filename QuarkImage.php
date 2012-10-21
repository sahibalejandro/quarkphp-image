<?php
require_once 'QuarkImageException.php';

class QuarkImage
{
  /**
   * Original image file path
   * 
   * @var string
   */
  private $image_file;
  
  /**
   * Image info obtained from getimagesize()
   * 
   * @var array
   */
  private $src_image_info;
  private $dst_image_info;
  
  /**
   * Image resource obtained from the original image
   * 
   * @var resource
   */
  private $src_image;
  private $dst_image;
  
  /**
   * JPEG output quality, from 0 (low quality) to 100 (high quality)
   * 
   * @var int
   */
  private $jpeg_quality = 90;
  
  /**
   * PNG output compression
   * 
   * @var int
   */
  private $png_compression = 9;
  private $png_filters     = null;
  
  /**
   * Resize types
   */
  const RESIZE_PROPORTIONAL = 1;
  const RESIZE_STRETCH      = 2;
  
  /**
   * Output methods
   */
  const OUTPUT_FILE   = 4;
  const OUTPUT_STREAM = 8;
  
  /**
   * Read the image file from which generate new images
   * 
   * @param string $image_file Image file path
   */
  public function __construct($image_file)
  {
    // Ignore E_WARNINGS or E_NOTICE errors.
    $error_reporting_level = error_reporting(E_ERROR | E_PARSE);
    
    /* Read image data and populate the object with it, or throw
     * exception if something is wrong.
     */
    if (!file_exists($image_file)) {
      throw new QuarkImageException(
        'Image file '.$image_file.' not found.',
        QuarkImageException::FILE_NOT_FOUND
      );
    } elseif (!($this->src_image_info = getimagesize($image_file))) {
      throw new QuarkImageException(
        "Can't read image info from file ".$image_file,
        QuarkImageException::CANT_READ_IMAGEINFO
      );
    } elseif (!$this->isImageTypeSupported($this->src_image_info[2])) {
      throw new QuarkImageException(
        'Image '.$image_file.' not supported',
        QuarkImageException::IMAGETYPE_NOT_SUPPORTED
      );
    } elseif ($this->src_image_info[0] == 0 || $this->src_image_info[1] == 0) {
      throw new QuarkImageException(
        "Can't determine the image size from file ".$image_file,
        QuarkImageException::CANT_DETERMINE_SIZE
      );
    } elseif (
      !($this->src_image = imagecreatefromstring(file_get_contents($image_file)))
    ) {
      throw new QuarkImageException(
        "Can't read image file ".$image_file,
        QuarkImageException::CANT_READ_IMAGE
      );
    }
    
    // Switch back the error reporting level
    error_reporting($error_reporting_level);
    
    $this->resetDstImage();
    $this->image_file = $image_file;
  }
  
  /**
   * Resize the image to width $w and height $h with type $resize_type
   */
  public function resize(
    $w, $h,
    $resize_type = QuarkImage::RESIZE_PROPORTIONAL
  ) {
    
    // Configure for proportional resizing with stretch
    $proportional_stretch = false;
    if ($resize_type == (self::RESIZE_PROPORTIONAL|self::RESIZE_STRETCH)) {
      $proportional_stretch = true;
      $resize_type          = self::RESIZE_PROPORTIONAL;
    }
    
    // Ignore E_WARNINGS or E_NOTICE errors.
    $error_reporting_level = error_reporting(E_ERROR | E_PARSE);
    
    switch ($resize_type) {
      case self::RESIZE_STRETCH:
        $new_w = $w;
        $new_h = $h;
        break;
      case self::RESIZE_PROPORTIONAL:
        if ($this->dst_image_info[0] == $this->dst_image_info[1]) {
          // Max width and max height are equal
          if ($proportional_stretch){ 
            $new_w = $new_h = min($w, $h);
          } else {
            $new_w = $this->dst_image_info[0];
            $new_h = $this->dst_image_info[1];
          }
        } elseif ($this->dst_image_info[0] > $this->dst_image_info[1]) {
          // Calculate proportions from width
          list($new_w, $new_h) = $this->calculateProportionalSizes(
            $this->dst_image_info[0],
            $this->dst_image_info[1],
            $w, $h, $proportional_stretch
          );
        } elseif ($this->dst_image_info[0] < $this->dst_image_info[1]) {
          // Calculate proportions from height
          list($new_h, $new_w) = $this->calculateProportionalSizes(
            $this->dst_image_info[1],
            $this->dst_image_info[0],
            $h, $w, $proportional_stretch
          );
        }
        break;
      default:
        throw new QuarkImageException(
          'Invalid resize type',
          QuarkImageException::INVALID_RESIZE_TYPE
        );
        break;
    }
    
    // Create the new image with white background
    $canvas = imagecreatetruecolor($new_w, $new_h);
    imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
    
    // Handle transparency for PNG or GIF formats
    if ($this->dst_image_info[2]  == IMAGETYPE_PNG
      || $this->dst_image_info[2] == IMAGETYPE_GIF
    ) {
      $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
      imagealphablending($canvas, false);
      imagesavealpha($canvas, true);
      imagecolortransparent($canvas, $transparent);
      imagefilledrectangle($canvas, 0, 0, $new_w, $new_h, $transparent);
    }
    
    /* In order to preserve transparency in GIF we need to use imagecopyresized()
     * instead of imagecopyresampled(), this is the only way I know.
     */
    if ($this->dst_image_info[2] == IMAGETYPE_GIF) {
      imagecopyresized(
        $canvas,
        $this->dst_image,
        0, 0, 0, 0,
        $new_w, $new_h,
        $this->dst_image_info[0], $this->dst_image_info[1]
      );
    } else {
      imagecopyresampled(
        $canvas,
        $this->dst_image,
        0, 0, 0, 0,
        $new_w, $new_h,
        $this->dst_image_info[0], $this->dst_image_info[1]
      );
    }
    
    // Save changes
    $this->dst_image_info[0] = $new_w;
    $this->dst_image_info[1] = $new_h;
    $this->dst_image         = $canvas;
    
    // Switch back the error reporting level
    error_reporting($error_reporting_level);
  }
  
  
  /**
   * Output the working image
   * 
   * @param string $file_name
   * @param int $output_method
   */
  public function output($file_name, $output_method = QuarkImage::OUTPUT_FILE)
  {
    // Will be true if output is succesfull
    $done = false;
    
    // For stream output the $file_name must be null
    if ($output_method == QuarkImage::OUTPUT_STREAM) {
      $file_name = null;
    }
    
    // Ignore E_WARNINGS or E_NOTICE errors.
    $error_reporting_level = error_reporting(E_ERROR | E_PARSE);
    
    // Output the image in the specified output type and image format
    if ($output_method == self::OUTPUT_FILE
      || $output_method == self::OUTPUT_STREAM
    ) {
      /*
       * File or stream output
       */
      if ($this->dst_image_info[2] == IMAGETYPE_JPEG
        || $this->dst_image_info[2] == IMAGETYPE_JPEG2000
      ) {
        /** Output JPEG file */
        $done = imagejpeg($this->dst_image, $file_name, $this->jpeg_quality);
      } elseif ($this->dst_image_info[2] == IMAGETYPE_PNG) {
        /** Output PNG file */
        $done = imagepng(
          $this->dst_image,
          $file_name,
          $this->png_compression,
          $this->png_filters
        );
      } elseif ($this->dst_image_info[2] == IMAGETYPE_GIF) {
        /** Output GIF file */
        $done = imagegif($this->dst_image, $file_name);
      }
    }
    
    // Switch back the error reporting level
    error_reporting($error_reporting_level);
    
    if ($done) {
      $this->resetDstImage();
    } else {
      throw new QuarkImageException(
        "Fail to output the image, check write permissions",
        QuarkImageException::OUTPUT_ERROR
      );
    }
  }
  
  /**
   * Get the array retrieved by getimagesize() from the working image
   * 
   * @return array
   */
  public function getImageInfo()
  {
    return $this->dst_image_info;
  }
  
  /**
   * Get the array retrieved by getimagesize() from the original image
   * 
   * @return array
   */
  public function getImageInfoOriginal()
  {
    return $this->src_image_info;
  }
  
  /**
   * Set the output image type
   * 
   * @throws QuarkImageException if type is not supported.
   * @param int $image_type Image type value like PHP's IMATETYPE_XXX
   */
  public function setOutputImageType($image_type)
  {
    if ($this->isImageTypeSupported($image_type)) {
      $this->dst_image_info[2] = $image_type;
    } else {
      throw new QuarkImageException(
        'setOutputImageType: Image type not supported.',
        QuarkImageException::IMAGETYPE_NOT_SUPPORTED
      );
    }
  }
  
  /**
   * Set the output quality of JPEG images, from 0 (worst quality, smaller file)
   * to 100 (best quality, biggest file), default quality is 90
   * 
   * @param int $quality JPEG output quality
   */
  public function setJPEGQuality($quality)
  {
    $this->jpeg_quality = $quality;
  }
  
  /**
   * Set the output PNG compression, from 0 (no compression) to 9.
   * The default compression is 9
   * 
   * @param int $compression Compression level
   */
  public function setPNGCompression($compression)
  {
    $this->png_compression = $compression;
  }
  
  /**
   * Set the output PNG filters, by default no filters are used.
   * See: http://www.php.net/manual/en/function.imagepng.php to know more about it.
   */
  public function setPNGFilters($filters)
  {
    $this->png_filters = $filters;
  }
  
  /**
   * Proportionally calculate the sizes of $base_size1 and $base_size2 to fit
   * $max_size1 and $max_size2 and return the new size1 and new size2 in an array.
   * 
   * @param int $base_size1 Original "size1"
   * @param int $base_size2 Original "size2"
   * @param int $max_size1 Max value that "size1" can have
   * @param int $max_size2 Max value that "size2" can have
   * @return array(new_size1, new_size2)
   */
  private function calculateProportionalSizes(
    $base_size1,
    $base_size2,
    $max_size1,
    $max_size2,
    $stretch = false
  ) {
    
    if (!$stretch && $base_size1 <= $max_size1 && $base_size2 <= $max_size2) {
      $new_size1 = $base_size1;
      $new_size2 = $base_size2;
    } else {
      // Output sizes
      $new_size1 = $max_size1;
      $new_size2 = $max_size2;
      
      /*
       * Calculate new sizes.
       *
       * Algorithm is like:
       *   1. Match new_size1 to max_size1.
       *   2. Calculate the new_size2 proportional to max_size1.
       *   3. If new_size2 is still exceeding the value of max_size2 then recalculate
       *      the value of new_size1 proportional to max_size2 and match new_size2
       *      to max_size2
       */
      if ($base_size1 > $max_size1 || ($stretch && $max_size1 >= $base_size1)) {
        // new height = original height / original width * new width
        $new_size2 = ($base_size2 / $base_size1) * $max_size1;
      }
          
      if ($new_size2 > $max_size2) {
        // new width = original width / original height * new height
        $new_size1 = ($new_size1 / $new_size2) * $max_size2;
        $new_size2 = $max_size2;
      }
    }
    
    return array(round($new_size1), round($new_size2));
  }
  
  /**
   * Check if $image_type is supported
   * 
   * @param int $image_type Image type value like PHP's IMATETYPE_XXX
   * @return bool true if supported, false if not.
   */
  public function isImageTypeSupported($image_type)
  {
    return !(
      $image_type    != IMAGETYPE_JPEG
      && $image_type != IMAGETYPE_JPEG2000
      && $image_type != IMAGETYPE_PNG
      && $image_type != IMAGETYPE_GIF
    );
  }
  
  /**
   * Reset the dst image resource to src image resource
   * and dst image info to src image info.
   */
  private function resetDstImage()
  {
    if ($this->dst_image) {
      imagedestroy($this->dst_image);
    }
    
    $this->dst_image      = $this->src_image;
    $this->dst_image_info = $this->src_image_info;
  }
}
