QuarkPHP Image
==============

PHP Class to resize images, it supports JPEG, PNG and no animated GIF, also supports transparency for PNG8, PNG24 and GIF.

_This class is intended to be part of [QuarkPHP](Quark-PHP-Framwork) framework future version 3.6, but you can use it as standalone component in your own projects._

With QuarkPHP Image you can:
-------

* Proportional shrink or stretch to a given width and height
* Unproportional resize to a given width and height
* Convert images between JPEG, PNG and GIF
* Save images to disk or send to output buffer

How to use
----------

Just copy `QuarkImage.php` and `QuarkImageException.php` to your classes directory and include `QuarkImage.php` in your PHP script, example:

    require 'QuarkImage.php';
    $Image = new QuarkImage('your-cool-image.jpg');
    $Image->resize(300, 200);
    $Image->output('resized-image.jpg');
    
See test.php for more details.

Test the source.
----------------

Download the source and run `test.php`

Issues
------
See the [known issues](quarkphp-image/issues/).
