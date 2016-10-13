<?php
/**
 * Block
 *
 * @copyright Copyright (c) 2016 Staempfli AG
 * @author    juan.alonso@staempfli.com
 */

namespace Staempfli\ImageResizer\Plugin\View\Layout\Generator;

use Magento\Framework\View\Layout\Generator\Block as MagentoGeneratorBock;
use Staempfli\ImageResizer\Helper\Resizer;

class Block
{
    /**
     * @var Resizer
     */
    protected $resizerHelper;

    /**
     * Block constructor.
     * @param Resizer $resizerHelper
     */
    public function __construct(Resizer $resizerHelper)
    {
        $this->resizerHelper = $resizerHelper;
    }

    /**
     * Add image resizer helper object to all template blocks
     *
     * @param MagentoGeneratorBock $subject
     * @param $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreateBlock(MagentoGeneratorBock $subject, $result) //@codingStandardsIgnoreLine
    {
        if (is_a($result, 'Magento\Framework\View\Element\Template')) {
            $result->addData(['image_resizer_helper' => $this->resizerHelper]);
        }
        return $result;
    }
}
