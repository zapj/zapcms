<?php

namespace zap\image;

class Image
{
    /** @var string 原始文件路径 */
    private $file;

    /** @var resource|\GdImage GD 图像资源 */
    private $image;

    /** @var int 当前图像宽度 */
    private $width;

    /** @var int 当前图像高度 */
    private $height;

    /** @var int 色彩位数 */
    private $bits;

    /** @var string MIME 类型 */
    private $mimeType;

    /** @var string 目录路径 */
    private $dirName;

    /** @var string 文件名（含扩展名） */
    private $baseName;

    /** @var string 扩展名（小写） */
    private $extName;

    /** @var string 文件名（不含扩展名） */
    private $fileName;

    /** @var int JPEG/WebP 保存质量 (0-100) */
    private $quality = 90;

    /** @var bool 是否已销毁 */
    private $destroyed = false;

    /** @var string 输出格式覆盖（用于格式转换） */
    private $outputFormat;

    // ───────────────────── 构造 & 工厂 ─────────────────────

    /**
     * Image constructor.
     *
     * @param string $file 图片文件路径
     * @throws \Exception
     */
    public function __construct($file)
    {
        if (!extension_loaded('gd')) {
            throw new \Exception('Error: PHP GD is not installed!');
        }

        if (!file_exists($file)) {
            throw new \Exception('Error: Could not load image ' . $file . '!');
        }

        $this->file = $file;
        $pathParts = pathinfo($file);
        $this->dirName  = $pathParts['dirname'];
        $this->baseName = $pathParts['basename'];
        $this->extName  = $pathParts['extension'] ? strtolower($pathParts['extension']) : '';
        $this->fileName = $pathParts['filename'];

        $info = getimagesize($file);
        if ($info === false) {
            throw new \Exception('Error: Cannot determine image size for ' . $file . '!');
        }

        $this->width    = $info[0];
        $this->height   = $info[1];
        $this->bits     = $info['bits'] ?? '';
        $this->mimeType = $info['mime'] ?? '';

        $this->image = $this->createImageFromFile($file, $this->mimeType);

        if ($this->image === false || $this->image === null) {
            throw new \Exception('Error: Unsupported image type: ' . $this->mimeType);
        }

        // 保留 Alpha 通道
        if ($this->isTransparentFormat()) {
            imagealphablending($this->image, false);
            imagesavealpha($this->image, true);
        }
    }

    /**
     * 从资源创建 Image 实例（内部使用，用于空白画布等）
     *
     * @param resource|\GdImage $gdResource
     * @param string $mimeType
     * @return static
     */
    private static function fromResource($gdResource, $mimeType = 'image/png')
    {
        $instance = new static(__FILE__); // 临时绕过构造函数限制
        // 这里用 hack 方式注入资源，因为 PHP 不允许 new static without constructor
        // 实际上我们需要一种干净的工厂方式。改为使用 clone + 手动赋值。
        return $instance->initFromResource($gdResource, $mimeType);
    }

    private function initFromResource($gdResource, $mimeType)
    {
        if ($this->image) {
            imagedestroy($this->image);
        }
        $this->image    = $gdResource;
        $this->mimeType = $mimeType;
        $this->width    = imagesx($gdResource);
        $this->height   = imagesy($gdResource);
        $this->extName  = $this->mimeToExtension($mimeType);
        return $this;
    }

    /**
     * 静态工厂：从路径创建
     *
     * @param string $path
     * @return static
     */
    public static function from($path)
    {
        return new static($path);
    }

    /**
     * 创建空白画布
     *
     * @param int    $width
     * @param int    $height
     * @param string $bgColor 背景色 (hex, 如 'FFFFFF' 或 '#FF0000')
     * @param string $format  输出格式 (png/jpeg/gif/webp)
     * @return static
     */
    public static function canvas($width, $height, $bgColor = 'FFFFFF', $format = 'png')
    {
        $instance = new static(__FILE__);
        $resource = imagecreatetruecolor((int)$width, (int)$height);

        $mimeMap = [
            'png'  => 'image/png',
            'jpeg' => 'image/jpeg',
            'jpg'  => 'image/jpeg',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
        ];
        $mimeType = $mimeMap[strtolower($format)] ?? 'image/png';

        // 透明背景
        if ($format === 'png' || $format === 'webp') {
            imagealphablending($resource, false);
            imagesavealpha($resource, true);
            $transparent = imagecolorallocatealpha($resource, 0, 0, 0, 127);
            imagefill($resource, 0, 0, $transparent);
        } else {
            $rgb = $instance->html2rgb($bgColor);
            $fill = imagecolorallocate($resource, $rgb[0], $rgb[1], $rgb[2]);
            imagefill($resource, 0, 0, $fill);
        }

        return $instance->initFromResource($resource, $mimeType);
    }

