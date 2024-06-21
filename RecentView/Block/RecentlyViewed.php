<?php

namespace Dss\RecentView\Block;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class RecentlyViewed extends Template
{
    protected $productRepository;
    protected $imageHelper;
    protected $searchCriteriaBuilder;
    protected $urlBuilder;
    protected $storeManager;
    protected $priceHelper;

    /**
     * @var string
     */
    protected $_template = 'Dss_RecentView::recently_viewed.phtml';

    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        ImageHelper $imageHelper,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        PriceHelper $priceHelper,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->priceHelper = $priceHelper;
        parent::__construct($context, $data);
    }

    public function getProducts()
    {
        $skuList = $this->getSkuList();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('sku', $skuList, 'in')
            ->create();

        return $this->productRepository->getList($searchCriteria)->getItems();
    }

    public function getSkuList()
    {
        return $this->getData('product_sku');
    }

    public function getProductImageUrl($product)
    {
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl . 'catalog/product' . $product->getData('small_image');
    }

    public function getProductPrice($product)
    {
        return $this->priceHelper->currency($product->getFinalPrice(), true, false);
    }

    public function getAddToCartUrl($product)
    {
        return $this->urlBuilder->getUrl('checkout/cart/add', ['product' => $product->getId()]);
    }
}
