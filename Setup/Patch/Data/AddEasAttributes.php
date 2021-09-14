<?php

declare(strict_types=1);

namespace Eas\Eucompliance\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Eas\Eucompliance\Model\Config\Configuration;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Api\Data\AttributeGroupInterfaceFactory;
use Magento\Eav\Api\AttributeGroupRepositoryInterface;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
class AddEasAttributes implements DataPatchInterface
{

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var AttributeGroupInterfaceFactory
     */
    private $attributeGroupFactory;

    /**
     * @var AttributeGroupRepositoryInterface
     */
    private $attributeGroupRepository;

    /**
     * AddEasAttributes constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param AttributeGroupInterfaceFactory $attributeGroupFactory
     * @param AttributeGroupRepositoryInterface $attributeGroupRepository
     * @param Product $product
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        AttributeGroupInterfaceFactory $attributeGroupFactory,
        AttributeGroupRepositoryInterface $attributeGroupRepository,
        Product $product
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeGroupFactory = $attributeGroupFactory;
        $this->attributeGroupRepository = $attributeGroupRepository;
        $this->product = $product;
    }

    public function apply()
    {
        $attributeSetId = $this->product->getDefaultAttributeSetId();
        $attributeGroup = $this->attributeGroupFactory->create();
        $attributeGroup->setAttributeSetId($attributeSetId);
        $attributeGroup->setAttributeGroupName(Configuration::EAS_ADDITIONAL_ATTRIBUTES);
        $this->attributeGroupRepository->save($attributeGroup);
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(
            Product::ENTITY,
            Configuration::EAS_HSCODE,
            [
                'type' => 'varchar',
                'label' => 'Hscode',
                'input' => 'text',
                'frontend' => '',
                'required' => false,
                'sort_order' => '5',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'default' => null,
                'visible' => true,
                'user_defined' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'group' => Configuration::EAS_ADDITIONAL_ATTRIBUTES,
                'used_in_product_listing' => false,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
            ]
        );
        $eavSetup->addAttribute(
            Product::ENTITY,
            Configuration::EAS_REDUCED_VAT,
            [
                'type' => 'int',
                'label' => 'Reduced vat',
                'input' => 'select',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'frontend' => '',
                'required' => false,
                'sort_order' => '6',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'default' => null,
                'visible' => true,
                'user_defined' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'group' => Configuration::EAS_ADDITIONAL_ATTRIBUTES,
                'used_in_product_listing' => false,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
            ]
        );
        $eavSetup->addAttribute(
            Product::ENTITY,
            Configuration::EAS_WAREHOUSE_COUNTRY,
            [
                'type' => 'varchar',
                'label' => 'Warehouse Country',
                'input' => 'select',
                'source' => 'Magento\Customer\Model\ResourceModel\Address\Attribute\Source\Country',
                'frontend' => '',
                'required' => false,
                'sort_order' => '7',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'default' => null,
                'visible' => true,
                'user_defined' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'group' => Configuration::EAS_ADDITIONAL_ATTRIBUTES,
                'used_in_product_listing' => false,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
            ]
        );
        $eavSetup->addAttribute(
            Product::ENTITY,
            Configuration::EAS_SELLER_REGISTRATION_COUNTRY,
            [
                'type' => 'varchar',
                'label' => 'Seller registration country',
                'input' => 'select',
                'source' => 'Magento\Customer\Model\ResourceModel\Address\Attribute\Source\Country',
                'frontend' => '',
                'required' => false,
                'sort_order' => '7',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'default' => null,
                'visible' => true,
                'user_defined' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'group' => Configuration::EAS_ADDITIONAL_ATTRIBUTES,
                'used_in_product_listing' => false,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
            ]
        );
        $eavSetup->addAttribute(
            Product::ENTITY,
            Configuration::EAS_ACT_AS_DISCLOSED_AGENT,
            [
                'type' => 'int',
                'label' => 'Act as disclosed agent',
                'input' => 'select',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'frontend' => '',
                'required' => false,
                'sort_order' => '6',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'default' => true,
                'visible' => true,
                'user_defined' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'group' => Configuration::EAS_ADDITIONAL_ATTRIBUTES,
                'used_in_product_listing' => false,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
            ]
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
