<?php

declare(strict_types=1);

namespace Itdelight\Learning\Validator;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\InputException;

class CallmerequestFrontValidator
{
    /**
     * @var RequestInterface
     */
    protected RequestInterface $_request;

    /**
     * CallmerequestFrontValidator constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->_request = $context->getRequest();
    }

    /**
     * @return array
     * @throws InputException
     */
    public function validateRequest(): array
    {
        $request = $this->_request;

        if (trim($request->getParam('name')) === '') {
            throw InputException::requiredField('name');
        }

        if (trim($request->getParam('description')) === '') {
            throw InputException::requiredField('description');
        }

        if (trim($request->getParam('phone')) === '') {
            throw InputException::requiredField('phone');
        }

        return $request->getParams();
    }
}
