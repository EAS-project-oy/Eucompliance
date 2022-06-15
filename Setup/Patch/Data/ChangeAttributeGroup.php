<?php

declare(strict_types=1);

namespace Easproject\Eucompliance\Setup\Patch\Data;

use Easproject\Eucompliance\Model\Config\Configuration;
use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Api\ProductAttributeGroupRepositoryInterface;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Catalog\Model\Config;
use Magento\Eav\Api\AttributeGroupRepositoryInterface;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
class ChangeAttributeGroup implements DataPatchInterface
{

    const EAS_GIFT_CARD = 'gift_card';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeGroupRepositoryInterface
     */
    private ProductAttributeGroupRepositoryInterface $productAttributeGroup;

    /**
     * @var \Magento\Eav\Api\AttributeManagementInterface
     */
    private AttributeManagementInterface $attributeManagement;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    private Config $config;

    /**
     * @var \Magento\Eav\Api\AttributeGroupRepositoryInterface
     */
    private AttributeGroupRepositoryInterface $attributeGroupRepository;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param \Magento\Catalog\Api\ProductAttributeGroupRepositoryInterface $productAttributeGroup
     * @param \Magento\Eav\Api\AttributeManagementInterface $attributeManagement
     * @param \Magento\Catalog\Model\Config $config
     * @param \Magento\Eav\Api\AttributeGroupRepositoryInterface $attributeGroupRepository
     */
    public function __construct(
        ModuleDataSetupInterface                 $moduleDataSetup,
        EavSetupFactory                          $eavSetupFactory,
        ProductAttributeGroupRepositoryInterface $productAttributeGroup,
        AttributeManagementInterface             $attributeManagement,
        Config                                   $config,
        AttributeGroupRepositoryInterface        $attributeGroupRepository
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->productAttributeGroup = $productAttributeGroup;
        $this->attributeManagement = $attributeManagement;
        $this->config = $config;
        $this->attributeGroupRepository = $attributeGroupRepository;
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);

        $attributes = [
            Configuration::EAS_HSCODE,
            Configuration::EAS_REDUCED_VAT,
            Configuration::EAS_WAREHOUSE_COUNTRY,
            Configuration::EAS_SELLER_REGISTRATION_COUNTRY,
            Configuration::EAS_SELLER_REGISTRATION_COUNTRY,
            AddGiftCardProductAttribute::EAS_GIFT_CARD

        ];
        $attributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId);
        foreach ($attributeSetIds as $attributeSetId) {
            if ($attributeSetId) {
                $groupId = $this->config->getAttributeGroupId(
                    $attributeSetId,
                    Configuration::EAS_ADDITIONAL_ATTRIBUTES
                );
                foreach ($attributes as $attribute) {
                    $this->attributeManagement->assign(
                        'catalog_product',
                        $attributeSetId,
                        $groupId,
                        $attribute,
                        999
                    );
                }
                $this->removeAttributeGroup($attributeSetId);
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @param $attributeSetId
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function removeAttributeGroup($attributeSetId)
    {
        $groupId = $this->config->getAttributeGroupId($attributeSetId, 'eas');
        $this->productAttributeGroup->delete($this->attributeGroupRepository->get($groupId));
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }
}
