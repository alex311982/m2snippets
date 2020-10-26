<?php

declare(strict_types=1);

namespace Itdelight\Learning\Ui\DataProvider\Callmerequest\Form;

use Itdelight\Learning\Model\Callrequest;
use Itdelight\Learning\Model\ResourceModel\Callrequest\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * DataProvider for call me request edit form
 *
 */
class CallmerequestDataProvider extends AbstractDataProvider
{
    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $callmerequestCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $callmerequestCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $callmerequestCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();
        $this->loadedData = [];

        /** @var Callrequest $callmerequest */
        foreach ($items as $callmerequest) {
            $this->loadedData[$callmerequest->getId()] = $callmerequest->getData();
        }

        return $this->loadedData;
    }
}
