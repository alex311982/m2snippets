<?php

declare(strict_types=1);

namespace Itdelight\Learning\Helper;

use Magento\Customer\Api\CustomerNameGenerationInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Helper\View;
use \Magento\Framework\App\Helper\AbstractHelper;

/**
 * Base helper
 *
 */
class Data extends AbstractHelper
{
    /**
     * Customer session
     *
     * @var Session
     */
    private Session $customerSession;

    /**
     * @var CustomerNameGenerationInterface
     */
    private CustomerNameGenerationInterface $customerViewHelper;

    /**
     * @var DataPersistorInterface
     */
    private DataPersistorInterface $dataPersistor;

    /**
     * @var array|null
     */
    private ?array $postData = null;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param DataPersistorInterface $dataPersistor
     * @param CustomerNameGenerationInterface $customerViewHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        DataPersistorInterface $dataPersistor,
        CustomerNameGenerationInterface $customerViewHelper
    ) {
        $this->customerSession = $customerSession;
        $this->customerViewHelper = $customerViewHelper;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     * Get user name
     *
     * @return string
     */
    public function getUserName(): string
    {
        if (!$this->customerSession->isLoggedIn()) {
            return '';
        }
        /**
         * @var \Magento\Customer\Api\Data\CustomerInterface $customer
         */
        $customer = $this->customerSession->getCustomerDataObject();

        return trim($this->customerViewHelper->getCustomerName($customer));
    }

    /**
     * Get value from POST by key
     *
     * @param string $key
     * @return string
     */
    public function getPostValue(string $key): string
    {
        if (null === $this->postData) {
            $this->postData = $this->dataPersistor->get('callmerequest');
            $this->dataPersistor->clear('callmerequest');
        }

        if (isset($this->postData[$key])) {
            return $this->postData[$key];
        }

        return '';
    }
}
