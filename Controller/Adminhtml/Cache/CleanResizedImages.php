<?php
/**
 * CleanResizedImages
 *
 * @copyright Copyright (c) 2016 Staempfli AG
 * @author    juan.alonso@staempfli.com
 */

namespace Staempfli\ImageResizer\Controller\Adminhtml\Cache;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Controller\Adminhtml\Cache as MagentoAdminCache;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Staempfli\ImageResizer\Model\Cache as ResizerCache;

class CleanResizedImages extends MagentoAdminCache
{
    /**
     * @var ResizerCache
     */
    protected $resizerCache;

    /**
     * CleanResizedImages constructor.
     * @param ResizerCache $resizerCache
     * @param Context $context
     * @param TypeListInterface $cacheTypeList
     * @param StateInterface $cacheState
     * @param Pool $cacheFrontendPool
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        ResizerCache $resizerCache,
        Context $context,
        TypeListInterface $cacheTypeList,
        StateInterface $cacheState,
        Pool $cacheFrontendPool,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context, $cacheTypeList, $cacheState, $cacheFrontendPool, $resultPageFactory);
        $this->resizerCache = $resizerCache;
    }

    /**
     * Clean JS/css files cache
     *
     * @return Redirect
     */
    public function execute()
    {
        try {
            $this->resizerCache->clearResizedImagesCache();
            $this->_eventManager->dispatch('staempfli_imageresizer_clean_images_cache_after');
            $this->messageManager->addSuccessMessage(__('The resized images cache was cleaned.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('An error occurred while clearing the resized images cache.'));
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('adminhtml/cache');
    }
}