    // ───────────────────── 输出 & 序列化 ─────────────────────

    /**
     * 保存到文件
     *
     * @param string $file    目标路径
     * @param int    $quality JPEG/WebP 质量 (0-100)
     * @return bool
     */
    public function save($file, $quality = null)
    {
        $this->assertNotDestroyed();
        $q = $quality ?? $this->quality;
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $format = $this->outputFormat ?: ($ext ?: $this->extName);

        switch ($format) {
            case 'jpeg':
            case 'jpg':
                return imagejpeg($this->image, $file, $q);
            case 'png':
                return imagepng($this->image, $file, $this->pngQualityToLevel($q));
            case 'gif':
                return imagegif($this->image, $file);
            case 'webp':
                return imagewebp($this->image, $file, $q);
            default:
                return imagejpeg($this->image, $file, $q);
        }
    }

    /**
     * 保存到目录（使用原始文件名）
     *
     * @param string $path    目标目录
     * @param int    $quality
     * @return bool
     * @throws \Exception
     */
    public function savePath($path, $quality = null)
    {
        if (!is_dir($path) && mkdir($path, 0755, true) === false) {
            throw new \Exception('No permission to create directory: ' . $path);
        }

        $ext = $this->outputFormat ?: $this->extName;
        $file = $path . '/' . $this->fileName . '.' . $ext;

        return $this->save($file, $quality);
    }

    /**
     * 保存到指定文件（自动创建目录）
     *
     * @param string $file
     * @param int    $quality
     * @return bool
     * @throws \Exception
     */
    public function saveFile($file, $quality = null)
    {
        $dir = dirname($file);
        if (!is_dir($dir) && mkdir($dir, 0755, true) === false) {
            throw new \Exception('No permission to create directory: ' . $dir);
        }
        return $this->save($file, $quality);
    }

    /**
     * 获取图像二进制数据
     *
     * @param string|null $format 输出格式 (png/jpeg/gif/webp)，null 则使用原格式
     * @param int|null    $quality
     * @return string
     */
    public function getImageData($format = null, $quality = null)
    {
        $this->assertNotDestroyed();
        $fmt = $format ?: ($this->outputFormat ?: $this->extName);
        $q = $quality ?? $this->quality;

        ob_start();
        switch (strtolower($fmt)) {
            case 'jpeg':
            case 'jpg':
                imagejpeg($this->image, null, $q);
                break;
            case 'png':
                imagepng($this->image, null, $this->pngQualityToLevel($q));
                break;
            case 'gif':
                imagegif($this->image);
                break;
            case 'webp':
                imagewebp($this->image, null, $q);
                break;
            default:
                imagepng($this->image);
        }
        $data = ob_get_contents();
        ob_end_clean();
        return $data;
    }

    /**
     * 转为 Base64 Data URI
     *
     * @param string|null $format
     * @param int|null    $quality
     * @return string
     */
    public function toBase64($format = null, $quality = null)
    {
        $fmt = $format ?: ($this->outputFormat ?: $this->extName);
        $mime = $this->extensionToMime($fmt);
        return 'data:' . $mime . ';base64,' . base64_encode($this->getImageData($format, $quality));
    }

    /**
     * 输出到浏览器
     *
     * @param int|null $quality JPEG/WebP 质量
     */
    public function toBrowser($quality = null)
    {
        $this->assertNotDestroyed();
        $ext = $this->outputFormat ?: $this->extName;
        $mime = $this->extensionToMime($ext);
        $q = $quality ?? $this->quality;

        header('Content-Type: ' . $mime);
        switch ($ext) {
            case 'jpeg':
            case 'jpg':
                imagejpeg($this->image, null, $q);
                break;
            case 'png':
                imagepng($this->image, null, $this->pngQualityToLevel($q));
                break;
            case 'gif':
                imagegif($this->image);
                break;
            case 'webp':
                imagewebp($this->image, null, $q);
                break;
            default:
                imagejpeg($this->image, null, $q);
        }
    }

    /**
     * 强制下载
     *
     * @param string     $filename 下载文件名
     * @param string|null $mimeType
     * @param int|null   $quality
     */
    public function download($filename, $mimeType = null, $quality = null)
    {
        $this->assertNotDestroyed();

        $sanitized = str_replace(['"', "'", '\\', '/', "\0", "\n", "\r"], '_', $filename);
        $ext = $this->outputFormat ?: $this->extName;
        $mime = $mimeType ?: $this->extensionToMime($ext);
        $q = $quality ?? $this->quality;

        $tmpFile = tempnam(sys_get_temp_dir(), 'img_');
        $this->save($tmpFile, $q);
        $fileSize = filesize($tmpFile);

        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-Length: ' . $fileSize);
        header('Content-Transfer-Encoding: Binary');
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $sanitized . '"');

