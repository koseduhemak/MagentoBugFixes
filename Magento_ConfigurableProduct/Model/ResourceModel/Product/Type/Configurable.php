<?php
/**
 * Configurable product type resource model
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Koseduhemak\MagentoBugFixes\Magento_ConfigurableProduct\Model\ResourceModel\Product\Type;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ScopeResolverInterface;

class Configurable extends \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable {
    /**
     * Product metadata pool
     *
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * Product entity link field
     *
     * @var string
     */
    private $productEntityLinkField;

    /** @var ScopeResolverInterface  */
    private $scopeResolver;

    /**
     * Load options for attribute
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $superAttribute
     * @param int $productId
     * @return array
     */
    public function getAttributeOptions($superAttribute, $productId)
    {
        $scope  = $this->getScopeResolver()->getScope();
        $select = $this->getConnection()->select()->from(
            ['super_attribute' => $this->getTable('catalog_product_super_attribute')],
            [
                'sku' => 'entity.sku',
                'product_id' => 'product_entity.entity_id',
                'attribute_code' => 'attribute.attribute_code',
                'value_index' => 'entity_value.value',
                'option_title' => $this->getConnection()->getIfNullSql(
                    'option_value.value',
                    'default_option_value.value'
                ),
                'default_title' => 'default_option_value.value',
            ]
        )->joinInner(
            ['product_entity' => $this->getTable('catalog_product_entity')],
            "product_entity.{$this->getProductEntityLinkField()} = super_attribute.product_id",
            []
        )->joinInner(
            ['product_link' => $this->getTable('catalog_product_super_link')],
            'product_link.parent_id = super_attribute.product_id',
            []
        )->joinInner(
            ['attribute' => $this->getTable('eav_attribute')],
            'attribute.attribute_id = super_attribute.attribute_id',
            []
        )->joinInner(
            ['entity' => $this->getTable('catalog_product_entity')],
            'entity.entity_id = product_link.product_id',
            []
        )->joinInner(
            ['entity_value' => $superAttribute->getBackendTable()],
            implode(
                ' AND ',
                [
                    'entity_value.attribute_id = super_attribute.attribute_id',
                    'entity_value.store_id = 0',
                    "entity_value.{$this->getProductEntityLinkField()} = "
                    . "entity.{$this->getProductEntityLinkField()}",
                ]
            ),
            []
        )->joinLeft(
            ['option_value' => $this->getTable('eav_attribute_option_value')],
            implode(
                ' AND ',
                [
                    'option_value.option_id = entity_value.value',
                    'option_value.store_id = ' . $scope->getId(),
                ]
            ),
            []
        )->joinLeft(
            ['default_option_value' => $this->getTable('eav_attribute_option_value')],
            implode(
                ' AND ',
                [
                    'default_option_value.option_id = entity_value.value',
                    'default_option_value.store_id = ' . \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
            ),
            []
        )->where(
            'super_attribute.product_id = ?',
            $productId
        )->where(
            'attribute.attribute_id = ?',
            $superAttribute->getAttributeId()
            // add fix by MaxF : https://github.com/magento/magento2/issues/7441#issuecomment-262440066 => Sorting attribute options in dropdown on frontend correctly in dropdown
        )->joinInner( ['attribute_opt' => $this->getTable('eav_attribute_option')], 'attribute_opt.option_id = entity_value.value', [] )->order( 'attribute_opt.sort_order ASC' );

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * @return ScopeResolverInterface
     * @deprecated
     */
    private function getScopeResolver()
    {
        if (!($this->scopeResolver instanceof ScopeResolverInterface)) {
            $this->scopeResolver = ObjectManager::getInstance()->get(ScopeResolverInterface::class);
        }
        return $this->scopeResolver;
    }

    /**
     * Get product metadata pool
     *
     * @return \Magento\Framework\EntityManager\MetadataPool
     */
    private function getMetadataPool()
    {
        if (!$this->metadataPool) {
            $this->metadataPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\EntityManager\MetadataPool::class);
        }
        return $this->metadataPool;
    }

    /**
     * Get product entity link field
     *
     * @return string
     */
    private function getProductEntityLinkField()
    {
        if (!$this->productEntityLinkField) {
            $this->productEntityLinkField = $this->getMetadataPool()
                ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getLinkField();
        }
        return $this->productEntityLinkField;
    }
}