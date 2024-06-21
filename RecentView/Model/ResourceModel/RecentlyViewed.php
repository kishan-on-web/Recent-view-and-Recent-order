<?php

namespace Dss\RecentView\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class RecentlyViewed extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('dss_recently_view', 'entity_id');
    }
}
