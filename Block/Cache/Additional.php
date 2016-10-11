<?php
/**
 * Additional
 *
 * @copyright Copyright (c) 2016 Staempfli AG
 * @author    juan.alonso@staempfli.com
 */

namespace Staempfli\ImageResizer\Block\Cache;

use Magento\Backend\Block\Cache\Additional as MagentoCacheAdditional;

class Additional extends MagentoCacheAdditional
{
    /**
     * Clean resized images url
     *
     * @return string
     */
    public function getCleanResizedImagesUrl()
    {
        return $this->getUrl('staempfli_imageresizer/cache/cleanResizedImages');
    }
}
