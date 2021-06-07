<?php
namespace Nouvolution\WishlistAjax\Helper;


class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_wishlistFactory;
		    /**
		* @var \Magento\Customer\Model\Session
		*/
		protected $_customerSessionFactory;

		protected $_registry;

		private $wishlist;

		 /**
	     * @var array
	     */
	    protected $_wishlistOptions;

		public function __construct(
			\Magento\Framework\App\Helper\Context $context,
		    \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
		     \Magento\Customer\Model\SessionFactory $customerSessionFactory,
		     \Magento\Framework\Registry $registry,
		     \Magento\Wishlist\Model\Wishlist $wishlist
		){
			parent::__construct($context);
			$this->_wishlistOptions = $this->scopeConfig->getValue('nouvolution_wishlistajax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		    $this->_wishlistFactory = $wishlistFactory;
		    $this->_customerSessionFactory = $customerSessionFactory;
		     $this->_registry = $registry;
		     $this->wishlist = $wishlist;
		}

		public function getWishlistProductId($itemId)
		{
			$productId='';
			$customerId=$this->getCustomerId();

		    $wishlist = $this->_wishlistFactory->create();
		    $wishlist_collection = $wishlist->loadByCustomerId($customerId, true)->getItemCollection();
					if(count($wishlist_collection)>0){		    
	    				foreach ($wishlist_collection as $_wishlist_item){
	                                if($itemId == $_wishlist_item->getId()){
	                                	$productId= $_wishlist_item->getProduct()->getId();
	                                	break;
	                                }
	                    }
                  }
            return $productId;
		}

		
		public function getWishlistListItems($_product)
		{
			$wishlistItem='';
			$customerId=$this->getCustomerId();

		    $wishlist = $this->_wishlistFactory->create();
		    $wishlist_collection = $wishlist->loadByCustomerId($customerId, true)->getItemCollection();

		    
    				foreach ($wishlist_collection as $_wishlist_item){
                                if($_product->getId() == $_wishlist_item->getProduct()->getId()){
                                	$wishlistItem= $_wishlist_item;
                                	break;
                                }
                    }
            return $wishlistItem;
		}

		/*
		get customer logged in 
		*/

		public function getCustomerId(){
			$customer = $this->_customerSessionFactory->create();
            return $customer->getCustomer()->getId();
		}

		/*
			get current product id
		*/

		public function getCurrentProduct()
    {        
        return $this->_registry->registry('current_product');
    } 

    public function isAjaxWishlistEnabled() {
        return trim($this->_wishlistOptions['general']['enable_ajaxwishlist']);
    }

}
