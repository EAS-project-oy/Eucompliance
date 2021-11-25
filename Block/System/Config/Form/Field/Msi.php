<?php

declare(strict_types=1);

namespace Eas\Eucompliance\Block\System\Config\Form\Field;

use Eas\Eucompliance\Model\Config\Configuration;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
class Msi extends Field
{

    /**
     * @var Manager
     */
    private Manager $moduleManager;

    /**
     * @param Manager $moduleManager
     * @param Context $context
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Manager $moduleManager,
        Context $context,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $data, $secureRenderer);
    }

    public function render(AbstractElement $element)
    {
        if (!$this->moduleManager->isEnabled(Configuration::INVENTORY_MODULE)) {
            return '';
        }
        return parent::render($element);
    }
}