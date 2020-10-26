<?php

declare(strict_types=1);

namespace Itdelight\Learning\Controller\Index;

use Itdelight\Learning\Validator\CallmerequestFrontValidator;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Psr\Log\LoggerInterface;

class Post implements HttpPostActionInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $_objectManager;

    /**
     * @var DataPersistorInterface
     */
    private DataPersistorInterface $dataPersistor;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var RequestInterface
     */
    private RequestInterface $_request;

    /**
     * @var MessageManagerInterface
     */
    private MessageManagerInterface $_messageManager;

    /**
     * @var RedirectFactory
     */
    private RedirectFactory $_resultRedirectFactory;

    /**
     * @var CallmerequestFrontValidator
     */
    private CallmerequestFrontValidator $requestValidator;

    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        LoggerInterface $logger,
        CallmerequestFrontValidator $requestValidator
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->_request = $context->getRequest();
        $this->_objectManager = $context->getObjectManager();
        $this->_resultRedirectFactory = $context->getResultRedirectFactory();
        $this->_messageManager = $context->getMessageManager();
        $this->logger = $logger;
        $this->requestValidator = $requestValidator;
    }

    /**
     * Post user question
     *
     * @return Redirect
     */
    public function execute(): Redirect
    {
        if (!$this->_request->isPost()) {
            return $this->_resultRedirectFactory->create()->setRefererOrBaseUrl();
        }

        $model = $this->_objectManager->create('Itdelight\Learning\Model\Callrequest');

        try {
            $data = $this->requestValidator->validateRequest();
            $model->setData($data);
            $model->save();

            $this->_messageManager->addSuccessMessage(
                __('Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.')
            );
            $this->dataPersistor->clear('callmerequest');
        } catch (LocalizedException $e) {
            $this->_messageManager->addErrorMessage($e->getMessage());
            $this->dataPersistor->set('callmerequest', $this->_request->getParams());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->_messageManager->addErrorMessage(
                __('An error occurred while processing your form. Please try again later.')
            );
            $this->dataPersistor->set('callmerequest', $this->_request->getParams());
        }

        return $this->_resultRedirectFactory->create()->setRefererOrBaseUrl();
    }
}
