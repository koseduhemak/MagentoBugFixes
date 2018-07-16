<?php
namespace Koseduhemak\MagentoBugFixes\SortNewestFirst\Plugin\Model;

class Config
{
    public function afterGetAttributeUsedForSortByArray(
        \Magento\Catalog\Model\Config $catalogConfig,
        $options
    ) {
        $newOptions['created_at'] = __('New');
        $newOptions = array_merge_recursive($newOptions, $options);

        return $newOptions;
    }
}