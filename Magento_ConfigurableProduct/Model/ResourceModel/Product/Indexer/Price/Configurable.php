<?php
/**
 * Configurable Products Price Indexer Resource model
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Koseduhemak\MagentoBugFixes\Magento_ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Api\StoreResolverInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Configurable extends \Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price\Configurable
{
    /**
     * @var StoreResolverInterface
     */
    private $storeResolver;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param string $connectionName
     * @param StoreResolverInterface $storeResolver
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Module\Manager $moduleManager,
        $connectionName = null,
        StoreResolverInterface $storeResolver = null
    ) {
        parent::__construct($context, $tableStrategy, $eavConfig, $eventManager, $moduleManager, $connectionName);
        $this->storeResolver = $storeResolver ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(StoreResolverInterface::class);
    }

    /**
     * Calculate minimal and maximal prices for configurable product options
     * and apply it to final price
     * MaxF: Fixing invalid sorting of products (regarding price)
     *
     * @return \Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price\Configurable
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _applyConfigurableOption()
    {
        $metadata = $this->getMetadataPool()->getMetadata(ProductInterface::class);
        $connection = $this->getConnection();
        $coaTable = $this->_getConfigurableOptionAggregateTable();
        $copTable = $this->_getConfigurableOptionPriceTable();
        $linkField = $metadata->getLinkField();

        $this->_prepareConfigurableOptionAggregateTable();
        $this->_prepareConfigurableOptionPriceTable();

        $subSelect = $this->getSelect();
        $subSelect->join(
            ['l' => $this->getTable('catalog_product_super_link')],
            'l.product_id = e.entity_id',
            []
        )->join(
            ['le' => $this->getTable('catalog_product_entity')],
            'le.' . $linkField . ' = l.parent_id',
            ['parent_id' => 'entity_id']
        );

        $select = $connection->select();
        $select->from(['sub' => new \Zend_Db_Expr('(' . (string)$subSelect . ')')], '')
            ->columns([
                'sub.parent_id',
                'sub.entity_id',
                'sub.customer_group_id',
                'sub.website_id',
                'sub.price',
                'sub.tier_price',
            ]);

        $query = $select->insertFromSelect($coaTable);
        $connection->query($query);

        $select = $connection->select()->from(
            [$coaTable],
            [
                'parent_id',
                'customer_group_id',
                'website_id',
                'MIN(price)',
                'MAX(price)',
                'MIN(tier_price)',
            ]
        )->group(
            ['parent_id', 'customer_group_id', 'website_id']
        );

        $query = $select->insertFromSelect($copTable);
        $connection->query($query);

        $table = ['i' => $this->_getDefaultFinalPriceTable()];
        $select = $connection->select()->join(
            ['io' => $copTable],
            'i.entity_id = io.entity_id AND i.customer_group_id = io.customer_group_id' .
            ' AND i.website_id = io.website_id',
            []
        );

        /**
         * Applying fix for MAGETWO-60098 / https://github.com/magento/magento2/issues/7367#issuecomment-332757898
         * Changed new \Zend_Db_Expr('i.min_price - i.orig_price + io.min_price') to new \Zend_Db_Expr('io.min_price')
         * Changed new \Zend_Db_Expr('i.max_price - i.orig_price + io.max_price') to new \Zend_Db_Expr('io.max_price')
         */
        $select->columns(
            [
                'min_price' => new \Zend_Db_Expr('io.min_price'),
                'max_price' => new \Zend_Db_Expr('io.max_price'),
                'tier_price' => 'io.tier_price',
            ]
        );

        $query = $select->crossUpdateFromSelect($table);
        $connection->query($query);

        $connection->delete($coaTable);
        $connection->delete($copTable);

        return $this;
    }
}
