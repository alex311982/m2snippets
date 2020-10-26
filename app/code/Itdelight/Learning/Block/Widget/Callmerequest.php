<?php

declare(strict_types=1);

namespace Itdelight\Learning\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class Callmerequest extends Template implements BlockInterface
{
    protected $_template = "widget/form.phtml";

    /**
     * Returns action url for form
     *
     * @return string
     */
    public function getFormAction(): string
    {
        return $this->getUrl('widget/index/post', ['_secure' => false]);
    }
}
