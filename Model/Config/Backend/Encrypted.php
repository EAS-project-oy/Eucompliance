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

namespace Easproject\Eucompliance\Model\Config\Backend;

use Easproject\Eucompliance\Service\Calculate;
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
     * @param \Magento\Framework\Model\Context                             $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface           $config
     * @param \Magento\Framework\App\Cache\TypeListInterface               $cacheTypeList
     * @param \Magento\Framework\Encryption\EncryptorInterface             $encryptor
     * @param \Easproject\Eucompliance\Service\Calculate                   $calculate
     * @param \Magento\Framework\App\Config\Storage\WriterInterface        $writer
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param \Magento\Framework\App\RequestInterface                      $request
     * @param array                                                        $data
     */
    public function __construct(
        Context              $context,
        Registry             $registry,
        ScopeConfigInterface $config,
        TypeListInterface    $cacheTypeList,
        EncryptorInterface   $encryptor,
        Calculate            $calculate,
        WriterInterface      $writer,
        RequestInterface     $request,
        AbstractResource     $resource = null,
        AbstractDb           $resourceCollection = null,
        array                $data = []
    ) {
        $this->writer = $writer;
        $this->calculate = $calculate;
        $this->request = $request;
        $this->cacheTypeList = $cacheTypeList;
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
     * @throws \Magento\Framework\Exception\InputException
     */
    public function beforeSave()
    {
        list($apiKey, $secretKey, $urlBase) = $this->getCredentialsFields();
        try {
            $this->calculate->getAuthorizeToken($apiKey, $secretKey, $urlBase);
        } catch (InputException $e) {
            throw new InputException(__($e->getMessage()));
        }
        parent::beforeSave();
    }

    /**
     * Get Credentials Fields
     *
     * @return array
     */
    public function getCredentialsFields(): array
    {
        $credentialsFields = $this->request->getParam('groups')['credentials']['fields'];
        $secretKey = $credentialsFields['secret_api_key']['value'];
        if (preg_match('/^\*+$/', $secretKey) && !empty($secretKey)) {
            $secretKey = null;
        }
        $apiKey = $credentialsFields['api_key']['value'];
        $urlBase = $credentialsFields['api_url']['value'];
        return [$apiKey, $secretKey, $urlBase];
    }
}
