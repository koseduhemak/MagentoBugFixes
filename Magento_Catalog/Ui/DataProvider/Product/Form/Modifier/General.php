<?php

namespace Koseduhemak\MagentoBugFixes\Magento_Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Ui\Component\Form;

class General extends \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\General
{
    /**
     * Customize Weight filed
     *
     * @param array $meta
     * @return array
     */
    protected function customizeWeightField(array $meta)
    {
        $weightPath = $this->arrayManager->findPath(ProductAttributeInterface::CODE_WEIGHT, $meta, null, 'children');

        if ($weightPath) {
            $meta = $this->arrayManager->merge(
                $weightPath . static::META_CONFIG_PATH,
                $meta,
                [
                    'dataScope' => ProductAttributeInterface::CODE_WEIGHT,
                    'validation' => [
                        'validate-zero-or-greater' => true
                    ],
                    'additionalClasses' => 'admin__field-small',
                    'addafter' => $this->locator->getStore()->getConfig('general/locale/weight_unit'),
                    'imports' => [
                        'disabled' => '!${$.provider}:' . self::DATA_SCOPE_PRODUCT
                            . '.product_has_weight:value'
                    ],
                    'value' => (int)$this->locator->getProduct()->getTypeInstance()->hasWeight(),
                ]
            );

            $containerPath = $this->arrayManager->findPath(
                static::CONTAINER_PREFIX . ProductAttributeInterface::CODE_WEIGHT,
                $meta,
                null,
                'children'
            );
            $meta = $this->arrayManager->merge($containerPath . static::META_CONFIG_PATH, $meta, [
                'component' => 'Magento_Ui/js/form/components/group',
            ]);

            $hasWeightPath = $this->arrayManager->slicePath($weightPath, 0, -1) . '/'
                . ProductAttributeInterface::CODE_HAS_WEIGHT;
            $meta = $this->arrayManager->set(
                $hasWeightPath . static::META_CONFIG_PATH,
                $meta,
                [

                    'dataType' => 'boolean',
                    'formElement' => Form\Element\Select::NAME,
                    'componentType' => Form\Field::NAME,
                    'dataScope' => 'product_has_weight',
                    'label' => '',
                    'options' => [
                        [
                            'label' => __('This item has weight'),
                            'value' => 1
                        ],
                        [
                            'label' => __('This item has no weight'),
                            'value' => 0
                        ],
                    ],
                    'value' => (int)$this->locator->getProduct()->getTypeInstance()->hasWeight(),
                ]
            );
        }

        return $meta;
    }
}