<?php
/**
 * Copyright © EAS Project Oy. All rights reserved.
 */

declare(strict_types=1);

namespace Easproject\Eucompliance\Block\System\Config\Form\Field;

use Easproject\Eucompliance\Model\Config\Configuration;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class Msi extends Field
{

    /**
     * @var Manager
     */
    private Manager $moduleManager;

    /**
     * Msi Constructor
     *
     * @param Manager                 $moduleManager
     * @param Context                 $context
     * @param array                   $data
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

    /**
     * Render element
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        if (!$this->moduleManager->isEnabled(Configuration::INVENTORY_MODULE)) {
            return '';
        }
        return parent::render($element);
    }
}
