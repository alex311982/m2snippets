<?php

declare(strict_types=1);

namespace Itdelight\Learning\Model\ResourceModel\Callrequest;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory class for @see \Itdelight\Learning\Model\ResourceModel\Callrequest\Collection
 */
class CollectionFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $_objectManager;

    /**
     * Instance name to create
     *
     * @var string
     */
    private string $_instanceName;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(ObjectManagerInterface $objectManager, $instanceName = '\\Itdelight\\Learning\\Model\\ResourceModel\\Callrequest\\Collection')
    {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return Collection
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}
