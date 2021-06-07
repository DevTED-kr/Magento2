<?php

namespace Nouvolution\WishlistAjax\Plugin;

use Magento\Wishlist\Controller\Index\Remove as WishlistRemoveController;
use Nouvolution\WishlistAjax\Helper\Data as WishlistHelper;

class WishlistRemoveAction
{

    /**
     * @var WishlistHelper
     */
    protected $_helper;

     public $_storeManager;

    /**
     * CustomerSharingBlock constructor.
     * @param WishlistHelper $helper
     */
    public function __construct(
        WishlistHelper $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->_helper = $helper;
        $this->_storeManager=$storeManager;
    }

    /**
     * @param WishlistRemoveController $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundExecute(
        WishlistRemoveController $subject,
        \Closure $proceed
    ){
        $result = $proceed();
        $productId=$this->_helper->getWishlistProductId($subject->getRequest()->getParam('item'));

        if ($subject->getRequest()->getParam('ajax') == 1 && $this->_helper->isAjaxWishlistEnabled()) {
            $wishAddUrl= $this->_storeManager->getStore()->getUrl('wishlistajax/index/getaddurl');
            return $subject->getResponse()->representJson(json_encode([
                'result' => true,
                'wish_add_url' => $wishAddUrl,
                'w_productId' => $productId
            ]));

        }
        return $result;
    }
}