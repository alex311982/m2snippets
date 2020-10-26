<?php

declare(strict_types=1);

namespace Itdelight\Learning\Controller\Adminhtml\Callmerequest;

use Itdelight\Learning\Model\Status;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Controller\Result\Redirect;
use Psr\Log\LoggerInterface;

class Save implements HttpPostActionInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $_objectManager;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var RequestInterface
     */
    private RequestInterface $_request;

    /**
     * @var RedirectFactory
     */
    private RedirectFactory $_resultRedirectFactory;

    /**
     * @var MessageManagerInterface
     */
    private MessageManagerInterface $_messageManager;

    /**
     * Save constructor.
     * @param Context $context
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger
    ) {
        $this->_request = $context->getRequest();
        $this->_objectManager = $context->getObjectManager();
        $this->_resultRedirectFactory = $context->getResultRedirectFactory();
        $this->_messageManager = $context->getMessageManager();
        $this->logger = $logger;
    }

    /**
     * @return Redirect
     */
    public function execute(): Redirect
    {
        $request = $this->_request;

        $id = $request->getParam('id');
        $status = $request->getParam('status');

        try {
            $this->checkIfStatusValid($status);

            $model = $this->_objectManager->create('Itdelight\Learning\Model\Callrequest');
            $model->load($id);
            $model->setData('status', $status);
            $model->save();

            $this->_messageManager->addSuccessMessage(__('The request has been saved.'));
        } catch (InputException $e) {
            $this->_messageManager->addErrorMessage($e->getMessage());

            return $this->_resultRedirectFactory->create()->setPath('*/*/edit');
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->_messageManager->addErrorMessage($e, __('Something went wrong while saving.'));
        }

        return $this->_resultRedirectFactory->create()->setPath('*/*/');
    }

    /**
     * @param string $status
     * @return bool
     * @throws InputException
     */
    private function checkIfStatusValid(string $status): bool
    {
        if (!in_array($status, [Status::CALL_ME_REQUEST_NEW, Status::CALL_ME_REQUEST_PROCESSED])) {
            throw InputException::invalidFieldValue('status', $status);
        }

        return true;
    }
}
