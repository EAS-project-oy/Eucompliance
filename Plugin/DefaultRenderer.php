<?php
/**
 * Copyright © EAS Project Oy. All rights reserved.
 */

namespace Easproject\Eucompliance\Plugin;

use Magento\Framework\DataObject;

class DefaultRenderer
{
    /**
     * Plugin After Get Column Html
     *
     * @param  \Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer $subject
     * @param  string                                                                   $result
     * @param  \Magento\Framework\DataObject                                            $item
     * @param  string                                                                   $column
     * @param  null                                                                     $field
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
