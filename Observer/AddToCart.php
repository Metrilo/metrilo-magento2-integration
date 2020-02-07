<?php

namespace Metrilo\Analytics\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddToCart implements ObserverInterface
{

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
    public function execute(Observer $observer)
    {
        try {
            $quantity = $observer->getEvent()->getQuoteItem()->getQty();
            $mainProduct = $observer->getEvent()->getProduct();

            if ($mainProduct->getTypeId() == 'grouped') {
                $options = $this->request->getParam('super_group');
                if (is_array($options)) {
                    foreach ($options as $productId => $qty) {
                        if ($qty) {
                            $product = $this->productRepository->getById($productId);
                            $this->addToCart($product, (int) $qty);
                        }
                    }
                }
            } else {
                $this->addToCart($mainProduct, $quantity);
            }
        } catch (\Exception $e) {
            $this->helper->logError($e);
        }
    }

    /**
     * Track product to Metrilo
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param int $qty
     */
    private function addToCart($product, $quantity)
    {
        $data = ['quantity' => $quantity];

        $childProduct = $this->productRepository->get($product->getSku());

        // if configurable
        if ($product->getId() != $childProduct->getId()) {
            $product = $this->productRepository->getById($product->getId());
            // for legacy reasons - we have been passing the SKU as ID for the child products
            $optionSku = $childProduct->getSku();
            $data['option_id'] = $optionSku ? $optionSku : $childProduct->getId();
            $data['option_sku'] = $childProduct->getSku();
            $data['option_name'] = $childProduct->getName();
            $data['option_price'] = (float)$childProduct->getFinalPrice();
        }

        $data['id'] = (string)$product->getId();
        $data['sku'] = $product->getSku();
        $data['name'] = $product->getName();
        $data['price'] = (float)$product->getFinalPrice();
        $data['url'] = $product->getProductUrl();

        $this->helper->addSessionEvent('track', 'add_to_cart', $data);
    }
}
