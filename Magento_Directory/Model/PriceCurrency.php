<?php


namespace Koseduhemak\MagentoBugFixes\Magento_Directory\Model;


class PriceCurrency extends \Magento\Directory\Model\PriceCurrency
{
    const DEFAULT_PRECISION = 3;

    /**
     * {@inheritdoc}
     */
    public function convertAndRound($amount, $scope = null, $currency = null, $precision = self::DEFAULT_PRECISION)
    {
        return parent::convertAndRound($amount, $scope, $currency, $precision);
    }
}