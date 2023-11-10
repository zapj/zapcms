<?php

namespace zap\image;

class Image
{

    private $file;

    private $image;

    private $width;

    private $height;

    private $bits;

    private $mimeType;


    private $dirName;
    private $baseName;
    private $extName;
    private $fileName;


    /**
     * Image constructor.
     *
     * @param $file
     */
    public function __construct($file)
    {
        if ( ! extension_loaded('gd')) {
            throw new \Exception('Error: PHP GD is not installed!');
        }

        if (file_exists($file)) {
            $this->file    = $file;
            $pathParts = pathinfo($file);
            $this->dirName = $pathParts['dirname'];
            $this->baseName = $pathParts['basename'];
            $this->extName = $pathParts['extension'] ? strtolower($pathParts['extension']) : '';
            $this->fileName = $pathParts['filename'];
            $info          = getimagesize($file);

            $this->width    = $info[0];
            $this->height   = $info[1];
            $this->bits     = isset($info['bits']) ? $info['bits'] : '';
            $this->mimeType = isset($info['mime']) ? $info['mime'] : '';

            if ($this->mimeType == 'image/gif') {
                $this->image = imagecreatefromgif($file);
            } elseif ($this->mimeType == 'image/png') {
                $this->image = imagecreatefrompng($file);
            } elseif ($this->mimeType == 'image/jpeg') {
                $this->image = imagecreatefromjpeg($file);
            }
        } else {
            throw new \Exception('Error: Could not load image '.$file.'!');
        }
    }

    /**
     * @param $path
     *
     * @return Image
     */
    public static function from($path)
    {
        return new Image($path);
    }

    public function getImageData()
    {
        ob_start();
        imagepng($this->image);
        $imageContent = ob_get_contents();
        ob_end_clean();

        return $imageContent;
    }

    public function toBase64()
    {
        return 'data:'.$this->mimeType.';base64,'.base64_encode(
                $this->getImageData()
            );
    }

    /**
     * 输出到浏览器
     */
    public function toBrowser()
    {
        header('Content-Type: '.$this->mimeType);
        if ($this->extName == 'jpeg' || $this->extName == 'jpg') {
            imagejpeg($this->image, null, 100);
        } elseif ($this->extName == 'png') {
            imagepng($this->image);
        } elseif ($this->extName == 'gif') {
            imagegif($this->image);
        }
    }

    public function getExif()
    {
        if ($this->mimeType === 'image/jpeg'
            && function_exists(
                'exif_read_data'
            )
        ) {
            return @exif_read_data($this->file);
        }

        return null;
    }

    /**
     * 获取分辨率 array(res_x,res_y)
     * res_x
     * The horizontal resolution in DPI.
     *
     * res_y
     * The vertical resolution in DPI.
     *
     * @return array|bool
     */
    public function getResolution()
    {
        return imageresolution($this->image);
    }

    public function download($filename, $mimeType = null, $quality = 100)
    {
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-Length: '.filesize($this->file));
        header('Content-Transfer-Encoding: Binary');
        header('Content-Type: application/octet-stream');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        if ($this->extName == 'jpeg' || $this->extName == 'jpg') {
            imagejpeg($this->image, null, $quality);
        } elseif ($this->extName == 'png') {
            imagepng($this->image);
        } elseif ($this->extName == 'gif') {
            imagegif($this->image);
        }
    }

