<?php

namespace Nouvolution\WishlistAjax\Plugin;

use Magento\Wishlist\Controller\Index\Add as WishlistAddController;
use Nouvolution\WishlistAjax\Helper\Data as WishlistHelper;

class WishlistAddAction
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
     * @param WishlistAddController $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundExecute(
        WishlistAddController $subject,
        \Closure $proceed
    ){
        $result = $proceed();
        
        if ($subject->getRequest()->getParam('ajax') == 1 && $this->_helper->isAjaxWishlistEnabled()) {
            $wishRemoveUrl= $this->_storeManager->getStore()->getUrl('wishlistajax/index/getremoveurl');

            return $subject->getResponse()->representJson(json_encode([
                'result' => true,
                'wish_remove_url' => $wishRemoveUrl
            ]));

        }
        return $result;
    }
}