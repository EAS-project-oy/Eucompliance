<?php

namespace Easproject\Eucompliance\Plugin;

use Magento\Framework\DataObject;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
class DefaultRenderer
{
    /**
     * @param \Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer $subject
     * @param string $result
     * @param DataObject|Item $item
     * @param string $column
     * @param $field
     * @return string
     */
    public function afterGetColumnHtml(
        \Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer $subject,
        string                                                                   $result,
        DataObject                                                               $item,
        $column,
        $field = null
    ): string {
        switch ($column) {
            case 'eas_custom_duties':
                return $subject->displayPriceAttribute('eas_custom_duties');
            case 'eas_fee':
                return $subject->displayPriceAttribute('eas_fee');
            case 'vat_on_eas_fee':
                return $subject->displayPriceAttribute('vat_on_eas_fee');
            default:
                return $result;
        }
    }
}
