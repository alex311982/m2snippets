<?php

declare(strict_types=1);

namespace Itdelight\Learning\Model;


class Status implements \Magento\Framework\Data\OptionSourceInterface
{
    const CALL_ME_REQUEST_NEW = 'new';
    const CALL_ME_REQUEST_PROCESSED = 'processed';

    /**
     * @var array
     */
    private array $options;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $types = $this->getAvailableRequestTypes();

        foreach ($types as $typeCode => $typeName) {
            $this->options[$typeCode]['label'] = $typeName;
            $this->options[$typeCode]['value'] = $typeCode;
        }

        return $this->options;
    }

    /**
     * @return array
     */
    private function getAvailableRequestTypes(): array
    {
        // @codingStandardsIgnoreStart
        return [
            self::CALL_ME_REQUEST_NEW => __('New'),
            self::CALL_ME_REQUEST_PROCESSED => __('Processed'),
        ];
        // @codingStandardsIgnoreEnd
    }
}
