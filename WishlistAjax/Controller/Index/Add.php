<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Nouvolution\WishlistAjax\Controller\Index;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;
use Nouvolution\WishlistAjax\Helper\Data as WishlistHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Add extends \Magento\Wishlist\Controller\AbstractIndex
{
    /**
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @param Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     * @param ProductRepositoryInterface $productRepository
     * @param Validator $formKeyValidator
     */
    public function __construct(
        Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ProductRepository $productRepositoryData,
        WishlistHelper $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Validator $formKeyValidator
    ) {
        $this->_customerSession = $customerSession;
        $this->wishlistProvider = $wishlistProvider;
        $this->productRepository = $productRepository;
        $this->formKeyValidator = $formKeyValidator;
        $this->productRepositoryData = $productRepositoryData;
        $this->_helper = $helper;
        $this->_storeManager=$storeManager;
        parent::__construct($context);
    }

    /**
     * Adding new item
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws NotFoundException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setPath('*/');
        }

        $wishlist = $this->wishlistProvider->getWishlist();
        if (!$wishlist) {
            throw new NotFoundException(__('Page not found.'));
        }

        $session = $this->_customerSession;

        $requestParams = $this->getRequest()->getParams();
        $productId = isset($requestParams['product']) ? (int)$requestParams['product'] : null;

        $product_data = $this->productRepository->getById($productId); 
    if($product_data->getData('type_id')==='grouped'){
     if ($this->getRequest()->getParam('ajax') == 1 && $this->_helper->isAjaxWishlistEnabled()) {
        $superGroupVal = $requestParams['super_group1'];
        $super_group_data=json_decode($superGroupVal);
        $productIds=array();
        foreach ($super_group_data as $key => $value) {
            $productIds[$value->product_id]=$value->value;
        }
                    $all_zero = true;
            foreach($productIds as $key => $value){
                if($value != '0')
                {
                    $all_zero = false;
                    break;
                }
            }
            if($all_zero){
            $groupedProduct = $this->productRepositoryData->getById($productId);
 
            $children = $groupedProduct->getTypeInstance(true)->getAssociatedProducts($groupedProduct);
            foreach ($children as $child){
                if($child->isSaleable()){
                    $productIds[$child->getId()]=0;

               } 
            } 
                    foreach ($productIds as $key => $val){
                          $key=$key;
                         break;
                    }
                $productIds[$key]=1;
        }
       
        $requestParams['super_group']=$productIds;
        unset($requestParams['super_group1']);
      }  
    }
        if ($session->getBeforeWishlistRequest()) {
            $requestParams = $session->getBeforeWishlistRequest();
            $session->unsBeforeWishlistRequest();
        }

        if (!$productId) {
            $resultRedirect->setPath('*/');
            return $resultRedirect;
        }

        try {
            $product = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            $product = null;
        }

        if (!$product || !$product->isVisibleInCatalog()) {
            $this->messageManager->addErrorMessage(__('We can\'t specify a product.'));
            $resultRedirect->setPath('*/');
            return $resultRedirect;
        }

        try {
            $buyRequest = new \Magento\Framework\DataObject($requestParams);

            $result = $wishlist->addNewItem($product, $buyRequest);
            if (is_string($result)) {
                throw new \Magento\Framework\Exception\LocalizedException(__($result));
            }
            if ($wishlist->isObjectNew()) {
                $wishlist->save();
            }
            $this->_eventManager->dispatch(
                'wishlist_add_product',
                ['wishlist' => $wishlist, 'product' => $product, 'item' => $result]
            );

            $referer = $session->getBeforeWishlistUrl();
            if ($referer) {
                $session->setBeforeWishlistUrl(null);
            } else {
                $referer = $this->_redirect->getRefererUrl();
            }

            $this->_objectManager->get(\Magento\Wishlist\Helper\Data::class)->calculate();

            $this->messageManager->addComplexSuccessMessage(
                'addProductSuccessMessage',
                [
                    'product_name' => $product->getName(),
                    'referer' => $referer
                ]
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage(
                __('We can\'t add the item to Wish List right now: %1.', $e->getMessage())
            );
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t add the item to Wish List right now.')
            );
        }
        if ($this->getRequest()->getParam('ajax') == 1 && $this->_helper->isAjaxWishlistEnabled()) {
            $wishRemoveUrl= $this->_storeManager->getStore()->getUrl('wishlistajax/index/getremoveurl');

            return $this->getResponse()->representJson(json_encode([
                'result' => true,
                'wish_remove_url' => $wishRemoveUrl
            ]));

        }else{

        $resultRedirect->setPath('*', ['wishlist_id' => $wishlist->getId()]);
        return $resultRedirect;
      }      
    }
}
