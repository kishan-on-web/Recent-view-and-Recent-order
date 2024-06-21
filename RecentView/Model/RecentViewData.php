<?php

declare(strict_types=1);

namespace Dss\RecentView\Model;

use Dss\RecentView\Api\RecentViewInterface;
use Dss\RecentView\Model\RecentlyViewedFactory;
use Dss\RecentView\Model\ResourceModel\RecentlyViewed as RecentlyViewedResourceModel;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Webapi\Model\Authorization\TokenUserContext;

class RecentViewData implements RecentViewInterface
{
    /**
     * @var RecentlyViewedFactory
     */
    protected $dataFactory;

    /**
     * @var RecentlyViewedResourceModel
     */
    protected $dataResourceModel;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var TokenUserContext
     */
    protected $authorization;

    public function __construct(
        TokenUserContext $authorization,
        RecentlyViewedFactory $dataFactory,
        RecentlyViewedResourceModel $dataResourceModel,
        Request $request
    ) {
        $this->authorization = $authorization;
        $this->dataFactory = $dataFactory;
        $this->dataResourceModel = $dataResourceModel;
        $this->request = $request;
    }

    public function recentviewData()
    {
        $bodyParams = $this->request->getBodyParams();
        $customerId = $this->authorization->getUserId();

        if (!empty($bodyParams) && isset($bodyParams['product_sku']) && $customerId) {
            try {
                // Load by product_sku and customer_id
                $collection = $this->dataFactory->create()->getCollection();
                $collection->addFieldToFilter('product_sku', $bodyParams['product_sku'])
                           ->addFieldToFilter('customer_id', $customerId);

                if ($collection->getSize()) {
                    // If an entry exists
                    $dataModel = $collection->getFirstItem();
                    $dataModel->setUpdatedAt(date('Y-m-d H:i:s'));
                } else {
                    // If no entry exists, create a new one
                    $dataModel = $this->dataFactory->create();
                    $dataModel->setData([
                        'product_sku' => (string)$bodyParams['product_sku'],
                        'customer_id' => (string)$customerId
                    ]);
                }

                $this->dataResourceModel->save($dataModel);
            } catch (\Exception $e) {
                return "Error saving data: " . $e->getMessage();
            }

            return "Data added/updated successfully";
        } else {
            return "No data to add or customer not logged in";
        }
    }
}
