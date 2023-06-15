<?php


namespace Alxnv\Nesttab\Models;

/**
 * Description of ImageResizeModel
 *
 * @author Alexandr
 */
class ImageResizeModel {
    //put your code here
    protected $img; // image of the file loaded by imagecreatefrom<?gif> function
    protected $contentType; // content type of file loaded, for example, image/jpeg
    /**
     * Tries to save resized image to file
     * @param string $fn - source file
     * @param string $fnto - destination file
     * @param int $w - required width in pizels
     * @param int $h - required height in pixels
     * @param string $type - one of ('cover', 'contain')
     */
    public function resizeImage(string $fn, string $fnto, int $w, int $h, string $type) {
        $b = $this->readFileTestExt($fn);
        if (!$b) return false;
        $this->resize($w, $h, $type, $fn, $fnto);
        return true;
    }
    /**
     * Reads file contents and check if this is a valid file of this type
     * @param string $fn - filename
     * @return boolean - is this a valid image of given type
     */
    public function readFileTestExt(string $fn) {
        $contentType = mime_content_type($fn);
        if ($contentType === false) return false;
        $this->contentType = $contentType;
        switch ($contentType) {
            case 'image/gif' :
                $this->img = @imagecreatefromgif($fn);
                if (!$this->img) return false;
                break;
            case 'image/jpeg' :
                $this->img = @imagecreatefromjpeg($fn);
                if (!$this->img) return false;
                break;
            case 'image/png' :
                $this->img = @imagecreatefrompng($fn);
                if (!$this->img) return false;
                break;
            default: 
                return false;
        }
        return true;
    }
    
    /**
     * Resizes the file according to the given size
     * @param int $w - required width
     * @param int $h - required heights
     * @param string $type - 'cover' or 'contain'
     * @param string $fn - file name of $this->img
     * @param string $fnto - file name where to write processed image
     * @return boolean - if original file's width is less then new, and nothing has 
     *   changed, returns false
     *   true, if the file was resized
     */
    public function resize(int $w, int $h, string $type, string $fn, string $fnto) {
        $width = imagesx($this->img);
        $height = imagesy($this->img);
        if (($w == 0) || ($h == 0) || ($width == 0) || ($height == 0) 
                || (($width <= $w) && ($height <= $h))) {
            $b = \Alxnv\Nesttab\core\FileHelper::copyFile($fnto, $fn);
            // $b === false if it was error writing file
            return false;
        }
        $imgAsp = $width / $height;
        $reqAsp = $w / $h;
        
        switch ($type) {
            case 'cover':
                // изображение может быть обрезано
                if ($imgAsp < $reqAsp) {
                    if ($width < $w) {
                        $dstw = $width;
                        $dsth = $h;
                        $srcw = $width;
                        $srcx = 0;
                        $srch = $h;
                        $srcy = (($height - $h) >> 1);
                    } else {
                        $dstw = $w;
                        $dsth = $h;
                        $srcx = 0;
                        $srcw = $width;
                        $srch = $width * $h / $w;
                        $srcy = (($height - $srch) >> 1);
                    }
                    
                } else {
                    if ($height < $h) {
                        $dsth = $height;
                        $dstw = $w;
                        $srch = $height;
                        $srcy = 0;
                        $srcw = $w;
                        $srcx = (($width - $w) >> 1);
                    } else {
                        $dstw = $w;
                        $dsth = $h;
                        $srcy = 0;
                        $srch = $height;
                        $srcw = $height * $w / $h;
                        $srcx = (($width - $srcw) >> 1);
                    }
                }
                $thumb = imagecreatetruecolor($dstw, $dsth);
                imagecopyresized($thumb, $this->img, 0, 0, $srcx, $srcy,
                        $dstw, $dsth, $srcw,
                        $srch);
                $this->saveTo($fnto, $thumb);
                       
                break;
            case 'contain':
                if ($imgAsp < $reqAsp) {
                    // 100% height
                    $dsth = $h;
                    $dstw = $width * $h / $height; 
                } else {
                    $dstw = $w;
                    $dsth = $height * $w / $width;
                }
                $thumb = imagecreatetruecolor($dstw, $dsth);
                imagecopyresized($thumb, $this->img, 0, 0, 0, 0, $dstw, $dsth, $width,
                        $height);
                $this->saveTo($fnto, $thumb);
                break;
            default:
                throw new \Exception('Type of conversion not in given list');
        }
        return true;
    }
    
    /**
     * Saves the image to the file of corresponding type
     * @param string $fn - file to save to
     * @param type $thumb - image to save to
     * @return boolean
     */
    public function saveTo(string $fn, $thumb) {
        switch ($this->contentType) {
            case 'image/gif' :
                imagegif($thumb, $fn);
                break;
            case 'image/jpeg' :
                imagejpeg($thumb, $fn, 90);
                break;
            case 'image/png' :
                imagepng($thumb, $fn);
                break;
            default: 
                return false;
        }
        return true;
    }
}
