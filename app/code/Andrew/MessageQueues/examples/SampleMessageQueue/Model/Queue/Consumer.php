<?php
/**
 * Copyright © SampleMessageQueue, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SampleMessageQueue\Model\Queue;

use Psr\Log\LoggerInterface;

class Consumer
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Consumer constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return void
     */
    public function processMessage(): void
    {
        $this->logger->debug('SampleMessageQueue: Processed queue message.');
    }
}
