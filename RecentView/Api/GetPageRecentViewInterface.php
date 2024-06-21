<?php

declare(strict_types=1);

namespace Dss\RecentView\Api;

interface GetPageRecentViewInterface
{
    /**
     * Get recently viewed SKUs for the customer and check if they have a specific SKU for a CMS page.
     *
     * @param int $pageId
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function getCmsHomepage(int $pageId);
}
