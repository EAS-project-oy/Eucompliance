<?php
/**
 * Copyright © EAS Project Oy. All rights reserved.
 * PHP version 8
 *
 * @category Module
 * @package  Easproject_Eucompliance
 * @author   EAS Project <magento@easproject.org>
 * @license  https://github.com/EAS-project-oy/eascompliance/ General License
 * @link     https://github.com/EAS-project-oy/eascompliance
 */

declare(strict_types=1);

namespace Easproject\Eucompliance\Block\Adminhtml\Order;

use Magento\Framework\DataObject;
use Magento\Sales\Api\Data\OrderInterface;

class Totals extends \Magento\Sales\Block\Adminhtml\Order\Totals
{

    /**
     * Initialize order totals array
     *
     * @return $this|Totals
     */
    protected function _initTotals()
    {
        parent::_initTotals();
        $this->_totals['eas_custom_duties'] = new DataObject(
            [
                'code' => 'eas_custom_duties',
                'strong' => true,
                'value' => $this->getOrderItemAggregatedValue('getEasCustomDuties', $this->getSource()),
                'base_value' => $this->getOrderItemAggregatedValue('getEasCustomDuties', $this->getSource()),
                'label' => __('Total customs duties'),
                'area' => 'footer',
            ]
        );

        $this->_totals['eas_total_vat'] = new DataObject(
            [
                'code' => 'eas_total_vat',
                'strong' => true,
                'value' => $this->getSource()->getEasTotalVat(),
                'base_value' => $this->getSource()->getEasTotalVat(),
                'label' => __('Total VAT'),
                'area' => 'footer',
            ]
        );

        $this->_totals['eas_fee'] = new DataObject(
            [
                'code' => 'eas_fee',
                'strong' => true,
                'value' => $this->getOrderItemAggregatedValue('getEasFee', $this->getSource()),
                'base_value' => $this->getOrderItemAggregatedValue('getEasFee', $this->getSource()),
                'label' => __('Total Other fees'),
                'area' => 'footer',
            ]
        );

        $this->_totals['eas_fee_vat'] = new DataObject(
            [
                'code' => 'eas_fee_vat',
                'strong' => true,
                'value' => $this->getOrderItemAggregatedValue('getVatOnEasFee', $this->getSource()),
                'base_value' => $this->getOrderItemAggregatedValue('getVatOnEasFee', $this->getSource()),
                'label' => __('Total Other fees VAT'),
                'area' => 'footer',
            ]
        );

        return $this;
    }

    /**
     * Calculate order item agreement value
     *
     * @param  string         $method
     * @param  OrderInterface $order
     * @return float
     */
    private function getOrderItemAggregatedValue(string $method, OrderInterface $order): float
    {
        $value = 0;
        foreach ($order->getItems() as $item) {
            $value += $item->$method();
        }
        return $value;
    }
}
