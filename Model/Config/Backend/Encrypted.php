<?php

namespace Easproject\Eucompliance\Model\Config\Backend;

use Easproject\Eucompliance\Model\Config\Configuration;
use Easproject\Eucompliance\Service\Calculate;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\App\Config\Storage\WriterInterface;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
class Encrypted extends \Magento\Config\Model\Config\Backend\Encrypted
{

    /**
     * @var Calculate
     */
    private Calculate $calculate;

    /**
     * @var WriterInterface
     */
    private WriterInterface $writer;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param EncryptorInterface $encryptor
     * @param Calculate $calculate
     * @param WriterInterface $writer
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        EncryptorInterface $encryptor,
        Calculate $calculate,
        WriterInterface $writer,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->writer = $writer;
        $this->calculate = $calculate;
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $encryptor,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Encrypt value before saving
     *
     * @return void
     * @throws InputException
     */
    public function beforeSave()
    {
        $this->_dataSaveAllowed = false;
        $value = (string)$this->getValue();
        // don't save value, if an obscured value was received. This indicates that data was not changed.
        if (!preg_match('/^\*+$/', $value) && !empty($value)) {

            $path = $this->getPath();
            if ($path == Configuration::CONFIGURATION_CREDENTIALS_API_KEY) {
                $_SESSION[Configuration::CONFIGURATION_CREDENTIALS_API_KEY] = $value;
            } elseif ($path == Configuration::CONFIGURATION_CREDENTIALS_SECRET_API_KEY) {
                $_SESSION[Configuration::CONFIGURATION_CREDENTIALS_SECRET_API_KEY] = $value;
            }

            if ($_SESSION &&
                array_key_exists(Configuration::CONFIGURATION_CREDENTIALS_API_KEY, $_SESSION) &&
                array_key_exists(Configuration::CONFIGURATION_CREDENTIALS_SECRET_API_KEY, $_SESSION)
            ) {
                try {
                    $this->calculate->getAuthorizeToken(
                        $_SESSION[Configuration::CONFIGURATION_CREDENTIALS_API_KEY],
                        $_SESSION[Configuration::CONFIGURATION_CREDENTIALS_SECRET_API_KEY]
                    );

                    $this->writer->save(
                        Configuration::CONFIGURATION_CREDENTIALS_API_KEY,
                        $this->_encryptor->encrypt($_SESSION[Configuration::CONFIGURATION_CREDENTIALS_API_KEY]),
                        $this->getScope(),
                        $this->getScopeId()
                    );

                    $this->writer->save(
                        Configuration::CONFIGURATION_CREDENTIALS_SECRET_API_KEY,
                        $this->_encryptor->encrypt($_SESSION[Configuration::CONFIGURATION_CREDENTIALS_SECRET_API_KEY]),
                        $this->getScope(),
                        $this->getScopeId()
                    );
                } catch (InputException $exception) {
                    unset($_SESSION[Configuration::CONFIGURATION_CREDENTIALS_API_KEY]);
                    unset($_SESSION[Configuration::CONFIGURATION_CREDENTIALS_SECRET_API_KEY]);
                    throw new InputException(__($exception->getMessage()));
                }

                unset($_SESSION[Configuration::CONFIGURATION_CREDENTIALS_API_KEY]);
                unset($_SESSION[Configuration::CONFIGURATION_CREDENTIALS_SECRET_API_KEY]);
            }

        }
    }
}
