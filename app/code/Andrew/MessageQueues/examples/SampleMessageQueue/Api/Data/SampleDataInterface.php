<?php /**
 * Copyright © SampleMessageQueue, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SampleMessageQueue\Api\Data;

interface SampleDataInterface
{
    /**
     * @return void
     * @param string $data
     */
    public function setData(string $data) : void;

    /**
     * @return string
     */
    public function getData(): string;
}
