<?php

namespace Dss\RecentView\Model\ResourceModel\RecentlyViewed;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Dss\RecentView\Model\RecentlyViewed', 
            'Dss\RecentView\Model\ResourceModel\RecentlyViewed'
        );
    }
}
