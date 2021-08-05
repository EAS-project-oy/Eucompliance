<?php

declare(strict_types=1);

namespace Eas\Eucompliance\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
class Configuration
{
    const CONFIGURATION_ATTRIBUTES_REDUCED_VAT = 'configuration/attributes/reduced_vat';
    const CONFIGURATION_ATTRIBUTES_HSCODE = 'configuration/attributes/hscode';
    const CONFIGURATION_ATTRIBUTES_WAREHOUSE_COUNTRY = 'configuration/attributes/warehouse_country';
    const CONFIGURATION_ADVANCED_DEBUG = 'configuration/advanced/debug';
    const CONFIGURATION_CREDENTIALS_AUTH_KEYS_URL = 'configuration/credentials/auth_keys_url';
    const CONFIGURATION_CREDENTIALS_AUTHORIZE_URL = 'configuration/credentials/authorize_url';
    const CONFIGURATION_CREDENTIALS_API_KEY = 'configuration/credentials/api_key';
    const CONFIGURATION_CREDENTIALS_SECRET_API_KEY = 'configuration/credentials/secret_api_key';
    const CONFIGURATION_GENERAL_ENABLE = 'configuration/general/enable';
    const CONFIGURATION_CREDENTIALS_CALCULATE_URL = 'configuration/credentials/calculate_url';
    const CONFIGURATION_GENERAL_POST_SHIPPING = 'configuration/general/post_shipping';
    const EAS_CHECKOUT_TOKEN = 'eas_checkout_token';
    const COUNTRY_CODE_PATH = 'general/country/default';
    const EAS_REDUCED_VAT = 'eas_reduced_vat';
    const SELLER_REGISTRATION_COUNTRY = 'seller_registration_country';
    const EAS_SELLER_REGISTRATION_COUNTRY = 'eas_seller_registration_country';
    const EAS_HSCODE = 'eas_hscode';
    const EAS_FEE = 'eas_fee';
    const LOCATION_WAREHOUSE_COUNTRY = 'location_warehouse_country';
    const ORIGINATING_COUNTRY = 'originating_country';
    const EAS_WAREHOUSE_COUNTRY = 'eas_warehouse_country';
    const REDUCED_TBE_VAT_GROUP = 'reduced_tbe_vat_group';
    const EAS_CALCULATE = 'eas/calculate';
    const POSTAL = 'postal';
    const COURIER = 'courier';
    const ACCESS_TOKEN = 'access_token';
    const GOODS = "GOODS";
    const PRODUCT_ENTITY_TYPE = 4;
    const ATTRIBUTE_CODE = 'attribute_code';
    const EAS = 'eas';
    const VERIFYPEER = 'verifypeer';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var EncryptorInterface
     */
    private EncryptorInterface $encryptor;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor
    ) {
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
    public function getDefaultCountryCode(): ?string
    {
        return $this->scopeConfig->getValue(
            Configuration::COUNTRY_CODE_PATH, ScopeInterface::SCOPE_STORE);
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
