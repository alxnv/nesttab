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

    /**
     * Constructor (loads file image to $img variable and checks if this is a valid
     *   image of given type)
     * @param string $fn - file name
     * @return type - is this a valid image of given type
     */
    public function __construct(string $fn) {
        $b = $this->readFileTestExt($fn);
        return $b;
    }
    
    /**
     * Reads file contents and check if this is a valid file of this type
     * @param string $fn - filename
     * @return boolean - is this a valid image of given type
     */
    public function readFileTestExt(string $fn) {
        $contentType = mime_content_type($fn);
        if ($contentType === false) return false;
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
     * @return boolean - if original file's width is less then new, and nothing has 
     *   changed, returns false
     *   true, if the file was resized
     */
    public function resize(int $w, int $h, string $type) {
        switch ($type) {
            case 'cover':
                // изображение может быть обрезано
                break;
            case 'contain':
                break;
            default:
                throw new \Exception('Type of conversion not in given list');
        }
    }
}
