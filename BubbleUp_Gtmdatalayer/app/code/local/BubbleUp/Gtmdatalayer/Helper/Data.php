<?php
class BubbleUp_Gtmdatalayer_Helper_Data extends Mage_Core_Helper_Abstract
{
	function collectChildrenProducts($order) {
        $items = $order->getAllItems();

        $childItems = array();

        $neededSkus = array(); // For quote items, Magento gives us the child sku for parent and child. We need the parent SKU!
        
        // First pass: Get all the children and their parents' ids.
        foreach($items as $item) {
        	if(!$item->getParentItemId()) continue;

            $neededSkus[] = $item->getParentItem()->getProduct()->getId();
            
            $childItems[ $item->getParentItemId() ] = $item;
        }

        // No child items means no need to get parent SKUs.
        if( count($childItems) < 1 ) return $childItems;

        // Look up all the SKUs in one single DB query!
        $_correctSkus = Mage::getResourceModel('catalog/product')->getProductsSku($neededSkus);

        // Key them for easy lookup...
		$correctSkus = array();
		foreach ($_correctSkus as $entry) {
		    $correctSkus[$entry['entity_id']] = $entry['sku'];
		}

        // Second pass: Go through the products to set their parent IDs.
		foreach($childItems as $parentId => $item) {
			$parentId = $item->getParentItem()->getProduct()->getId();

			$item->setParentProductSku( $correctSkus[ $parentId ] );
		}

        return $childItems;
    }

    function getProductCategories($product) {
        if( !Mage::getStoreConfig('google/gtmdatalayer/include_product_categories') ) {
            return;
        }

        $categoryNames = array();
        $categoryCollection = $product->getCategoryCollection();
        $categoryCollection->addAttributeToSelect('name');

        foreach($categoryCollection as $category) {
            $categoryPath = array();
            
            foreach ($category->getParentCategories() as $parent) {
                if( empty($parent->getName()) ) continue;

                $categoryPath[] = $parent->getName();
            }

            #$categoryPath[] = $category->getName(); // The result of getParentCategories will include this category.
            if( empty($categoryPath) ) continue;
            
            $categoryNames[] = implode('/', $categoryPath);
        }

        return $categoryNames;
    }

    function getConfigurableVariantData($configurableProduct, $childProduct) {
        $attributes = $configurableProduct->getTypeInstance(true)->getConfigurableAttributes($configurableProduct);

        $variantData = array();

        foreach($attributes as $attribute) {
            $attributeCode = $attribute->getProductAttribute()->getAttributeCode();

            $variantData[ $attributeCode ] = $childProduct->getAttributeText($attributeCode);
        }

        $variantData = array_values($variantData); // For now we just want the values. Maybe GTM will someday support objects like this (per Aaron.)

        return $variantData;
    }

    function getBrandAttribute(){
        $brand = Mage::getStoreConfig('google/gtmdatalayer/brand_attribute');
        if(!$brand){
            $brand = 'manufacturer';
        }
        return $brand;
    }

    function getTrackingPrice($product){
        if(Mage::getStoreConfig('google/gtmdatalayer/pricing')=="taxfree"){
           $price =  Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), false );
        } else {
            $price = (double)number_format($product->getFinalPrice(),2);
        }
        return $price;

    }
    function getTrackingSubtotal($quote){
        if(Mage::getStoreConfig('google/gtmdatalayer/pricing')=="taxfree"){
            $subtotal =  round($quote->getSubtotal(),2);
        } else {
            $subtotal =  round($quote->getGrandTotal(),2);
        }
        return $subtotal;
    }
    function getOrderRevenue($order){
        if(Mage::getStoreConfig('google/gtmdatalayer/pricing')=="taxfree"){
            $revenue =  round($order->getSubtotal(),2);
        } else {
            $revenue = round($order->getGrandTotal(),2);
        }
        return $revenue;
    }
    function getOrderTax($order){
        if(Mage::getStoreConfig('google/gtmdatalayer/pricing')=="taxfree"){
            $taxamount = 0;
        } else {
            $taxamount = round($order->getTaxAmount(),2);
        }
        return $taxamount;
    }
    function getOrderShipment($order){
        if(Mage::getStoreConfig('google/gtmdatalayer/pricing')=="taxfree"){
            $shipping = 0;
        } else {
            $shipping = round($order->getBaseShippingAmount(),2);
        }
        return $shipping;
    }
    function getLineItemData($order) {
    	// To get the product color, we need the child product.
        $childItems = $this->collectChildrenProducts($order);

        $orderItems = $order->getAllVisibleItems();


        $toReturn = array();
        foreach ($orderItems as $item) {
            $product = $item->getProduct();
            $itemData = array(
                "name"         => $item->getName(), // Required. Dynamic. String value.
                "id"           => $product->getSku(), // Required. Dynamic. String value.
                "price"        => Mage::helper('gtmdatalayer/data')->getTrackingPrice($product), // Required. Dynamic. String value.
                "quantity"     => (int)$item->getQtyOrdered() ?: (int)$item->getQty(), // In the cart, there is no qty_ordered; only qty.
                "category"     => $this->getProductCategories($item->getProduct()), // Required. Dynamic. String value.
                "brand"        => $product->getAttributeText(Mage::helper('gtmdatalayer/data')->getBrandAttribute()), // Required. Dynamic. String value.
            );

            
            if( $item->getProductType() === 'configurable' && isset( $childItems[$item->getId()] ) ) {
                $childItem = $childItems[$item->getId()];
                $itemData['variant'] = $this->getConfigurableVariantData($item->getProduct(), $childItem->getProduct());
                /*$itemData['variant'] = array(
                    $childItem->getProduct()->getAttributeText('size'),
                    $childItem->getProduct()->getAttributeText('color')
                );*/

                if( $childItem->getParentProductSku() ) { // This gets set within collectChildrenProducts.
                	$itemData['id'] = $childItem->getParentProductSku();
                    
                    if( Mage::getStoreConfig('google/gtmdatalayer/include_variant_id')) {
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