    public function thumb($width, $height, $anchor = 'center')
    {
        $currentRatio = $this->getHeight() / $this->getWidth();
        $targetRatio  = $height / $width;
        // Fit to height/width
        if ($targetRatio > $currentRatio) {
            $this->resize(null, $height);
        } else {
            $this->resize($width, null);
        }
        switch ($anchor) {
            case 'top':
                $x1 = floor(($this->getWidth() / 2) - ($width / 2));
                $x2 = $width + $x1;
                $y1 = 0;
                $y2 = $height;
                break;
            case 'bottom':
                $x1 = floor(($this->getWidth() / 2) - ($width / 2));
                $x2 = $width + $x1;
                $y1 = $this->getHeight() - $height;
                $y2 = $this->getHeight();
                break;
            case 'left':
                $x1 = 0;
                $x2 = $width;
                $y1 = floor(($this->getHeight() / 2) - ($height / 2));
                $y2 = $height + $y1;
                break;
            case 'right':
                $x1 = $this->getWidth() - $width;
                $x2 = $this->getWidth();
                $y1 = floor(($this->getHeight() / 2) - ($height / 2));
                $y2 = $height + $y1;
                break;
            case 'top left':
                $x1 = 0;
                $x2 = $width;
                $y1 = 0;
                $y2 = $height;
                break;
            case 'top right':
                $x1 = $this->getWidth() - $width;
                $x2 = $this->getWidth();
                $y1 = 0;
                $y2 = $height;
                break;
            case 'bottom left':
                $x1 = 0;
                $x2 = $width;
                $y1 = $this->getHeight() - $height;
                $y2 = $this->getHeight();
                break;
            case 'bottom right':
                $x1 = $this->getWidth() - $width;
                $x2 = $this->getWidth();
                $y1 = $this->getHeight() - $height;
                $y2 = $this->getHeight();
                break;
            default:
                $x1 = floor(($this->getWidth() / 2) - ($width / 2));
                $x2 = $width + $x1;
                $y1 = floor(($this->getHeight() / 2) - ($height / 2));
                $y2 = $height + $y1;
                break;
        }

        // Return the cropped thumbnail image
        return $this->crop($x1, $y1, $x2, $y2);
    }

    /**
     *
     *
     * @param  string  $file
     * @param  int     $quality
     * @return bool    $ret
     */
    public function save($file, $quality = 90)
    {
        $ret = false;
        if ($this->image) {
            if ($this->extName == 'jpeg' || $this->extName == 'jpg') {
                $ret = imagejpeg($this->image, $file, $quality);
            } elseif ($this->extName == 'png') {
                $ret = imagepng($this->image, $file);
            } elseif ($this->extName == 'gif') {
                $ret = imagegif($this->image, $file);
            }

            imagedestroy($this->image);
        }
        return $ret;
    }

    public function savePath($path , $quality = 90){
        if(!is_dir($path) && mkdir($path,0755,true) === false){
            throw new \Exception('No permission to create directory , '.$path);
        }

        $file = $path  . '/' . $this->fileName . '.' . $this->extName;

        return $this->save($file,$quality);
    }

    public function saveFile($file, $quality = 90){
        $dir = dirname($file);
        if(!is_dir($dir) && mkdir($dir,0755,true) === false){
            throw new \Exception('No permission to create directory , '.$dir);
        }
        return $this->save($file,$quality);
    }


    //filter

    /**
     * 翻转颜色
     *
     * @return $this
     */
    public function invert()
    {
        imagefilter($this->image, IMG_FILTER_NEGATE);

        return $this;
    }


    public function resize($width = 0, $height = 0)
    {
        if ( ! $width && ! $height) {
            return $this;
        }
        // Resize to width
        if ($width && ! $height) {
            $height = $width / ($this->getWidth() / $this->getHeight());
        }
        // Resize to height
        if ( ! $width && $height) {
            $width = $height * ($this->getWidth() / $this->getHeight());
        }

        if ($this->getWidth() === $width && $this->getHeight() === $height) {
            return $this;
        }

        $newImage         = imagecreatetruecolor($width, $height);
        $transparentColor = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
        imagecolortransparent($newImage, $transparentColor);
        imagefill($newImage, 0, 0, $transparentColor);
        imagecopyresampled(
            $newImage,
            $this->image,
            0, 0, 0, 0,
            $width,
            $height,
            $this->getWidth(),
            $this->getHeight()
        );

        $this->image  = $newImage;
        $this->width  = $width;
        $this->height = $height;

        return $this;
    }

