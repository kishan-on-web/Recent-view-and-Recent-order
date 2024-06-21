<?php

namespace Dss\RecentView\Model;

use Magento\Framework\Model\AbstractModel;

class RecentlyViewed extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Dss\RecentView\Model\ResourceModel\RecentlyViewed');
    }
}
