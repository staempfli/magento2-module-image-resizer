<?php
/**
 * Cache
 *
 * @copyright Copyright (c) 2016 Staempfli AG
 * @author    juan.alonso@staempfli.com
 */

namespace Staempfli\ImageResizer\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;

class Cache extends AbstractHelper
{
    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * Cache constructor.
     * @param Filesystem $filesystem
     * @param Context $context
     */
    public function __construct(Filesystem $filesystem, Context $context)
    {
        parent::__construct($context);
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * Delete Image resizer cache dir
     */
    public function clearResizedImagesCache()
    {
        $this->mediaDirectory->delete(Resizer::IMAGE_RESIZER_CACHE_DIR);
    }
}
