<?php

declare(strict_types=1);

namespace Magento\SampleGraphQlEndpoint\Model\Resolver;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Customers field resolver, used for GraphQL request processing.
 */
class Customer implements ResolverInterface
{

    /**
     * @var ValueFactory
     */
    private ValueFactory $valueFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private CustomerRepositoryInterface $customerRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteria;
    /**
     * @var DataObjectProcessor
     */
    private DataObjectProcessor $dataObjectConverter;

    /**
     *
     * @param ValueFactory $valueFactory
     * @param DataObjectProcessor $dataObjectConverter
     * @param CustomerRepositoryInterface $customerRepository
     * @param SearchCriteriaBuilder $searchCriteria
     */
    public function __construct(
        ValueFactory $valueFactory,
        DataObjectProcessor $dataObjectConverter,
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteria
    ) {
        $this->valueFactory = $valueFactory;
        $this->customerRepository = $customerRepository;
        $this->searchCriteria = $searchCriteria;
        $this->dataObjectConverter = $dataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($args['email'])) {
            throw new GraphQlInputException(__('email for customer should be specified', [\Magento\Customer\Model\Customer::ENTITY]));
        }

        return $this->getCustomerData($args['email']);
    }

    /**
     *
     * @param string $customerEmail
     * @return array
     * @throws LocalizedException
     */
    private function getCustomerData(string $customerEmail) : array
    {
        $customerCriteria = $this->searchCriteria->addFilter('email', $customerEmail, 'eq')->create();
        $customerColl = $this->customerRepository->getList($customerCriteria)->getItems();

        $data = $customerColl[0] ? $this->dataObjectConverter->buildOutputDataArray($customerColl[0], CustomerInterface::class) : [];

        return $data;
    }
}
