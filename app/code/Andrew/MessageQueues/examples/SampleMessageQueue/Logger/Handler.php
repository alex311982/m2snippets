<?php
/**
 * Copyright © SampleMessageQueue, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SampleMessageQueue\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

/**
 * Class Handler
 */
class Handler extends Base
{
    /**
     * Filename constant
     */
    const FILENAME = 'var/log/sample_queues.log';

    /**
     * File name
     *
     * @var string
     */
    protected $fileName = self::FILENAME;

    /**
     * Logging level
     *
     * @var int
     */
    protected $loggerType = Logger::DEBUG;
}
