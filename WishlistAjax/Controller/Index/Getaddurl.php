<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Nouvolution\WishlistAjax\Controller\Index;

class Getaddurl extends \Magento\Framework\App\Action\Action
{
	 /**
     * @var WishlistHelper
     */
    protected $_helper;

    /**
     * @var $_productloader
     */
    protected $_productloader;  

    /**
     * @var $assetRepo
     */
    protected $assetRepo; 

     /**
     * @var $request
     */
    protected $request;

	 /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Nouvolution\WishlistAjax\Helper\Data $helper
     * @param \Magento\Catalog\Model\ProductFactory $_productloader
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Nouvolution\WishlistAjax\Helper\Data $helper,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\App\RequestInterface $request
    ) {
    	$this->_helper = $helper;
        $this->_productloader = $_productloader;
         $this->assetRepo = $assetRepo;
         $this->request = $request;

        parent::__construct($context);
    }

    /**
     * Show Contact Us page
     *
     * @return void
     */
    public function execute()
    {	
    	$data = $this->getRequest()->getPostValue();
    	$htmlLink='';
       // create wishlist remove item link
            $wishlistRemoveLink='';

            $productId=$data['wProductId'];
                if(isset($productId) && $productId>0){
	                $_product=$this->_productloader->create()->load($productId);

	                	$params = array('_secure' => $this->request->isSecure());
    					$imageUrl= $this->assetRepo->getUrlWithParams('Nouvolution_WishlistAjax::images/icons/icon-wishlist.png',$params);
	                	$wishlistAddLink=$this->_objectManager->create('Magento\Wishlist\Helper\Data')->getAddParams($_product);

	                	$htmlLink='<a href="#"  data-post='.$wishlistAddLink.' class="heart_icon action towishlist wishlist-blank">
                                            <span class="wish-list wishlist-blank-gallery"><img width="16" src="'.$imageUrl.'" alt="Wish list"></span>
                                        </a>';
            }
       echo $htmlLink;exit;
    }
}
