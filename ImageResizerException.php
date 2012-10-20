<?php
class ImageResizerException extends Exception
{
  const FILE_NOT_FOUND          = 1;
  const CANT_READ_IMAGEINFO     = 2;
  const CANT_DETERMINE_SIZE     = 4;
  const OUTPUT_ERROR            = 8;
  const IMAGETYPE_NOT_SUPPORTED = 16;
  
  public function __construct($message, $code)
  {
    parent::__construct($message, $code);
  }
}
