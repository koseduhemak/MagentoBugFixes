<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Koseduhemak\MagentoBugFixes\Magento_CatalogSearch\Model\Layer\Filter;

/**
 * Layer attribute filter
 */
class Attribute extends \Magento\CatalogSearch\Model\Layer\Filter\Attribute
{
    /**
     * Checks whether the option reduces the number of results
     * MaxF: THIS SHOULD FIX BROKEN LAYERED NAV (NO ATTRIBUTES SHOWN (ONLY ON MIGRATED PRODUCTS))
     *
     * @param int $optionCount Count of search results with this option
     * @param int $totalSize Current search results count
     * @return bool
     */
    protected function isOptionReducesResults($optionCount, $totalSize)
    {
        // return $optionCount < $totalSize;
        return $optionCount <= $totalSize;
    }
}
