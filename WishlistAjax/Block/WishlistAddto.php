<?php

namespace Nouvolution\WishlistAjax\Block;


class WishlistAddto extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Nouvolution\WishlistAjax\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Nouvolution\WishlistAjax\Helper\Data $helper
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(\Nouvolution\WishlistAjax\Helper\Data $helper,
                                \Magento\Framework\View\Element\Template\Context $context,
                                array $data = [])
    {
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isAjaxWishlistEnabled() {
        return $this->_helper->isAjaxWishlistEnabled();
    }


}
