<?php
/**
 * Resizer
 *
 * @copyright Copyright (c) 2016 Staempfli AG
 * @author    juan.alonso@staempfli.com
 */

namespace Staempfli\ImageResizer\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory as imageAdapterFactory;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Filesystem\Io\File;
use Psr\Log\LoggerInterface;

class Resizer
{
    /**
     * constant IMAGE_RESIZER_DIR
     */
    const IMAGE_RESIZER_DIR = 'staempfli_imageresizer';
    /**
     * constant IMAGE_RESIZER_CACHE_DIR
     */
    const IMAGE_RESIZER_CACHE_DIR = self::IMAGE_RESIZER_DIR . '/' . DirectoryList::CACHE;
    /**
     * @var imageAdapterFactory
     */
    protected $imageAdapterFactory;
    /**
     * @var array
     */
    protected $resizeSettings = [];
    /**
     * @var string
     */
    protected $relativeFilename;
    /**
     * @var int
     */
    protected $width;
    /**
     * @var int
     */
    protected $height;
    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectoryRead;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var array
     *
     * - constrainOnly[true]: Guarantee, that image picture will not be bigger, than it was. It is false by default.
     * - keepAspectRatio[true]: Guarantee, that image picture width/height will not be distorted. It is true by default.
     * - keepTransparency[true]: Guarantee, that image will not lose transparency if any. It is true by default.
     * - keepFrame[false]: Guarantee, that image will have dimensions, set in $width/$height. Not applicable,
     * if keepAspectRatio(false).
     * - backgroundColor[null]: Default white
     */
    protected $defaultSettings = [
        'constrainOnly' => true,
        'keepAspectRatio' => true,
        'keepTransparency' => true,
        'keepFrame' => false,
        'backgroundColor' => null,
        'quality' => 85
    ];
    /**
     * @var array
     */
    protected $subPathSettingsMapping = [
        'constrainOnly' => 'co',
        'keepAspectRatio' => 'ar',
        'keepTransparency' => 'tr',
        'keepFrame' => 'fr',
        'backgroundColor' => 'bc',
    ];
    /**
     * @var File
     */
    protected $fileIo;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Resizer constructor.
     * @param Filesystem $filesystem
     * @param ImageAdapterFactory $imageAdapterFactory
     * @param StoreManagerInterface $storeManager
     * @param File $fileIo
     * @param LoggerInterface $logger
     */
    public function __construct(
        Filesystem $filesystem,
        imageAdapterFactory $imageAdapterFactory,
        StoreManagerInterface $storeManager,
        File $fileIo,
        LoggerInterface $logger
    ) {
        $this->imageAdapterFactory = $imageAdapterFactory;
        $this->mediaDirectoryRead = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $this->storeManager = $storeManager;
        $this->fileIo = $fileIo;
        $this->logger = $logger;
    }

    /**
     * Resized image and return url
     * - Return original image url if no success
     *
     * @param string $imageUrl
     * @param null|int $width
     * @param null|int $height
     * @param array $resizeSettings
     * @return bool|string
     */
    public function resizeAndGetUrl(string $imageUrl, $width, $height, array $resizeSettings = [])
    {
        // Set $resultUrl with $fileUrl to return this one in case the resize fails.
        $resultUrl = $imageUrl;
        $this->initRelativeFilenameFromUrl($imageUrl);
        if (!$this->relativeFilename) {
            return $resultUrl;
        }

        $this->initSize($width, $height);
        $this->initResizeSettings($resizeSettings);

        try {
            // Check if resized image already exists in cache
            $resizedUrl = $this->getResizedImageUrl();
            if (!$resizedUrl) {
                if ($this->resizeAndSaveImage()) {
                    $resizedUrl = $this->getResizedImageUrl();
                }
            }
            if ($resizedUrl) {
                $resultUrl = $resizedUrl;
            }
        } catch (\Exception $e) {
            $this->logger->addError("Staempfli_ImageResizer: could not resize image: \n" . $e->getMessage());
        }

        return $resultUrl;
    }

