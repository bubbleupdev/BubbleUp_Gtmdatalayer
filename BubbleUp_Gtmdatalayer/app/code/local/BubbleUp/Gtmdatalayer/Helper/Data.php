<?php
class BubbleUp_Gtmdatalayer_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function collectChildrenProducts($order)
    {
        $items = $order->getAllItems();

        $childItems = array();

        $neededSkus = array(); // For quote items, Magento gives us the child sku for parent and child. We need the parent SKU!
        
        // First pass: Get all the children and their parents' ids.
        foreach ($items as $item) {
            if (!$item->getParentItemId()) {
                continue;
            }

            $neededSkus[] = $item->getParentItem()->getProduct()->getId();
            
            $childItems[ $item->getParentItemId() ] = $item;
        }

        // No child items means no need to get parent SKUs.
        if (count($childItems) < 1) {
            return $childItems;
        }

        // Look up all the SKUs in one single DB query!
        $_correctSkus = Mage::getResourceModel('catalog/product')->getProductsSku($neededSkus);

        // Key them for easy lookup...
        $correctSkus = array();
        foreach ($_correctSkus as $entry) {
            $correctSkus[$entry['entity_id']] = $entry['sku'];
        }

        // Second pass: Go through the products to set their parent IDs.
        foreach ($childItems as $parentId => $item) {
            $parentId = $item->getParentItem()->getProduct()->getId();

            $item->setParentProductSku($correctSkus[ $parentId ]);
        }

        return $childItems;
    }

    public function getProductCategories($product)
    {
        if (!Mage::getStoreConfig('google/gtmdatalayer/include_product_categories')) {
            return;
        }

        $categoryNames = array();
        $categoryCollection = $product->getCategoryCollection();
        $categoryCollection->addAttributeToSelect('name');

        foreach ($categoryCollection as $category) {
            $categoryPath = array();
            
            foreach ($category->getParentCategories() as $parent) {
                if (empty($parent->getName())) {
                    continue;
                }

                $categoryPath[] = $parent->getName();
            }

            #$categoryPath[] = $category->getName(); // The result of getParentCategories will include this category.
            if (empty($categoryPath)) {
                continue;
            }
            
            $categoryNames[] = implode('/', $categoryPath);
        }

        return $categoryNames;
    }

    public function getConfigurableVariantData($configurableProduct, $childProduct)
    {
        $attributes = $configurableProduct->getTypeInstance(true)->getConfigurableAttributes($configurableProduct);

        $variantData = array();

        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getProductAttribute()->getAttributeCode();

            $variantData[ $attributeCode ] = $childProduct->getAttributeText($attributeCode);
        }

        $variantData = array_values($variantData); // For now we just want the values. Maybe GTM will someday support objects like this (per Aaron.)

        return $variantData;
    }

    public function getLineItemData($order)
    {
        // To get the product color, we need the child product.
        $childItems = $this->collectChildrenProducts($order);

        $orderItems = $order->getAllVisibleItems();


        $toReturn = array();
        foreach ($orderItems as $item) {
            $itemData = array(
                "name"         => $item->getName(), // Required. Dynamic. String value.
                "id"           => $item->getProduct()->getSku(), // Required. Dynamic. String value.
                "price"        => (double)number_format($item->getBasePrice(), 2, '.', ''), // Required. Dynamic. String value.
                "quantity"     => (int)$item->getQtyOrdered() ?: (int)$item->getQty(), // In the cart, there is no qty_ordered; only qty.
                "category"     => $this->getProductCategories($item->getProduct()), // Required. Dynamic. String value.

            );

            
            if ($item->getProductType() === 'configurable' && isset($childItems[$item->getId()])) {
                $childItem = $childItems[$item->getId()];
                $itemData['variant'] = $this->getConfigurableVariantData($item->getProduct(), $childItem->getProduct());
                /*$itemData['variant'] = array(
                    $childItem->getProduct()->getAttributeText('size'),
                    $childItem->getProduct()->getAttributeText('color')
                );*/

                if ($childItem->getParentProductSku()) { // This gets set within collectChildrenProducts.
                    $itemData['id'] = $childItem->getParentProductSku();
                    
                    if (Mage::getStoreConfig('google/gtmdatalayer/include_variant_id')) {
                        $itemData['variantId'] = $childItem->getProduct()->getSku();
                    }
                }
            }


            $toReturn[] = $itemData;
        }

        return $toReturn;
        /* Spec, according to google.
                array( // Optional. Dynamic.
                    "name": , // Required. Dynamic. String value.
                    "id": , // Required. Dynamic. String value.
                    "price": , // Required. Dynamic. String value.
                    "brand": , // Required. Dynamic. String value.
                    "category": , // Required. Dynamic. String value.
                    "variant": , // Required. Dynamic. String value.
                    "quantity": , // Required. Dynamic. Numeric value.
                    "coupon": , // Required. Dynamic. String value.
                    "list": , // Required. Dynamic. String value.
                    "position": // Required. Dynamic. Numeric value.
                )*/
    }
}
