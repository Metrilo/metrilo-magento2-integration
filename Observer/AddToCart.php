<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddToCart implements ObserverInterface {

    /**
     * @param \Metrilo\Analytics\Helper\Data           $helper
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Framework\App\RequestInterface  $request
     */
    public function __construct(
        \Metrilo\Analytics\Helper\Data $helper,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->helper = $helper;
        $this->productRepository = $productRepository;
        $this->request = $request;
    }

    /**
     * Track added products to cart
     * and send to Metrilo
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer) {
        try {
            $item = $observer->getEvent()->getQuoteItem();
            $cartProduct = $observer->getEvent()->getProduct();
            $product = $item->getProduct();

            if ($cartProduct->getTypeId() == 'grouped') {
                $options = $this->request->getParam('super_group');
                if (is_array($options)) {
                    foreach ($options as $productId => $qty) {
                        if ($qty) { // check for grouped products
                            $this->_addToCart((int)$productId, $cartProduct, (int)$qty);
                        }
                    }
                }
            } elseif($cartProduct->getTypeId() == 'configurable') {
                $this->_addToCart($product->getId(), $cartProduct, $item->getQty());
            } else {
                $this->_addToCart($cartProduct->getId(), $cartProduct, $item->getQty());
            }
        } catch (Exception $e) {
            $this->helper->logError($e);
        }
    }

    /**
     * Track product to Metrilo
     *
     * @param int $productId
     * @param \Magento\Catalog\Model\Product $item
     * @param int $qty
     */
    private function _addToCart($productId, $item, $qty) {
        $product = $this->productRepository->getById($productId);
        $data = [
            'id' => (int)$product->getId(),
            'price' => (float)$product->getFinalPrice(),
            'name' => $product->getName(),
            'url' => $product->getProductUrl(),
            'quantity' => $qty
        ];
        // Add options for grouped or configurable products
        if ($item->getTypeId() == 'grouped' || $item->getTypeId() == 'configurable') {
            $data['id']     = $item->getId();
            $data['name']   = $item->getName();
            $data['url']    = $item->getProductUrl();
            // Options
            $data['option_id'] = $product->getSku();
            $data['option_name'] = trim(str_replace("-", " ", $product->getName()));
            $data['option_price'] = (float)$product->getFinalPrice();
        }
        $this->helper->addSessionEvent('track', 'add_to_cart', $data);
    }
}