<?php

declare(strict_types=1);

namespace Itdelight\Learning\Model\ResourceModel\Callrequest;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Itdelight\Learning\Model\Callrequest', 'Itdelight\Learning\Model\ResourceModel\Callrequest');
    }
}
