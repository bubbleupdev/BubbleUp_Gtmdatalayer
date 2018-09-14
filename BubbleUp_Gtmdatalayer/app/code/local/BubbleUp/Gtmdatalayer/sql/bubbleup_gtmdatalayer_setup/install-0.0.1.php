<?php

$installer = $this;
 
$installer->startSetup();
 
// Set SKU so that it shows up in the catalog product list collection

$attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'sku');
if ($attributeId) {
    $attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
    $attribute->setUsedInProductListing(1);

    $attribute->save();
}

$installer->endSetup();
