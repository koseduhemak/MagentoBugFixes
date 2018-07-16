<?php

namespace Koseduhemak\MagentoBugFixes\SortNewestFirst\Magento_Catalog\Block\Product\ProductList;

class Toolbar extends \Magento\Catalog\Block\Product\ProductList\Toolbar
{
    const DEFAULT_ORDER_OVERWRITE = 'e.created_at';

    const DEFAULT_SORT_DIRECTION = 'desc';

    protected $_direction = self::DEFAULT_SORT_DIRECTION;

    public function setCollection($collection) {

        $this->_collection = $collection;

        $this->_collection->setCurPage($this->getCurrentPage());

        // we need to set pagination only if passed value integer and more that 0
        $limit = (int)$this->getLimit();
        if ($limit) {
            $this->_collection->setPageSize($limit);
        }


        // switch tra i tipi di ordinamento

        // echo '<pre>';
        // var_dump($this->getAvailableOrders());
        // die;

        if ($this->getCurrentOrder()) {


            // Costruisco la custom query
            switch ($this->getCurrentOrder()) {

                case 'created_at':

                    if ( $this->getCurrentDirection() == 'desc' ) {

                        $this->_collection
                            ->getSelect()
                            ->order('e.created_at DESC');


                    } elseif ( $this->getCurrentDirection() == 'asc' ) {

                        $this->_collection
                            ->getSelect()
                            ->order('e.created_at ASC');

                    }

                    break;

                default:
                    $this->_collection->setOrder($this->getCurrentOrder(), $this->getCurrentDirection());
                    break;

            }

        }


        # echo '<pre>';
        # var_dump($this->getCurrentOrder());
         #var_dump((string) $this->_collection->getSelect());
         #die;


        return $this;

    }

    public function getCurrentOrder()
    {
        $order = $this->_getData('_current_grid_order');
        if ($order) {
            return $order;
        }

        $orders = $this->getAvailableOrders();
        $defaultOrder = self::DEFAULT_ORDER_OVERWRITE;

        if (!isset($orders[$defaultOrder])) {
            $keys = array_keys($orders);
            $defaultOrder = $keys[0];
        }

        $order = $this->_toolbarModel->getOrder();
        if (!$order || !isset($orders[$order])) {
            $order = $defaultOrder;
        }

        if ($order != $defaultOrder) {
            $this->_memorizeParam('sort_order', $order);
        }

        $this->setData('_current_grid_order', $order);
        return $order;
    }
}