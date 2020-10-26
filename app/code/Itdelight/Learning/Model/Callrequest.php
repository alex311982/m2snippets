<?php

declare(strict_types=1);

namespace Itdelight\Learning\Model;

use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

class Callrequest extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'callmerequest_cache_tag';

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Initialize resource model
     * @return void
     */
    public function _construct()
    {
        $this->_init('Itdelight\Learning\Model\ResourceModel\Callrequest');
    }
}
