<?php

declare(strict_types=1);

namespace Eas\Eucompliance\Service;

use Magento\Framework\Model\AbstractModel;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\CartItemExtensionFactory;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
class CartItem
{

    /**
     * @var CartItemExtensionFactory
     */
    private CartItemExtensionFactory $cartItemInterfaceFactory;

    /**
     * @param CartItemExtensionFactory $cartItemInterfaceFactory
     */
    public function __construct(CartItemExtensionFactory $cartItemInterfaceFactory)
    {
        $this->cartItemInterfaceFactory = $cartItemInterfaceFactory;
    }

    const SAVE = 'save';
    const SET = 'set';
    const GET = 'get_';
    const ATTRIBUTE_NAMES = [
        "eas_tax_amount" => "eas_tax_amount",
        "eas_tax_percent" => "eas_tax_percent",
        "eas_row_total" => "eas_row_total",
        "eas_row_total_incl_tax" => "eas_row_total_incl_tax",
        "tax_amount" => "eas_tax_amount",
        "base_tax_amount" => "eas_tax_amount",
        "tax_percent" => "eas_tax_percent",
        "row_total" => "eas_row_total",
        "base_row_total" => "eas_row_total",
        "row_total_incl_tax" => "eas_row_total_incl_tax",
        "base_row_total_incl_tax" => "eas_row_total_incl_tax",
        "eas_custom_duties" => "eas_custom_duties",
        "eas_warehouse_code" => "eas_warehouse_code",
        "vat_on_eas_fee" => "vat_on_eas_fee",
        "eas_fee" => "eas_fee"
    ];

    /**
     * @param CartItemInterface|AbstractModel $cartItem
     * @param string $mode
     * @param array $attributes
     * @return CartItemInterface
     */
    public function handleAttributes($cartItem, string $mode = self::SAVE, array $attributes = self::ATTRIBUTE_NAMES)
    {
        $extAttributes = $cartItem->getExtensionAttributes() ?: $this->cartItemInterfaceFactory->create();

        foreach ($attributes as $key => $attributeName) {
            if (is_array($attributeName)) {

                $value = 0;
                foreach ($attributeName as $attribute) {
                    $value += $this->handleAttribute($cartItem, null, $attribute, $extAttributes, $mode);
                }
                if ($value) {
                    $setFieldName = $this->toCamelCase(self::SET . '_' . $key);
                    $cartItem->$setFieldName($value);
                }
            } else {
                $this->handleAttribute($cartItem, $key, $attributeName, $extAttributes, $mode);
            }
        }
        $cartItem->setExtensionAttributes($extAttributes);
        return $cartItem;
    }

    /**
     * @param $cartItem
     * @param string|null $key
     * @param string $attribute
     * @param $extAttributes
     * @param string $mode
     * @return mixed
     */
    private function handleAttribute(
        $cartItem,
        ?string $key,
        string $attribute,
        $extAttributes,
        string $mode = self::SAVE
    ) {
        $getAttributeName = $this->toCamelCase(self::GET . $attribute);
        $setAttributeName = $this->toCamelCase(self::SET . '_' . $attribute);
        if ($mode == self::SAVE) {
            if ($extAttributes->$getAttributeName()) {
                $cartItem->$setAttributeName($extAttributes->$getAttributeName());
            }

            if ($key) {
                $setFieldName = $this->toCamelCase(self::SET . '_' . $key);
                if ($cartItem->$getAttributeName()) {
                    $cartItem->$setFieldName($cartItem->$getAttributeName());
                }
            }

        } elseif ($mode == self::SET) {
            if ($cartItem->$getAttributeName()) {
                $extAttributes->$setAttributeName($cartItem->$getAttributeName());
            }
        }
        return $cartItem->$getAttributeName();
    }

    /**
     * Converst string to camel case
     * @param $string
     * @return string
     */
    private function toCamelCase($string): string
    {
        $str = str_replace('_', '', ucwords($string, '_'));
        return lcfirst($str);
    }
}
