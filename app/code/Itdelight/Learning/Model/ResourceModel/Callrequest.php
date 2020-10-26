<?php

declare(strict_types=1);

namespace Itdelight\Learning\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Callrequest extends AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('call_requests', 'id');
    }


}