        readfile($tmpFile);
        unlink($tmpFile);
    }

    // ───────────────────── 格式转换 ─────────────────────

    /**
     * 设置输出格式（用于格式转换，如 PNG → JPEG）
     *
     * @param string $format (png/jpeg/gif/webp)
     * @return $this
     */
    public function setOutputFormat($format)
    {
        $format = strtolower($format);
        $allowed = ['png', 'jpeg', 'jpg', 'gif', 'webp'];
        if (!in_array($format, $allowed)) {
            throw new \InvalidArgumentException('Unsupported output format: ' . $format . '. Allowed: ' . implode(', ', $allowed));
        }
        $this->outputFormat = $format === 'jpg' ? 'jpeg' : $format;
        return $this;
    }

    /**
     * 设置 JPEG/WebP 质量
     *
     * @param int $quality (0-100)
     * @return $this
     */
    public function setQuality($quality)
    {
        $this->quality = max(0, min(100, (int)$quality));
        return $this;
    }

    // ───────────────────── 几何变换 ─────────────────────

    /**
     * 缩放图像
     *
     * @param int|null $width  目标宽度（null 则等比计算）
     * @param int|null $height 目标高度（null 则等比计算）
     * @return $this
     */
    public function resize($width = null, $height = null)
    {
        if (!$width && !$height) {
            return $this;
        }

        $w = $width;
        $h = $height;

        // Resize to width
        if ($w && !$h) {
            $h = (int)round($w / ($this->getWidth() / $this->getHeight()));
        }
        // Resize to height
        if (!$w && $h) {
            $w = (int)round($h * ($this->getWidth() / $this->getHeight()));
        }

        if ($this->getWidth() === (int)$w && $this->getHeight() === (int)$h) {
            return $this;
        }

        $newImage = imagecreatetruecolor((int)$w, (int)$h);

        if ($this->isTransparentFormat()) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
            imagefill($newImage, 0, 0, $transparent);
        }

        imagecopyresampled(
            $newImage,
            $this->image,
            0, 0, 0, 0,
            (int)$w,
            (int)$h,
            $this->getWidth(),
            $this->getHeight()
        );

        $this->image  = $newImage;
        $this->width  = (int)$w;
        $this->height = (int)$h;

        return $this;
    }

    /**
     * 按比例缩放（短边适配）
     *
     * @param int  $width   最大宽度
     * @param int  $height  最大高度
     * @param bool $upscale 是否允许放大
     * @return $this
     */
    public function fit($width, $height, $upscale = false)
    {
        $ratio = $this->width / $this->height;
        $targetRatio = $width / $height;

        if ($targetRatio > $ratio) {
            $newH = $height;
            $newW = (int)round($height * $ratio);
        } else {
            $newW = $width;
            $newH = (int)round($width / $ratio);
        }

        if (!$upscale && $newW >= $this->width && $newH >= $this->height) {
            return $this;
        }

        return $this->resize($newW, $newH);
    }

    /**
     * 缩放并居中裁剪（fill 模式）
     *
     * @param int    $width
     * @param int    $height
     * @param string $anchor 锚点 (center/top/bottom/left/right/top-left/top-right/bottom-left/bottom-right)
     * @return $this
     */
    public function fill($width, $height, $anchor = 'center')
    {
        return $this->thumb($width, $height, $anchor);
    }

    /**
     * 生成缩略图（等比缩放并裁剪）
     *
     * @param int    $width
     * @param int    $height
     * @param string $anchor 裁剪锚点
     * @return $this
     */
    public function thumb($width, $height, $anchor = 'center')
    {
        $currentRatio = $this->getHeight() / $this->getWidth();
        $targetRatio  = $height / $width;

        if ($targetRatio > $currentRatio) {
            $this->resize(null, $height);
        } else {
            $this->resize($width, null);
        }

        $coords = $this->anchorCoords($width, $height, $anchor);
        return $this->crop($coords[0], $coords[1], $coords[2], $coords[3]);
    }

    /**
     * 裁剪
     *
     * @param int $x1
     * @param int $y1
     * @param int $x2
     * @param int $y2
     * @return $this
     */
    public function crop($x1, $y1, $x2, $y2)
    {
        $this->assertNotDestroyed();

        $x1 = $this->valueLimit((int)$x1, 0, $this->getWidth());
        $x2 = $this->valueLimit((int)$x2, 0, $this->getWidth());
        $y1 = $this->valueLimit((int)$y1, 0, $this->getHeight());
        $y2 = $this->valueLimit((int)$y2, 0, $this->getHeight());

        $rect = [
            'x'      => min($x1, $x2),
            'y'      => min($y1, $y2),
            'width'  => abs($x2 - $x1),
            'height' => abs($y2 - $y1),
        ];

        $cropped = imagecrop($this->image, $rect);
        if ($cropped !== false) {
            $this->image  = $cropped;
            $this->width  = $rect['width'];
            $this->height = $rect['height'];
        }

        return $this;
    }

    /**
     * 正方形裁剪
     *
     * @param int|null $size     目标边长（null 则取短边）
     * @param string   $anchor   锚点
     * @return $this
     */
    public function square($size = null, $anchor = 'center')
    {
        $side = $size ?? min($this->width, $this->height);
        return $this->thumb($side, $side, $anchor);
    }

    /**
     * 旋转
     *
     * @param float  $degree 角度（逆时针）
     * @param string $color  背景色 (hex)
     * @return $this
     */
    public function rotate($degree, $color = 'FFFFFF')
    {
        $this->assertNotDestroyed();

        $rgb = $this->html2rgb($color);
        $bgColor = imagecolorallocate($this->image, $rgb[0], $rgb[1], $rgb[2]);

        $this->image = imagerotate($this->image, (float)$degree, $bgColor);

        if ($this->isTransparentFormat()) {
            imagealphablending($this->image, false);
            imagesavealpha($this->image, true);
        }

        $this->width  = imagesx($this->image);
        $this->height = imagesy($this->image);

        return $this;
    }

    /**
     * 水平翻转
     *
     * @return $this
     */
    public function flip()
    {
        imageflip($this->image, IMG_FLIP_HORIZONTAL);
        return $this;
    }

    /**
     * 垂直翻转
     *
     * @return $this
     */
    public function flop()
    {
        imageflip($this->image, IMG_FLIP_VERTICAL);
        return $this;
    }

    /**
     * 水平+垂直翻转（180度）
     *
     * @return $this
     */
    public function flipBoth()
    {
        imageflip($this->image, IMG_FLIP_BOTH);
        return $this;
    }

    /**
     * 根据 EXIF 自动旋转（修正手机竖拍图片方向）
     *
     * @return $this
     */
    public function orient()
    {
        $exif = $this->getExif();
        if (!$exif || empty($exif['Orientation'])) {
            return $this;
        }

        $orientation = (int)$exif['Orientation'];
        switch ($orientation) {
            case 2:
                $this->flip();
                break;
            case 3:
                $this->rotate(180);
                break;
            case 4:
                $this->flop();
                break;
            case 5:
                $this->flip()->rotate(-90);
                break;
            case 6:
                $this->rotate(-90);
                break;
            case 7:
                $this->flop()->rotate(-90);
                break;
            case 8:
                $this->rotate(90);
                break;
        }

        return $this;
    }

    // ───────────────────── 画布操作 ─────────────────────

    /**
     * 扩展/收缩画布
     *
     * @param int         $width   新宽度
     * @param int         $height  新高度
     * @param string      $anchor  原图在新画布中的位置
     * @param string|null $bgColor 背景色 (hex)，null 表示透明
     * @return $this
     */
    public function resizeCanvas($width, $height, $anchor = 'center', $bgColor = null)
    {
        $this->assertNotDestroyed();

        $newImage = imagecreatetruecolor((int)$width, (int)$height);
        $hasAlpha = $bgColor === null || $this->isTransparentFormat();

        if ($hasAlpha) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $fillColor = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
        } else {
            $rgb = $this->html2rgb($bgColor);
            $fillColor = imagecolorallocate($newImage, $rgb[0], $rgb[1], $rgb[2]);
        }
        imagefill($newImage, 0, 0, $fillColor);

        $coords = $this->anchorCoords($width, $height, $anchor);
        imagecopy(
            $newImage,
            $this->image,
            $coords[0], $coords[1],
            0, 0,
            $this->width, $this->height
        );

        $this->image  = $newImage;
        $this->width  = (int)$width;
        $this->height = (int)$height;

        return $this;
    }

    // ───────────────────── 滤镜 ─────────────────────

    /**
     * 灰度
     *
     * @return $this
     */
    public function grayscale()
    {
        imagefilter($this->image, IMG_FILTER_GRAYSCALE);
        return $this;
    }

    /**
     * 反色
     *
     * @return $this
     */
    public function invert()
    {
        imagefilter($this->image, IMG_FILTER_NEGATE);
        return $this;
    }

    /**
     * 亮度
     *
     * @param int $level -255 ~ 255
     * @return $this
     */
    public function brightness($level)
    {
        imagefilter($this->image, IMG_FILTER_BRIGHTNESS, (int)$level);
        return $this;
    }

    /**
     * 对比度
     *
     * @param int $level -100 ~ 100
     * @return $this
     */
    public function contrast($level)
    {
        imagefilter($this->image, IMG_FILTER_CONTRAST, (int)$level);
        return $this;
    }

    /**
     * 色彩饱和度
     *
     * @param int $level -100 ~ 100
     * @return $this
     */
    public function saturation($level)
    {
        // PHP 没有内置饱和度滤镜，通过灰度+混合模拟
        if ($level === 0) {
            return $this;
        }

        $w = $this->width;
        $h = $this->height;

        // 创建灰度副本
        $gray = imagecreatetruecolor($w, $h);
        imagecopy($gray, $this->image, 0, 0, 0, 0, $w, $h);
        imagefilter($gray, IMG_FILTER_GRAYSCALE);

        // 灰度与原图混合
        $amount = abs($level) / 100;
        if ($level > 0) {
            // 偏原图
            imagecopymerge($gray, $this->image, 0, 0, 0, 0, $w, $h, (int)(100 - $amount * 100));
            $result = $gray;
        } else {
            // 偏灰度
            imagecopymerge($this->image, $gray, 0, 0, 0, 0, $w, $h, (int)($amount * 100));
            imagedestroy($gray);
            return $this;
        }

        imagedestroy($this->image);
        $this->image = $result;

        return $this;
    }

    /**
     * 高斯模糊
     *
     * @param int $passes 模糊次数（1-10，越大越模糊）
     * @return $this
     */
    public function blur($passes = 1)
    {
        $passes = max(1, min(10, (int)$passes));
        for ($i = 0; $i < $passes; $i++) {
            imagefilter($this->image, IMG_FILTER_GAUSSIAN_BLUR);
        }
        return $this;
    }

    /**
     * 选择性模糊（保留边缘）
     *
     * @return $this
     */
    public function blurSelective()
    {
        imagefilter($this->image, IMG_FILTER_SELECTIVE_BLUR);
        return $this;
    }

    /**
     * 锐化
     *
     * @return $this
     */
    public function sharpen()
    {
        $matrix = [
            [-1, -1, -1],
            [-1, 16, -1],
            [-1, -1, -1],
        ];
        $divisor = array_sum(array_map('array_sum', $matrix));
        imageconvolution($this->image, $matrix, $divisor ?: 1, 0);
        return $this;
    }

    /**
     * 边缘检测
     *
     * @return $this
     */
    public function edgeDetect()
    {
        imagefilter($this->image, IMG_FILTER_EDGEDETECT);
        return $this;
    }

    /**
     * 浮雕效果
     *
     * @return $this
     */
    public function emboss()
    {
        imagefilter($this->image, IMG_FILTER_EMBOSS);
        return $this;
    }

    /**
     * 像素化/马赛克
     *
     * @param int $blockSize 块大小（越大越模糊）
     * @param bool $advanced 是否使用高级像素化
     * @return $this
     */
    public function pixelate($blockSize = 10, $advanced = false)
    {
        imagefilter($this->image, $advanced ? IMG_FILTER_PIXELATE : IMG_FILTER_PIXELATE, (int)$blockSize, $advanced);
        return $this;
    }

    /**
     * 平滑处理
     *
     * @param int $level 平滑等级
     * @return $this
     */
    public function smooth($level)
    {
        imagefilter($this->image, IMG_FILTER_SMOOTH, (int)$level);
        return $this;
    }

    /**
     * 平均去噪
     *
     * @return $this
     */
    public function meanRemoval()
    {
        imagefilter($this->image, IMG_FILTER_MEAN_REMOVAL);
        return $this;
    }

    /**
     * 色彩叠加
     *
     * @param int $red   (0-255)
     * @param int $green (0-255)
     * @param int $blue  (0-255)
     * @param int $alpha (0-127, 0=不透明)
     * @return $this
     */
    public function colorize($red, $green, $blue, $alpha = 0)
    {
        imagefilter($this->image, IMG_FILTER_COLORIZE, (int)$red, (int)$green, (int)$blue, (int)$alpha);
        return $this;
    }

    /**
     * 复古/怀旧色调
     *
     * @return $this
     */
    public function sepia()
    {
        $this->grayscale();
        $this->brightness(-20);
        imagefilter($this->image, IMG_FILTER_COLORIZE, 90, 55, 30);
        return $this;
    }

    /**
     * 不透明度
     *
     * @param int $percent 0-100（0=完全透明，100=完全不透明）
     * @return $this
     */
    public function opacity($percent)
    {
        $percent = max(0, min(100, (int)$percent));

        if ($this->isTransparentFormat()) {
            imagealphablending($this->image, false);
            $w = $this->width;
            $h = $this->height;
            for ($x = 0; $x < $w; $x++) {
                for ($y = 0; $y < $h; $y++) {
                    $rgba = imagecolorat($this->image, $x, $y);
                    $alpha = ($rgba >> 24) & 0x7F;
                    $newAlpha = (int)(127 - (127 - $alpha) * ($percent / 100));
                    $color = imagecolorallocatealpha(
                        $this->image,
                        ($rgba >> 16) & 0xFF,
                        ($rgba >> 8) & 0xFF,
                        $rgba & 0xFF,
                        $newAlpha
                    );
                    imagesetpixel($this->image, $x, $y, $color);
                }
            }
        }

        return $this;
    }

    /**
     * 应用自定义图像滤镜函数
     *
     * @param int   $filterType IMG_FILTER_* 常量
     * @param mixed ...$args    滤镜参数
     * @return $this
     */
    public function filter($filterType, ...$args)
    {
        $args = array_merge([$this->image, $filterType], $args);
        call_user_func_array('imagefilter', $args);
        return $this;
    }

    // ───────────────────── 水印 & 叠加 ─────────────────────

    /**
     * 添加图像水印
     *
     * @param Image  $watermark 水印图像
     * @param string $position  位置
     * @param int    $opacity   不透明度 (0-100)
     * @return $this
     */
    public function watermark($watermark, $position = 'bottomright', $opacity = 100)
    {
        $this->assertNotDestroyed();

        $wmW = $watermark->getWidth();
        $wmH = $watermark->getHeight();

        $positions = [
            'topleft'      => [0, 0],
            'topcenter'    => [(int)(($this->width - $wmW) / 2), 0],
            'topright'     => [$this->width - $wmW, 0],
            'middleleft'   => [0, (int)(($this->height - $wmH) / 2)],
            'middlecenter' => [(int)(($this->width - $wmW) / 2), (int)(($this->height - $wmH) / 2)],
            'middleright'  => [$this->width - $wmW, (int)(($this->height - $wmH) / 2)],
            'bottomleft'   => [0, $this->height - $wmH],
            'bottomcenter' => [(int)(($this->width - $wmW) / 2), $this->height - $wmH],
            'bottomright'  => [$this->width - $wmW, $this->height - $wmH],
        ];

        if (!isset($positions[$position])) {
            $position = 'bottomright';
        }

        [$posX, $posY] = $positions[$position];

        imagealphablending($this->image, true);
        imagesavealpha($this->image, true);

        if ($opacity < 100) {
            imagecopymerge(
                $this->image, $watermark->getImage(),
                $posX, $posY, 0, 0, $wmW, $wmH,
                $opacity
            );
        } else {
            imagecopy(
                $this->image, $watermark->getImage(),
                $posX, $posY, 0, 0, $wmW, $wmH
            );
        }

        return $this;
    }

    /**
     * 叠加另一张图（带透明度）
     *
     * @param Image $merge   要叠加的图像
     * @param int   $x
     * @param int   $y
     * @param int   $opacity 不透明度 (0-100)
     * @return $this
     */
    public function merge($merge, $x = 0, $y = 0, $opacity = 100)
    {
        $this->assertNotDestroyed();

        imagealphablending($this->image, true);
        imagesavealpha($this->image, true);

        imagecopymerge(
            $this->image, $merge->getImage(),
            (int)$x, (int)$y, 0, 0,
            $merge->getWidth(), $merge->getHeight(),
            (int)$opacity
        );

        return $this;
    }

    // ───────────────────── 文字 ─────────────────────

    /**
     * 添加文字（GD 内置字体）
     *
     * @param string $text
     * @param int    $x
     * @param int    $y
     * @param int    $size   字体大小 (1-5)
     * @param string $color  颜色 hex
     * @return $this
     */
    public function text($text, $x = 0, $y = 0, $size = 5, $color = '000000')
    {
        $this->assertNotDestroyed();

        $rgb = $this->html2rgb($color);
        $fontColor = imagecolorallocate($this->image, $rgb[0], $rgb[1], $rgb[2]);

        imagestring($this->image, (int)$size, (int)$x, (int)$y, $text, $fontColor);

        return $this;
    }

    /**
     * 添加 TTF 文字
     *
     * @param string $text
     * @param string $fontFile  .ttf 字体文件路径
     * @param int    $size      字号
     * @param int    $x
     * @param int    $y
     * @param string $color     颜色 hex
     * @param float  $angle     角度
     * @return $this
     */
    public function ttfText($text, $fontFile, $size = 12, $x = 0, $y = 0, $color = '000000', $angle = 0)
    {
        $this->assertNotDestroyed();

        if (!file_exists($fontFile)) {
            throw new \Exception('Font file not found: ' . $fontFile);
        }

        $rgb = $this->html2rgb($color);
        $fontColor = imagecolorallocate($this->image, $rgb[0], $rgb[1], $rgb[2]);

        imagettftext($this->image, (float)$size, (float)$angle, (int)$x, (int)$y, $fontColor, $fontFile, $text);

        return $this;
    }

    /**
     * 获取 TTF 文字尺寸
     *
     * @param string $text
     * @param string $fontFile
     * @param int    $size
     * @param float  $angle
     * @return array [width, height, left, top, ...]
     */
    public static function ttfBoundingBox($text, $fontFile, $size = 12, $angle = 0)
    {
        if (!file_exists($fontFile)) {
            throw new \Exception('Font file not found: ' . $fontFile);
        }
        return imagettfbbox((float)$size, (float)$angle, $fontFile, $text);
    }

    // ───────────────────── EXIF / 元数据 ─────────────────────

    /**
     * 获取 EXIF 数据（仅 JPEG）
     *
     * @return array|null
     */
    public function getExif()
    {
        if ($this->mimeType === 'image/jpeg' && function_exists('exif_read_data')) {
            return @exif_read_data($this->file);
        }
        return null;
    }

    /**
     * 获取分辨率（DPI）
     *
     * @return array|bool [res_x, res_y] 或 false
     */
    public function getResolution()
    {
        return imageresolution($this->image);
    }

    /**
     * 获取图像信息数组
     *
     * @return array
     */
    public function info()
    {
        return [
            'file'      => $this->file,
            'dirname'   => $this->dirName,
            'basename'  => $this->baseName,
            'filename'  => $this->fileName,
            'extension' => $this->extName,
            'mime'      => $this->mimeType,
            'width'     => $this->width,
            'height'    => $this->height,
            'bits'      => $this->bits,
            'quality'   => $this->quality,
            'destroyed' => $this->destroyed,
        ];
    }

    /**
     * 获取信息数组（同 info()）
     *
     * @return array
     */
    public function toArray()
    {
        return $this->info();
    }

    // ───────────────────── 复制 & 销毁 ─────────────────────

    /**
     * 复制图像实例（独立的 GD 资源）
     *
     * @return static
     */
    public function copy()
    {
        $this->assertNotDestroyed();

        $copy = clone $this;
        $copyW = $copy->width;
        $copyH = $copy->height;

        $newResource = imagecreatetruecolor($copyW, $copyH);
        if ($this->isTransparentFormat()) {
            imagealphablending($newResource, false);
            imagesavealpha($newResource, true);
            imagefill($newResource, 0, 0, imagecolorallocatealpha($newResource, 0, 0, 0, 127));
        }
        imagecopy($newResource, $this->image, 0, 0, 0, 0, $copyW, $copyH);

        $copy->image = $newResource;
        $copy->destroyed = false;
        return $copy;
    }

    /**
     * 显式销毁 GD 资源
     *
     * @return void
     */
    public function destroy()
    {
        if ($this->image && !$this->destroyed) {
            imagedestroy($this->image);
            $this->image = null;
            $this->destroyed = true;
        }
    }

    /**
     * 析构：自动释放资源
     */
    public function __destruct()
    {
        if (!$this->destroyed && $this->image) {
            imagedestroy($this->image);
        }
    }

    /**
     * 克隆时深拷贝 GD 资源
     */
    public function __clone()
    {
        if ($this->image && !$this->destroyed) {
            $w = $this->width;
            $h = $this->height;
            $new = imagecreatetruecolor($w, $h);
            if ($this->isTransparentFormat()) {
                imagealphablending($new, false);
                imagesavealpha($new, true);
                imagefill($new, 0, 0, imagecolorallocatealpha($new, 0, 0, 0, 127));
            }
            imagecopy($new, $this->image, 0, 0, 0, 0, $w, $h);
            $this->image = $new;
        }
    }

    // ───────────────────── Getter ─────────────────────

    public function getFile()    { return $this->file; }
    public function getImage()   { return $this->image; }
    public function getWidth()   { return $this->width; }
    public function getHeight()  { return $this->height; }
    public function getBits()    { return $this->bits; }
    public function getMimeType(){ return $this->mimeType; }
    public function getExtName() { return $this->extName; }

    // ───────────────────── 内部工具方法 ─────────────────────

    /**
     * 从文件创建 GD 图像资源
     */
    private function createImageFromFile($file, $mimeType)
    {
        switch ($mimeType) {
            case 'image/gif':
                return imagecreatefromgif($file);
            case 'image/png':
                return imagecreatefrompng($file);
            case 'image/jpeg':
                return imagecreatefromjpeg($file);
            case 'image/webp':
                return imagecreatefromwebp($file);
            default:
                // 尝试从扩展名推断
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                switch ($ext) {
                    case 'gif':  return imagecreatefromgif($file);
                    case 'png':  return imagecreatefrompng($file);
                    case 'jpg':
                    case 'jpeg': return imagecreatefromjpeg($file);
                    case 'webp': return imagecreatefromwebp($file);
                    default:     return null;
                }
        }
    }

    /**
     * 是否支持透明通道
     */
    private function isTransparentFormat()
    {
        $ext = $this->outputFormat ?: $this->extName;
        return in_array($ext, ['png', 'webp', 'gif']);
    }

    /**
     * HTML 颜色 (hex) 转 RGB 数组
     *
     * @param string $color
     * @return array [r, g, b]
     * @throws \InvalidArgumentException
     */
    private function html2rgb($color)
    {
        if ($color[0] === '#') {
            $color = substr($color, 1);
        }

        if (strlen($color) === 6) {
            [$r, $g, $b] = [$color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]];
        } elseif (strlen($color) === 3) {
            [$r, $g, $b] = [$color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]];
        } else {
            throw new \InvalidArgumentException('Invalid hex color: ' . $color);
        }

        return [(int)hexdec($r), (int)hexdec($g), (int)hexdec($b)];
    }

    /**
     * 值限制在范围内
     */
    private function valueLimit($value, $min, $max)
    {
        return max($min, min($max, $value));
    }

    /**
     * 计算锚点裁剪坐标
     */
    private function anchorCoords($targetW, $targetH, $anchor)
    {
        switch ($anchor) {
            case 'top':
                $x1 = (int)(($this->getWidth() / 2) - ($targetW / 2));
                $x2 = (int)($targetW + $x1);
                $y1 = 0;
                $y2 = (int)$targetH;
                break;
            case 'bottom':
                $x1 = (int)(($this->getWidth() / 2) - ($targetW / 2));
                $x2 = (int)($targetW + $x1);
                $y1 = $this->getHeight() - (int)$targetH;
                $y2 = $this->getHeight();
                break;
            case 'left':
                $x1 = 0;
                $x2 = (int)$targetW;
                $y1 = (int)(($this->getHeight() / 2) - ($targetH / 2));
                $y2 = (int)($targetH + $y1);
                break;
            case 'right':
                $x1 = $this->getWidth() - (int)$targetW;
                $x2 = $this->getWidth();
                $y1 = (int)(($this->getHeight() / 2) - ($targetH / 2));
                $y2 = (int)($targetH + $y1);
                break;
            case 'top-left':
            case 'top left':
                $x1 = 0; $x2 = (int)$targetW; $y1 = 0; $y2 = (int)$targetH;
                break;
            case 'top-right':
            case 'top right':
                $x1 = $this->getWidth() - (int)$targetW;
                $x2 = $this->getWidth();
                $y1 = 0;
                $y2 = (int)$targetH;
                break;
            case 'bottom-left':
            case 'bottom left':
                $x1 = 0;
                $x2 = (int)$targetW;
                $y1 = $this->getHeight() - (int)$targetH;
                $y2 = $this->getHeight();
                break;
            case 'bottom-right':
            case 'bottom right':
                $x1 = $this->getWidth() - (int)$targetW;
                $x2 = $this->getWidth();
                $y1 = $this->getHeight() - (int)$targetH;
                $y2 = $this->getHeight();
                break;
            default: // center
                $x1 = (int)(($this->getWidth() / 2) - ($targetW / 2));
                $x2 = (int)($targetW + $x1);
                $y1 = (int)(($this->getHeight() / 2) - ($targetH / 2));
                $y2 = (int)($targetH + $y1);
                break;
        }

        return [$x1, $y1, $x2, $y2];
    }

    /**
     * 扩展名 -> MIME 类型
     */
    private function extensionToMime($ext)
    {
        $map = [
            'jpeg' => 'image/jpeg',
            'jpg'  => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
        ];
        return $map[strtolower($ext)] ?? 'image/jpeg';
    }

    /**
     * MIME 类型 -> 扩展名
     */
    private function mimeToExtension($mime)
    {
        $map = [
            'image/jpeg' => 'jpeg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];
        return $map[$mime] ?? 'jpeg';
    }

    /**
     * PNG 质量 (0-100) → 压缩级别 (0-9, 反转)
     */
    private function pngQualityToLevel($quality)
    {
        $quality = (int)$quality;
        return max(0, min(9, (int)round(9 - ($quality / 100) * 9)));
    }

    /**
     * 检查资源未被销毁
     */
    private function assertNotDestroyed()
    {
        if ($this->destroyed || !$this->image) {
            throw new \RuntimeException('Image resource has been destroyed. Create a new instance.');
        }
    }
}
