<?php
/**
 * Copyright © SampleMessageQueue, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SampleMessageQueue\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\SampleMessageQueue\Api\Data\SampleDataInterface;
use Psr\Log\LoggerInterface;

class Index implements HttpGetActionInterface
{
    /**
     * @var PublisherInterface
     */
    private PublisherInterface $publisher;
    /**
     * @var SampleDataInterface
     */
    private SampleDataInterface $sampleData;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var JsonFactory
     */
    private JsonFactory $jsonResultFactory;

    /**
     * Index constructor.
     * @param LoggerInterface $logger
     * @param SampleDataInterface $sampleData
     * @param PublisherInterface $publisher
     * @param JsonFactory $jsonResultFactory
     */
    public function __construct(
        LoggerInterface $logger,
        SampleDataInterface $sampleData,
        PublisherInterface $publisher,
        JsonFactory $jsonResultFactory
    ) {
        $this->publisher = $publisher;
        $this->sampleData = $sampleData;
        $this->logger = $logger;
        $this->jsonResultFactory = $jsonResultFactory;
    }

    /**
     * @inheridDoc
     */
    public function execute(): ResultInterface
    {
        $this->sampleData->setData('SampleMessageQueue: Some random customer data for later processing ...');
        $this->publisher->publish('new_customer.created', $this->sampleData);
        $this->logger->debug('SampleMessageQueue: Added message to queue');

        /** @var Json $result */
        $result = $this->jsonResultFactory->create();
        $result->setData(['message' => __('SampleMessageQueue: Added message to queue')]);

        return $result;
    }
}