    /**
     * Prepare and set resize settings for image
     *
     * @param array $resizeSettings
     */
    protected function initResizeSettings(array $resizeSettings)
    {
        // Init resize settings with default
        $this->resizeSettings = $this->defaultSettings;
        // Override resizeSettings only if key matches with existing settings
        foreach ($resizeSettings as $key => $value) {
            if (array_key_exists($key, $this->resizeSettings)) {
                $this->resizeSettings[$key] = $value;
            }
        }
    }

    /**
     * Init relative filename from original image url to resize
     *
     * @param string $imageUrl
     * @return bool|mixed|string
     */
    protected function initRelativeFilenameFromUrl(string $imageUrl)
    {
        $this->relativeFilename = false; // reset filename in case there was another value defined
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $mediaPath = parse_url($mediaUrl, PHP_URL_PATH);
        $imagePath = parse_url($imageUrl, PHP_URL_PATH);

        if (false !== strpos($imagePath, $mediaPath)) {
            if($mediaPath=="/"){
                $this->relativeFilename = trim($imagePath ,'/' );
            }else{
                $this->relativeFilename = str_replace($mediaPath, '', $imagePath);
            }
        }
    }

    /**
     * Init resize dimensions
     *
     * @param null|int $width
     * @param null|int $height
     */
    protected function initSize($width, $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Get sub folder name where the resized image will be saved
     *
     * In order to have unique folders depending on setting, we use the following logic:
     *      - <width>x<height>_[co]_[ar]_[tr]_[fr]_[quality]
     *
     * @return string
     */
    protected function getResizeSubFolderName()
    {
        $subPath = $this->width . "x" . $this->height;
        foreach ($this->resizeSettings as $key => $value) {
            if ($value && isset($this->subPathSettingsMapping[$key])) {
                $subPath .= "_" . $this->subPathSettingsMapping[$key];
            }
        }
        return sprintf('%s_%s',$subPath, $this->resizeSettings['quality']);
    }

    /**
     * Get relative path where the resized image is saved
     *
     * In order to have unique paths, we use the original image path plus the ResizeSubFolderName.
     *
     * @return string
     */
    protected function getRelativePathResizedImage()
    {
        $pathInfo = $this->fileIo->getPathInfo($this->relativeFilename);
        $relativePathParts = [
            self::IMAGE_RESIZER_CACHE_DIR,
            $pathInfo['dirname'],
            $this->getResizeSubFolderName(),
            $pathInfo['basename']
        ];
        return implode('/', $relativePathParts);
    }

    /**
     * Get absolute path from original image
     *
     * @return string
     */
    protected function getAbsolutePathOriginal()
    {
        return $this->mediaDirectoryRead->getAbsolutePath($this->relativeFilename);
    }

    /**
     * Get absolute path from resized image
     *
     * @return string
     */
    protected function getAbsolutePathResized()
    {
        return $this->mediaDirectoryRead->getAbsolutePath($this->getRelativePathResizedImage());
    }

    /**
     * Get url of resized image
     *
     * @return bool|string
     */
    protected function getResizedImageUrl()
    {
        $relativePath = $this->getRelativePathResizedImage();
        if ($this->mediaDirectoryRead->isFile($relativePath)) {
            return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $relativePath;
        }
        return false;
    }

    /**
     * Resize and save new generated image
     *
     * @return bool
     */
    protected function resizeAndSaveImage()
    {
        if (!$this->mediaDirectoryRead->isFile($this->relativeFilename)) {
            return false;
        }

        $imageAdapter = $this->imageAdapterFactory->create();
        $imageAdapter->open($this->getAbsolutePathOriginal());
        $imageAdapter->constrainOnly($this->resizeSettings['constrainOnly']);
        $imageAdapter->keepAspectRatio($this->resizeSettings['keepAspectRatio']);
        $imageAdapter->keepTransparency($this->resizeSettings['keepTransparency']);
        $imageAdapter->keepFrame($this->resizeSettings['keepFrame']);
        $imageAdapter->backgroundColor($this->resizeSettings['backgroundColor']);
        $imageAdapter->quality($this->resizeSettings['quality']);
        $imageAdapter->resize($this->width, $this->height);
        $imageAdapter->save($this->getAbsolutePathResized());
        return true;
    }
}
