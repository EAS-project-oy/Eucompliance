<?php

namespace Eas\Eucompliance\Model\Config\Backend;

use Eas\Eucompliance\Model\Config\Configuration;
use Eas\Eucompliance\Service\Calculate;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
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
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Eas\Eucompliance\Service\Calculate $calculate
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $writer
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param \Magento\Framework\App\RequestInterface $request
     * @param array $data
     */
    public function __construct(
        Context              $context,
        Registry             $registry,
        ScopeConfigInterface $config,
        TypeListInterface    $cacheTypeList,
        EncryptorInterface   $encryptor,
        Calculate            $calculate,
        WriterInterface      $writer,
        AbstractResource     $resource = null,
        AbstractDb           $resourceCollection = null,
        RequestInterface     $request,
        array                $data = []
    ) {
        $this->writer = $writer;
        $this->calculate = $calculate;
        $this->request = $request;
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
                $this->request->setParams([Configuration::CONFIGURATION_CREDENTIALS_API_KEY => $value]);
            } elseif ($path == Configuration::CONFIGURATION_CREDENTIALS_SECRET_API_KEY) {
                $this->request->setParams([Configuration::CONFIGURATION_CREDENTIALS_SECRET_API_KEY => $value]);
            }

            if ($this->request->getParam(Configuration::CONFIGURATION_CREDENTIALS_API_KEY) &&
                $this->request->getParam(Configuration::CONFIGURATION_CREDENTIALS_SECRET_API_KEY)
            ) {
                try {
                    $this->calculate->getAuthorizeToken(
                        $this->request->getParam(Configuration::CONFIGURATION_CREDENTIALS_API_KEY),
                        $this->request->getParam(Configuration::CONFIGURATION_CREDENTIALS_SECRET_API_KEY)
                    );

                    $this->writer->save(
                        Configuration::CONFIGURATION_CREDENTIALS_API_KEY,
                        $this->_encryptor->encrypt(
                            $this->request->getParam(Configuration::CONFIGURATION_CREDENTIALS_API_KEY)
                        ),
                        $this->getScope(),
                        $this->getScopeId()
                    );

                    $this->writer->save(
                        Configuration::CONFIGURATION_CREDENTIALS_SECRET_API_KEY,
                        $this->_encryptor->encrypt(
                            $this->request->getParam(Configuration::CONFIGURATION_CREDENTIALS_SECRET_API_KEY)
                        ),
                        $this->getScope(),
                        $this->getScopeId()
                    );
                } catch (InputException $exception) {
                    $this->request->setParams([Configuration::CONFIGURATION_CREDENTIALS_API_KEY => null]);
                    $this->request->setParams([Configuration::CONFIGURATION_CREDENTIALS_SECRET_API_KEY => null]);
                    throw new InputException(__($exception->getMessage()));
                }

                $this->request->setParams([Configuration::CONFIGURATION_CREDENTIALS_API_KEY => null]);
                $this->request->setParams([Configuration::CONFIGURATION_CREDENTIALS_SECRET_API_KEY => null]);
            }

        }
    }
}
