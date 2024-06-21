<?php

declare(strict_types=1);

namespace Dss\RecentView\Model;

use Dss\RecentView\Api\GetPageRecentViewInterface;
use Dss\RecentView\Model\ResourceModel\RecentlyViewed\CollectionFactory;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Webapi\Model\Authorization\TokenUserContext;

class GetPageRecentViewData implements GetPageRecentViewInterface
{
    protected $collectionFactory;
    protected $pageRepository;
    protected $authorization;
    protected $layout;
    protected $orderRepository;
    protected $searchCriteriaBuilder;

    public function __construct(
        CollectionFactory $collectionFactory,
        TokenUserContext $authorization,
        PageRepositoryInterface $pageRepository,
        LayoutInterface $layout,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->authorization = $authorization;
        $this->pageRepository = $pageRepository;
        $this->layout = $layout;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function getCmsHomepage(int $pageId): array
    {
        try {
            $page = $this->pageRepository->getById($pageId);
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException(__('The CMS page with the "%1" ID doesn\'t exist.', $pageId));
        }

        $customerId = $this->authorization->getUserId();
        $content = $page->getContent();
        $skuList = [];

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('customer_id', $customerId)
            ->setOrder('updated_at', 'DESC')
            ->setPageSize(10);
        if (!empty($collection->getData())) {
            foreach ($collection as $item) {
                $skuList[] = $item->getProductSku();
            }

            if (!empty($skuList)) {
                $recentViewhtml = $this->getHtml($skuList);
        
                $content = str_replace('<div class="recent_view"></div>', '<div class="recent_view">' . $recentViewhtml . '</div>', $content);
            }
        }

        $orderSearchCriteria = $this->searchCriteriaBuilder
            ->addFilter('customer_id', $customerId)
            ->create();

        $orders = $this->orderRepository->getList($orderSearchCriteria)->getItems();
        $orderskuList = [];
        $i = 1;
        foreach ($orders as $order) {
            foreach ($order->getAllVisibleItems() as $item) {
                $sku = $item->getProduct()->getSku();
                if (!in_array($sku, $orderskuList)) {
                    $orderskuList[] = $sku;
                    $i++;
                }
                if ($i == 10) {
                    break 2;
                }
            }
        }

        if (!empty($orderskuList)) {
            $recentOrderhtml = $this->getHtml($orderskuList);
    
            $content = str_replace('<div class="recent_view_ordered"></div>', '<div class="recent_view_ordered">' . $recentOrderhtml . '</div>', $content);
        }
        $page->setContent($content);
        return [$page->getData()];
    }

    private function getHtml(array $skuList): string
    {
        $html = $this->layout->createBlock(\Dss\RecentView\Block\RecentlyViewed::class)
                ->setData('product_sku', $skuList)
                ->toHtml();
        return $html;
    }
}
