<?php
/**
 * Block
 *
 * @copyright Copyright (c) 2016 Staempfli AG
 * @author    juan.alonso@staempfli.com
 */

namespace Staempfli\ImageResizer\Plugin\View\Layout\Generator;

use Magento\Framework\View\Layout\Generator\Block as MagentoGeneratorBock;
use Staempfli\ImageResizer\Model\Resizer;

class Block
{
    /**
     * @var Resizer
     */
    protected $resizer;

    /**
     * Block constructor.
     * @param Resizer $resizer
     */
    public function __construct(Resizer $resizer)
    {
        $this->resizer = $resizer;
    }

    /**
     * Add image resizer object to all template blocks
     *
     * @param MagentoGeneratorBock $subject
     * @param $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreateBlock(MagentoGeneratorBock $subject, $result) //@codingStandardsIgnoreLine
    {
        if (is_a($result, 'Magento\Framework\View\Element\Template')) {
            $result->addData(['image_resizer' => $this->resizer]);
        }
        return $result;
    }
}
