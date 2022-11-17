<?php
/**
 * Copyright © EAS Project Oy. All rights reserved.
 */

declare(strict_types=1);

namespace Easproject\Eucompliance\Block\System\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;

class Disable extends Field
{
    /**
     * Disabled element
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setDisabled('disabled');
        return $element->getElementHtml();
    }
}
