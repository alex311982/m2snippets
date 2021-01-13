<?php

declare(strict_types=1);

namespace Magento\SampleGraphQlEndpoint\Model\Resolver\CacheIdentity;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

class CustomerCacheIdentities implements IdentityInterface
{
    private const CACHE_TAG = 'test_customer';

    /**
     * @inheridDoc
     */
    public function getIdentities(array $resolvedData): array
    {
        $tags = $resolvedData['id'] ? [self::CACHE_TAG, self::CACHE_TAG . '_' . $resolvedData['email']] : [];

        return $tags;
    }
}
