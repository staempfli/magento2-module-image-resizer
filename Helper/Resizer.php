<?php
/**
 * Resizer
 *
 * @copyright Copyright (c) 2016 Staempfli AG
 * @author    juan.alonso@staempfli.com
 */

namespace Staempfli\ImageResizer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory as imageAdapterFactory;

class Resizer extends AbstractHelper
{
    /**
     * @var Filesystem
     */
    protected $filesystem;
    /**
     * @var imageAdapterFactory
     */
    protected $imageAdapterFactory;
    /**
     * @var $constraintOnly
     *
     * Guarantee, that image picture will not be bigger, than it was. It is false by default..
     */
    protected $constraintOnly = true;
    /**
     * @var bool $keepAspectRadio
     *
     * Guarantee, that image picture width/height will not be distorted. It is true by default.
     */
    protected $keepAspectRadio = true;
    /**
     * @var bool $keepTransparency
     *
     * Guarantee, that image will not lose transparency if any. It is true by default.
     */
    protected $keepTransparency = true;
    /**
     * @var bool $keepFrame
     *
     * Guarantee, that image will have dimensions, set in $width/$height. Not applicable, if keepAspectRatio(false).
     */
    protected $keepFrame = false;
    /**
     * @var string
     */
    protected $filePath;
    /**
     * @var string
     */
    protected $filename;
    /**
     * @var int
     */
    protected $width;
    /**
     * @var int
     */
    protected $height;

    /**
     * Resizer constructor.
     * @param Filesystem $filesystem
     * @param ImageAdapterFactory $imageAdapterFactory
     * @param Context $context
     */
    public function __construct(
        Filesystem $filesystem,
        imageAdapterFactory $imageAdapterFactory,
        Context $context)
    {
        parent::__construct($context);
        $this->filesystem = $filesystem;
        $this->imageAdapterFactory = $imageAdapterFactory;
    }

    /**
     * @param mixed $constraintOnly
     */
    public function setConstraintOnly($constraintOnly)
    {
        $this->constraintOnly = $constraintOnly;
    }

    /**
     * @param boolean $keepAspectRadio
     */
    public function setKeepAspectRadio($keepAspectRadio)
    {
        $this->keepAspectRadio = $keepAspectRadio;
    }

    /**
     * @param boolean $keepTransparency
     */
    public function setKeepTransparency($keepTransparency)
    {
        $this->keepTransparency = $keepTransparency;
    }

    /**
     * @param boolean $keepFrame
     */
    public function setKeepFrame($keepFrame)
    {
        $this->keepFrame = $keepFrame;
    }

    // ask for image url, and get image path and filename from there. Return original url if no success. Log error.
    public function getResizedImageUrl($filePath, $filename, $width, $height)
    {
        $this->filePath = $filePath;
        $this->filename = $filename;
        $this->width = $width;
        $this->height = $height;

        // Check if resized image already exists in cache
        $resizedImageUrl = $this->getCachedResizedImageUrl();
        if (!$resizedImageUrl) {
            $this->createResizedImageInCache();
            $resizedImageUrl = $this->getCachedResizedImageUrl();
        }

        return $resizedImageUrl?:false;
    }

    protected function getCachedResizedImageUrl()
    {
        // return cache url or false
    }


    protected function createAndSaveResizedImage()
    {
        $imageAdapter = $this->imageAdapterFactory->create();
        $imageAdapter->open($this->filename);
        $imageAdapter->constrainOnly($this->constraintOnly);
        $imageAdapter->keepAspectRatio($this->keepAspectRadio);
        $imageAdapter->keepTransparency($this->keepTransparency);
        $imageAdapter->keepFrame($this->keepFrame);
        $imageAdapter->resize($this->width, $this->height);
        $imageAdapter->save($this->getCachePathForImage());
    }

    protected function getCachePathForImage()
    {
        // return cache path
    }
}
