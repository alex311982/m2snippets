<?php

declare(strict_types=1);

namespace Itdelight\Learning\Plugin;

use Itdelight\Learning\Block\Widget\Callmerequest;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class AssignVariableToBlock
{
    /**
     * @var ArgumentInterface
     */
    private ArgumentInterface $viewModel;

    /**
     * AssignVariableToBlock constructor.
     * @param ArgumentInterface $viewModel
     */
    public function __construct(
        ArgumentInterface $viewModel
    ) {
        $this->viewModel = $viewModel;
    }

    /**
     * @param Callmerequest $block
     * @return array
     */
    public function beforeToHtml(Callmerequest $block): array
    {
        $block->assign('viewModel', $this->viewModel);

        return [];
    }
}
