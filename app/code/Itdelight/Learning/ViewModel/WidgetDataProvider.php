<?php

declare(strict_types=1);

namespace Itdelight\Learning\ViewModel;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Escaper;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Provides the widget data to fill the form.
 */
class WidgetDataProvider implements ArgumentInterface
{
    /**
     * @var AbstractHelper
     */
    private AbstractHelper $helper;

    /**
     * @var Escaper
     */
    private Escaper $escaper;

    /**
     * WidgetDataProvider constructor.
     * @param AbstractHelper $helper
     * @param Escaper $escaper
     */
    public function __construct(
        AbstractHelper $helper,
        Escaper $escaper
    ) {
        $this->helper = $helper;
        $this->escaper = $escaper;
    }

    /**
     * Get user name
     *
     * @return string
     */
    public function getUserName(): string
    {
        return $this->escaper->escapeHtmlAttr($this->helper->getPostValue('name') ?: $this->helper->getUserName());
    }

    /**
     * Get user telephone
     *
     * @return string
     */
    public function getUserTelephone(): string
    {
        return $this->escaper->escapeHtmlAttr($this->helper->getPostValue('phone'));
    }

    /**
     * Get user comment
     *
     * @return string
     */
    public function getUserComment(): string
    {
        return $this->escaper->escapeHtml($this->helper->getPostValue('description'));
    }
}
