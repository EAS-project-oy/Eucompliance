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

namespace Easproject\Eucompliance\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Module\Manager;
use Magento\Store\Model\ScopeInterface;

class Configuration
{
    public const CONFIGURATION_ATTRIBUTES_REDUCED_VAT = 'configuration/attributes/reduced_vat';
    public const CONFIGURATION_ATTRIBUTES_HSCODE = 'configuration/attributes/hscode';
    public const CONFIGURATION_ATTRIBUTES_WAREHOUSE_COUNTRY = 'configuration/attributes/warehouse_country';
    public const CONFIGURATION_ATTRIBUTES_ACT_AS_DISCLOSED_AGENT = 'configuration/attributes/act_as_disclosed_agent';
    public const CONFIGURATION_ATTRIBUTES_SELLER_REGISTRATION_COUNTRY
        = 'configuration/attributes/seller_registration_country';
    public const CONFIGURATION_ADVANCED_DEBUG = 'configuration/advanced/debug';
    public const CONFIGURATION_GENERAL_DEFAULT_EMAIL = 'configuration/advanced/default_email';
    public const CONFIGURATION_CREDENTIALS_BASE_URL = 'configuration/credentials/api_url';
    public const CREDENTIALS_AUTH_KEYS_URL = '/auth/keys';
    public const CREDENTIALS_AUTHORIZE_URL = '/auth/open-id/connect';
    public const CONFIGURATION_GENERAL_TAX_NAME = 'configuration/general/tax_name';
    public const CONFIGURATION_CREDENTIALS_API_KEY = 'configuration/credentials/api_key';
    public const CONFIGURATION_CREDENTIALS_SECRET_API_KEY = 'configuration/credentials/secret_api_key';
    public const CONFIGURATION_GENERAL_ENABLE = 'configuration/general/enable';
    public const CREDENTIALS_CALCULATE_URL = '/calculate';
    public const CREDENTIALS_PAYMENT_VERIFY_URL = '/payment/verify';
    public const CONFIGURATION_GENERAL_POST_SHIPPING = 'configuration/general/post_shipping';
    public const INVENTORY_MODULE = 'Magento_Inventory';
    public const EAS_CHECKOUT_TOKEN = 'eas_checkout_token';
    public const COUNTRY_CODE_PATH = 'general/country/default';
    public const STORE_COUNTRY_CODE = 'general/store_information/country_id';
    public const EAS_REDUCED_VAT = 'eas_reduced_vat';
    public const SELLER_REGISTRATION_COUNTRY = 'seller_registration_country';
    public const EAS_SELLER_REGISTRATION_COUNTRY = 'eas_seller_registration_country';
    public const EAS_ACT_AS_DISCLOSED_AGENT = 'eas_act_as_disclosed_agent';
    public const ACT_AS_DISCLOSED_AGENT = 'act_as_disclosed_agent';
    public const EAS_HSCODE = 'eas_hscode';
    public const EAS_FEE = 'eas_fee';
    public const LOCATION_WAREHOUSE_COUNTRY = 'location_warehouse_country';
    public const ORIGINATING_COUNTRY = 'originating_country';
    public const EAS_WAREHOUSE_COUNTRY = 'eas_warehouse_country';
    public const REDUCED_TBE_VAT_GROUP = 'reduced_tbe_vat_group';
    public const COUNTRY_OF_MANUFACTURE = 'country_of_manufacture';
    public const EAS_CALCULATE = 'eas/calculate';
    public const POSTAL = 'postal';
    public const COURIER = 'courier';
    public const ACCESS_TOKEN = 'access_token';
    public const GOODS = "GOODS";
    public const GIFTCARD = "GIFTCARD";
    public const TBE = "TBE";
    public const VIRTUAL = "virtual";
    public const PRODUCT_ENTITY_TYPE = 4;
    public const ATTRIBUTE_CODE = 'attribute_code';
    public const EAS = 'eas';
    public const EAS_TOKEN = 'eas_token';
    public const EAS_SHIPPING_COST = 'eas_shipping_cost';
    public const EAS_TOTAL_VAT = 'eas_total_vat';
    public const EAS_TOTAL_TAX = 'eas_total_tax';
    public const EAS_TOTAL_AMOUNT = 'eas_total_amount';
    public const EAS_ADDITIONAL_ATTRIBUTES = 'EAS additional attributes';
    public const VERIFYPEER = 'verifypeer';
    public const CONFIGURATION_MSI_ENABLE = 'configuration/msi/enable';
    public const CONFIGURATION_MSI_MSI_ALGORITHM = 'configuration/msi/msi_algorithm';

    public const CONFIGURATION_GENERAL_MULTISHIPPING_ENABLE = "multishipping/options/checkout_multiple";

    public const CONFIGURATION_GENERAL_STANDARD_SOLUTION = 'configuration/general/standard_solution';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var EncryptorInterface
     */
    private EncryptorInterface $encryptor;

    /**
     * @var Manager
     */
    private Manager $moduleManager;

    /**
     * @var string
     */
    private string $baseUrl = '';

    /**
     * Configuration constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Manager              $moduleManager
     * @param EncryptorInterface   $encryptor
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Manager              $moduleManager,
        EncryptorInterface   $encryptor
    ) {
        $this->moduleManager = $moduleManager;
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    /**
     * Check is Enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::CONFIGURATION_GENERAL_ENABLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Calculate Url
     *
     * @return string|null
     */
    public function getCalculateUrl(): ?string
    {
        return $this->getBaseUrl() . self::CREDENTIALS_CALCULATE_URL;
    }

    /**
     * Get Payment Verify Url
     *
     * @return string|null
     */
    public function getPaymentVerifyUrl(): ?string
    {
        return $this->getBaseUrl() . self::CREDENTIALS_PAYMENT_VERIFY_URL;
    }

