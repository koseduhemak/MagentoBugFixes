<?php

namespace Koseduhemak\MagentoBugFixes\Magento_ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableRegularPrice extends \Magento\ConfigurableProduct\Pricing\Price\ConfigurableRegularPrice {
    /**
     * FIX display old prices in catalog for configurable products are calculated without tax => now with tax
     * Get min regular amount
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    protected function doGetMinRegularAmount()
    {
        $minAmount = null;
        foreach ($this->getUsedProducts() as $product) {
            $childPriceAmount = $product->getPriceInfo()->getPrice(self::PRICE_CODE)->getAmount();
            if (!$minAmount || ($childPriceAmount->getValue() < $minAmount->getValue())) {
                $minAmount = $childPriceAmount;
            }
        }
        
        return $minAmount;
    }
}