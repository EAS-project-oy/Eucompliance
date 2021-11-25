<?php

declare(strict_types=1);

namespace Eas\Eucompliance\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Module\Manager;
use Magento\Store\Model\ScopeInterface;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
class Configuration
{
    const CONFIGURATION_ATTRIBUTES_REDUCED_VAT = 'configuration/attributes/reduced_vat';
    const CONFIGURATION_ATTRIBUTES_HSCODE = 'configuration/attributes/hscode';
    const CONFIGURATION_ATTRIBUTES_WAREHOUSE_COUNTRY = 'configuration/attributes/warehouse_country';
    const CONFIGURATION_ATTRIBUTES_ACT_AS_DISCLOSED_AGENT = 'configuration/attributes/act_as_disclosed_agent';
    const CONFIGURATION_ATTRIBUTES_SELLER_REGISTRATION_COUNTRY = 'configuration/attributes/seller_registration_country';
    const CONFIGURATION_ADVANCED_DEBUG = 'configuration/advanced/debug';
    const CONFIGURATION_CREDENTIALS_AUTH_KEYS_URL = 'configuration/credentials/auth_keys_url';
    const CONFIGURATION_CREDENTIALS_AUTHORIZE_URL = 'configuration/credentials/authorize_url';
    const CONFIGURATION_CREDENTIALS_API_KEY = 'configuration/credentials/api_key';
    const CONFIGURATION_CREDENTIALS_SECRET_API_KEY = 'configuration/credentials/secret_api_key';
    const CONFIGURATION_GENERAL_ENABLE = 'configuration/general/enable';
    const CONFIGURATION_CREDENTIALS_CALCULATE_URL = 'configuration/credentials/calculate_url';
    const CONFIGURATION_CREDENTIALS_PAYMENT_VERIFY_URL = 'configuration/credentials/payment_verify_url';
    const CONFIGURATION_GENERAL_POST_SHIPPING = 'configuration/general/post_shipping';
    const INVENTORY_MODULE = 'Magento_Inventory';
    const EAS_CHECKOUT_TOKEN = 'eas_checkout_token';
    const COUNTRY_CODE_PATH = 'general/country/default';
    const STORE_COUNTRY_CODE = 'general/store_information/country_id';
    const EAS_REDUCED_VAT = 'eas_reduced_vat';
    const SELLER_REGISTRATION_COUNTRY = 'seller_registration_country';
    const EAS_SELLER_REGISTRATION_COUNTRY = 'eas_seller_registration_country';
    const EAS_ACT_AS_DISCLOSED_AGENT = 'eas_act_as_disclosed_agent';
    const ACT_AS_DISCLOSED_AGENT = 'act_as_disclosed_agent';
    const EAS_HSCODE = 'eas_hscode';
    const EAS_FEE = 'eas_fee';
    const LOCATION_WAREHOUSE_COUNTRY = 'location_warehouse_country';
    const ORIGINATING_COUNTRY = 'originating_country';
    const EAS_WAREHOUSE_COUNTRY = 'eas_warehouse_country';
    const REDUCED_TBE_VAT_GROUP = 'reduced_tbe_vat_group';
    const COUNTRY_OF_MANUFACTURE = 'country_of_manufacture';
    const EAS_CALCULATE = 'eas/calculate';
    const POSTAL = 'postal';
    const COURIER = 'courier';
    const ACCESS_TOKEN = 'access_token';
    const GOODS = "GOODS";
    const TBE = "TBE";
    const VIRTUAL = "virtual";
    const PRODUCT_ENTITY_TYPE = 4;
    const ATTRIBUTE_CODE = 'attribute_code';
    const EAS = 'eas';
    const EAS_TOKEN = 'eas_token';
    const EAS_ADDITIONAL_ATTRIBUTES = 'EAS additional attributes';
    const VERIFYPEER = 'verifypeer';
    const CONFIGURATION_MSI_ENABLE = 'configuration/msi/enable';
    const CONFIGURATION_MSI_MSI_METHODS = 'configuration/msi/msi_methods';

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
     * @param ScopeConfigInterface $scopeConfig
     * @param Manager $moduleManager
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Manager $moduleManager,
        EncryptorInterface $encryptor
    ) {
        $this->moduleManager = $moduleManager;
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::CONFIGURATION_GENERAL_ENABLE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string|null
     */
    public function getCalculateUrl(): ?string
    {
        return $this->scopeConfig->getValue(
            Configuration::CONFIGURATION_CREDENTIALS_CALCULATE_URL, ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string|null
     */
    public function getPaymentVerifyUrl(): ?string
    {
        return $this->scopeConfig->getValue(
            Configuration::CONFIGURATION_CREDENTIALS_PAYMENT_VERIFY_URL, ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string|null
     */
    public function getPostalMethods(): ?string
    {
        return $this->scopeConfig->getValue(
            Configuration::CONFIGURATION_GENERAL_POST_SHIPPING, ScopeInterface::SCOPE_STORE);
    }

    /**
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
     * @return string|null
     */
    public function getStoreDefaultCountryCode(): ?string
    {
        return $this->scopeConfig->getValue(
            Configuration::STORE_COUNTRY_CODE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string|null
     */
    public function getMSIWarehouseLocation(): ?string
    {
        if ($this->scopeConfig->getValue(self::CONFIGURATION_MSI_ENABLE, ScopeInterface::SCOPE_STORE) &&
            $this->moduleManager->isEnabled(self::INVENTORY_MODULE)) {
            return $this->scopeConfig->getValue(self::CONFIGURATION_MSI_MSI_METHODS, ScopeInterface::SCOPE_STORE);
        }
        return null;
    }

    /**
     * @return string|null
     */
    public function getHscodeAttributeName(): ?string
    {
        return $this->scopeConfig->getValue(Configuration::CONFIGURATION_ATTRIBUTES_HSCODE,
            ScopeInterface::SCOPE_STORE) ?: self::EAS_HSCODE;
    }

    /**
     * @return string|null
     */
    public function getReducedVatAttributeName(): ?string
    {
        return $this->scopeConfig->getValue(Configuration::CONFIGURATION_ATTRIBUTES_REDUCED_VAT,
            ScopeInterface::SCOPE_STORE) ?: self::EAS_REDUCED_VAT;
    }

    /**
     * @return string|null
     */
    public function getSellerRegistrationName(): ?string
    {
        return $this->scopeConfig->getValue(Configuration::CONFIGURATION_ATTRIBUTES_SELLER_REGISTRATION_COUNTRY,
            ScopeInterface::SCOPE_STORE) ?: self::EAS_SELLER_REGISTRATION_COUNTRY;
    }

    /**
     * @return bool
     */
    public function isDebugEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            Configuration::CONFIGURATION_ADVANCED_DEBUG, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string|null
     */
    public function getApiKeysUrl(): ?string
    {
        return $this->scopeConfig->getValue(
            Configuration::CONFIGURATION_CREDENTIALS_AUTH_KEYS_URL,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string|null
     */
    public function getAuthorizeUrl(): ?string
    {
        return $this->scopeConfig->getValue(
            Configuration::CONFIGURATION_CREDENTIALS_AUTHORIZE_URL,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return array
     */
    public function getApiKeys(): array
    {
        $apiKey = $this->encryptor->decrypt($this->scopeConfig->getValue(
            Configuration::CONFIGURATION_CREDENTIALS_API_KEY,
            ScopeInterface::SCOPE_STORE
        ));
        $secretApiKey = $this->encryptor->decrypt($this->scopeConfig->getValue(
            Configuration::CONFIGURATION_CREDENTIALS_SECRET_API_KEY,
            ScopeInterface::SCOPE_STORE
        ));
        return [$apiKey, $secretApiKey];
    }
}