    /**
     *
     *
     * @param  \zap\image\Image  $watermark
     * @param  string  $position
     */
    public function watermark($watermark, $position = 'bottomright')
    {
        switch ($position) {
            case 'topleft':
                $watermark_pos_x = 0;
                $watermark_pos_y = 0;
                break;
            case 'topcenter':
                $watermark_pos_x = intval(
                    ($this->width - $watermark->getWidth()) / 2
                );
                $watermark_pos_y = 0;
                break;
            case 'topright':
                $watermark_pos_x = $this->width - $watermark->getWidth();
                $watermark_pos_y = 0;
                break;
            case 'middleleft':
                $watermark_pos_x = 0;
                $watermark_pos_y = intval(
                    ($this->height - $watermark->getHeight()) / 2
                );
                break;
            case 'middlecenter':
                $watermark_pos_x = intval(
                    ($this->width - $watermark->getWidth()) / 2
                );
                $watermark_pos_y = intval(
                    ($this->height - $watermark->getHeight()) / 2
                );
                break;
            case 'middleright':
                $watermark_pos_x = $this->width - $watermark->getWidth();
                $watermark_pos_y = intval(
                    ($this->height - $watermark->getHeight()) / 2
                );
                break;
            case 'bottomleft':
                $watermark_pos_x = 0;
                $watermark_pos_y = $this->height - $watermark->getHeight();
                break;
            case 'bottomcenter':
                $watermark_pos_x = intval(
                    ($this->width - $watermark->getWidth()) / 2
                );
                $watermark_pos_y = $this->height - $watermark->getHeight();
                break;
            case 'bottomright':
                $watermark_pos_x = $this->width - $watermark->getWidth();
                $watermark_pos_y = $this->height - $watermark->getHeight();
                break;
        }

        imagealphablending($this->image, true);
        imagesavealpha($this->image, true);
        imagecopy(
            $this->image, $watermark->getImage(), $watermark_pos_x,
            $watermark_pos_y, 0, 0, $watermark->getWidth(),
            $watermark->getHeight()
        );

        imagedestroy($watermark->getImage());
    }


    public function crop($x1, $y1, $x2, $y2)
    {
        // Keep crop within image dimensions
        $x1 = $this->valueLimit($x1, 0, $this->getWidth());
        $x2 = $this->valueLimit($x2, 0, $this->getWidth());
        $y1 = $this->valueLimit($y1, 0, $this->getHeight());
        $y2 = $this->valueLimit($y2, 0, $this->getHeight());
        // Crop it
        $this->image = imagecrop($this->image, [
            'x'      => min($x1, $x2),
            'y'      => min($y1, $y2),
            'width'  => abs($x2 - $x1),
            'height' => abs($y2 - $y1),
        ]);

        return $this;
    }

    /**
     *
     *
     * @param  int     $degree
     * @param  string  $color
     */
    public function rotate($degree, $color = 'FFFFFF')
    {
        $rgb = $this->html2rgb($color);

        $this->image = imagerotate(
            $this->image, $degree,
            imagecolorallocate($this->image, $rgb[0], $rgb[1], $rgb[2])
        );

        $this->width  = imagesx($this->image);
        $this->height = imagesy($this->image);
    }

    /**
     *
     *
     */
    private function filter()
    {
        $args = func_get_args();

        call_user_func_array('imagefilter', $args);
    }

    /**
     *
     *
     * @param  string  $text
     * @param  int     $x
     * @param  int     $y
     * @param  int     $size
     * @param  string  $color
     */
    private function text($text, $x = 0, $y = 0, $size = 5, $color = '000000')
    {
        $rgb = $this->html2rgb($color);

        imagestring(
            $this->image, $size, $x, $y, $text,
            imagecolorallocate($this->image, $rgb[0], $rgb[1], $rgb[2])
        );
    }

    /**
     *
     *
     * @param  object  $merge
     * @param  object  $x
     * @param  object  $y
     * @param  object  $opacity
     */
    private function merge($merge, $x = 0, $y = 0, $opacity = 100)
    {
        imagecopymerge(
            $this->image, $merge->getImage(), $x, $y, 0, 0, $merge->getWidth(),
            $merge->getHeight(), $opacity
        );
    }

    /**
     *
     *
     * @param  string  $color
     *
     * @return    array
     */
    private function html2rgb($color)
    {
        if ($color[0] == '#') {
            $color = substr($color, 1);
        }

        if (strlen($color) == 6) {
            [$r, $g, $b] = [$color[0].$color[1], $color[2].$color[3],
                            $color[4].$color[5]];
        } elseif (strlen($color) == 3) {
            [$r, $g, $b] = [$color[0].$color[0], $color[1].$color[1],
                            $color[2].$color[2]];
        } else {
            return false;
        }

        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);

        return [$r, $g, $b];
    }

    private function valueLimit($value, $min, $max)
    {
        if ($value < $min) {
            return $min;
        }
        if ($value > $max) {
            return $max;
        }

        return $value;
    }

    /**
     * @return string 获取文件
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     *
     *
     * @return    array
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     *
     *
     * @return    string
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     *
     *
     * @return    string
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     *
     *
     * @return    string
     */
    public function getBits()
    {
        return $this->bits;
    }

    /**
     *
     *
     * @return    string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @return string
     */
    public function getExtName(): string
    {
        return $this->extName;
    }

}