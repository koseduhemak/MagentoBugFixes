# Enthaltene Fixes für Magento 2.1.9 CE

## Wenn Sonderpreis (Rabattierter Special Price) angelegt, wurde bisher der Netto Preis angezeigt
=> Nun der Brutto-Preis

* Github-Issue: https://github.com/magento/magento2/issues/6729#issuecomment-279336519

Dateien:
* Koseduhemak\MagentoBugFixes\Magento_ConfigurableProduct\Pricing\Price\ConfigurableRegularPrice

## Rechnung
* SKU entfernt
* Preis ausgerichtet
* Mehr Platz für Artikelname

Dateien:
* `Koseduhemak\MagentoBugFixes\Magento_Sales\Model\Order\Pdf\Items\Invoice\DefaultInvoice`
* `Koseduhemak\MagentoBugFixes\Magento_Sales\Model\ResourceModel\Order\Invoice\Collection\Invoice`

## Sortierung der Produkt-Attribute korrigiert (aufsteigend)

* Github-Issue: https://github.com/magento/magento2/issues/7441#issuecomment-262440066

Dateien:
* `Koseduhemak\MagentoBugFixes\Magento_ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable`

## Attribute in Filter Navigation (links, bei Kategorien oder Suche) anzeigen für neue Produkte (alte, migrierte Produkte haben funktioniert)

* Github-Issue: https://github.com/magento/magento2/issues/3209#issuecomment-262407827

Dateien:
* Koseduhemak\MagentoBugFixes\Magento_CatalogSearch\Model\Layer\Filter\Attribute
* Koseduhemak\MagentoBugFixes\Magento_CatalogSearch\Model\Layer\Filter\Category
* Koseduhemak\MagentoBugFixes\Magento_CatalogSearch\Model\Layer\Filter\Decimal 

## Sortierung von Produkten nach Preis (auf-/absteigend)
=> fixed

* Github-Issue: https://github.com/magento/magento2/issues/7367#issuecomment-332757898
* Community-Fix: https://github.com/WeareJH/m2-core-bug-configurable-prices

Dateien:
* Koseduhemak\MagentoBugFixes\Magento_ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price\Configurable

## Rundung von Preisen falsch
=> fixed

* Github-Issue: https://github.com/magento/magento2/pull/11006
* Community-Fix: https://github.com/meanbee/magento2-tax-rounding
* \+ eigenes Fix in <code>etc/di.xml</code>:

```
<preference for="Magento\Directory\Model\PriceCurrency" type="Koseduhemak\MagentoBugFixes\TaxRounding\Model\PriceCurrency" />
```

Dateien:
* Koseduhemak\MagentoBugFixes\TaxRounding\Model\PriceCurrency

## Gewicht von Produkten standardmäßig 1 kg
=> damit Magento bei Configurable Products keine Virtual Products, sondern Simple Products erzeugt (bei Varianten). Sonst fehlt der Versand / Addresseingabe beim Checkout

* Stackoverflow: https://magento.stackexchange.com/questions/148767/magento-2-set-default-weight-on-admin-panel-create-new-product-form
* Für vorhandene Produkte ändern: https://magento.stackexchange.com/questions/36234/update-weight-through-sql-query (evtl. nicht für alle ausreichend, nur bei denen das Attribut schon existiert in der Tabelle)

Dateien:

* `Koseduhemak/MagentoBugFixes/Magento_Catalog/Ui/DataProvider/Product/Form/Modifier/General`