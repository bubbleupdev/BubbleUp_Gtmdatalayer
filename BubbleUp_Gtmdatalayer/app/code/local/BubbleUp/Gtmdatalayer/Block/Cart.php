<?php   
class BubbleUp_Gtmdatalayer_Block_Cart extends BubbleUp_Gtmdatalayer_Block_Json
{   
	/*
		This block is for setting the snippet shown here (Cart Add and Remove):
			https://developers.google.com/tag-manager/enhanced-ecommerce#add
	*/
	
	const SESSION_DATA_KEY_CART_ADD    = 'product_to_shopping_cart';
	const SESSION_DATA_KEY_CART_REMOVE = 'product_from_shopping_cart';
	
	function getDatalayer() {
		$dataLayers = [];

		$added = $this->harvestDataFromSession(self::SESSION_DATA_KEY_CART_ADD);
		if( $added ) {
			//Mage::log("RENDERing ADDED datalayer item");
			//Mage::log($added);
			$dataLayers[] = $this->generateEventDataCartAdd($added);
		}


		$removed = $this->harvestDataFromSession(self::SESSION_DATA_KEY_CART_REMOVE);
		if( $removed ) {
			//Mage::log("RENDERing REMVOED datalayer item");
			//Mage::log($removed);
			$dataLayers[] = $this->generateEventDataCartRemove($removed);
		}


		return $dataLayers;
	}

	function generateEventDataCartAdd($products) {
		return array(
          "event" => "addToCart",
          "ecommerce" => array(
              "add" => array(
                  #"products" => array($products)
              	  "products" => $products
              )
          )
      	);
	}

	function generateEventDataCartRemove($products) {
		return array(
            "event" => "removeFromCart",
            "ecommerce" => array(
                "remove" => array(
                    #"products" => array($products)
                    "products" => $products
                )
            )
        );
	}

	function harvestDataFromSession($key) {
		$session = Mage::getModel('core/session');

		$itemData = $session->getData($key, true); // Passing true automatically unsets the variable.
		
		if( !isset($itemData) ) {
			return false;
		}
		$products = array();
	
		foreach($itemData as $item) {
			$products[] = $this->getCartItemData($item);
		}

		return $products;
	}
	

	function getCartItemData($lineItem) {
		$product = Mage::getModel('catalog/product')->load($lineItem['product_id']);

		if(!$product->getId())
			return false;
		
		$productData = $this->getProductData($product);

		$productData['quantity'] = $lineItem['qty'];

		if( isset($lineItem['related_to']) ) {
			$productData['category'] = "Related to {$lineItem['related_to']}";
		}

		// The add-to-cart observer sets the variant color for us.
		if( isset($lineItem['variant']) ) {
			$productData['variant'] = $lineItem['variant'];
		}
		// Look up the variant's color if it's not already loaded (in the case of the remove-from-cart observer).
		if( empty($productData['variant']) && !empty($lineItem['variant_sku']) ) {
			$_vp = Mage::getModel('catalog/product');
			$variantProduct = $_vp->load($_vp->getIdBySku($lineItem['variant_sku']));
			$productData['variant'] = Mage::helper('gtmdatalayer/data')->getConfigurableVariantData($product, $variantProduct);

/*
			if( !empty($variantProduct->getAttributeText('color')) ){
				$productData['variant'] = $variantProduct->getAttributeText('color');
			}
*/

		}
		return $productData;
	}

	

	
}