    /**
     * Get Postal Methods
     *
     * @return string|null
     */
    public function getPostalMethods(): ?string
    {
        return $this->scopeConfig->getValue(
            Configuration::CONFIGURATION_GENERAL_POST_SHIPPING,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Warehouse Attribute Name
     *
     * @return string|null
     */
    public function getWarehouseAttributeName(): ?string
    {
        return $this->scopeConfig->getValue(
            Configuration::CONFIGURATION_ATTRIBUTES_WAREHOUSE_COUNTRY,
            ScopeInterface::SCOPE_STORE
        ) ?: Configuration::EAS_WAREHOUSE_COUNTRY;
    }

    /**
     * Get Act As Disclosed Agent Attribute Name
     *
     * @return string|null
     */
    public function getActAsDisclosedAgentAttributeName(): ?string
    {
        return $this->scopeConfig->getValue(
            Configuration::CONFIGURATION_ATTRIBUTES_ACT_AS_DISCLOSED_AGENT,
            ScopeInterface::SCOPE_STORE
        ) ?: Configuration::EAS_ACT_AS_DISCLOSED_AGENT;
    }

    /**
     * Get Store Default Country Code
     *
     * @return string|null
     */
    public function getStoreDefaultCountryCode(): ?string
    {
        return $this->scopeConfig->getValue(
            Configuration::STORE_COUNTRY_CODE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get MSI Warehouse Location
     *
     * @return string|null
     */
    public function getMSIWarehouseLocation(): ?string
    {
        if ($this->scopeConfig->getValue(self::CONFIGURATION_MSI_ENABLE, ScopeInterface::SCOPE_STORE)
            && $this->moduleManager->isEnabled(self::INVENTORY_MODULE)
        ) {
            return $this->scopeConfig->getValue(self::CONFIGURATION_MSI_MSI_ALGORITHM, ScopeInterface::SCOPE_STORE);
        }
        return null;
    }

    /**
     * Get Hscode Attribute Name
     *
     * @return string|null
     */
    public function getHscodeAttributeName(): ?string
    {
        return $this->scopeConfig->getValue(
            Configuration::CONFIGURATION_ATTRIBUTES_HSCODE,
            ScopeInterface::SCOPE_STORE
        ) ?: self::EAS_HSCODE;
    }

    /**
     * Get Reduced Vat Attribute Name
     *
     * @return string|null
     */
    public function getReducedVatAttributeName(): ?string
    {
        return $this->scopeConfig->getValue(
            Configuration::CONFIGURATION_ATTRIBUTES_REDUCED_VAT,
            ScopeInterface::SCOPE_STORE
        ) ?: self::EAS_REDUCED_VAT;
    }

    /**
     * Get Seller Registration Name
     *
     * @return string|null
     */
    public function getSellerRegistrationName(): ?string
    {
        return $this->scopeConfig->getValue(
            Configuration::CONFIGURATION_ATTRIBUTES_SELLER_REGISTRATION_COUNTRY,
            ScopeInterface::SCOPE_STORE
        ) ?: self::EAS_SELLER_REGISTRATION_COUNTRY;
    }

    /**
     * Check is Debug Enabled
     *
     * @return bool
     */
    public function isDebugEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            Configuration::CONFIGURATION_ADVANCED_DEBUG,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Api Keys Url
     *
     * @return string|null
     */
    public function getApiKeysUrl(): ?string
    {
        return $this->getBaseUrl() . self::CREDENTIALS_AUTH_KEYS_URL;
    }

    /**
     * Get Base Url
     *
     * @return string|null
     */
    public function getBaseUrl() : ?string
    {
        if ($this->baseUrl) {
            return $this->baseUrl;
        }
        $this->baseUrl = $this->scopeConfig->getValue(
            self::CONFIGURATION_CREDENTIALS_BASE_URL,
            ScopeInterface::SCOPE_STORE
        );
        return $this->baseUrl;
    }

    /**
     * Get Authorize Url
     *
     * @return string|null
     */
    public function getAuthorizeUrl(): ?string
    {
        return $this->getBaseUrl() . self::CREDENTIALS_AUTHORIZE_URL;
    }

    /**
     * Get Tax Label
     *
     * @return string
     */
    public function getTaxLabel(): string
    {
        return $this->scopeConfig->getValue(
            Configuration::CONFIGURATION_GENERAL_TAX_NAME,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Api Key
     *
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->scopeConfig->getValue(
            Configuration::CONFIGURATION_CREDENTIALS_API_KEY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Secret Key
     *
     * @return string
     */
    public function getSecretKey(): string
    {
        return $this->encryptor->decrypt(
            $this->scopeConfig->getValue(
                Configuration::CONFIGURATION_CREDENTIALS_SECRET_API_KEY,
                ScopeInterface::SCOPE_STORE
            )
        );
    }

    /**
     * Get Default Email
     *
     * @return mixed
     */
    public function getDefaultEmail(): mixed
    {
        return $this->scopeConfig->getValue(
            Configuration::CONFIGURATION_GENERAL_DEFAULT_EMAIL,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check Multi Shipping is Enabled
     *
     * @return bool
     */
    public function isMultiShippingEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::CONFIGURATION_GENERAL_MULTISHIPPING_ENABLE,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Warning Message
     *
     * @return string
     */
    public function getWarningMessage(): string
    {
        return "We do not support multi shipping, please disable this option before activation EAS EU compliance.";
    }

    /**
     * Check if standard solution enabled
     *
     * @return bool
     */
    public function isStandardSolution(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::CONFIGURATION_GENERAL_STANDARD_SOLUTION,
            ScopeInterface::SCOPE_STORE
        );
    }
}
