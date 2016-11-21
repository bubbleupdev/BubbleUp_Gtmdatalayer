<?php   
class BubbleUp_Gtmdatalayer_Block_Impression extends BubbleUp_Gtmdatalayer_Block_Json
{   
	var $currentCategory;
	var $categoryPath;

	/*
		This block is for setting the snippet shown here:
			https://developers.google.com/tag-manager/enhanced-ecommerce#product-impressions
	*/

	function getDatalayer() {
		$this->currentCategory = $this->getCurrentCategory();

		$products = $this->getProductsData();

		if( !$products ) {
			//Mage::log("No products");
			return;
		}

	    return array(
	       "event"     => 'impressionsPushed',
	       "ecommerce" => array(
	            "impressions" => $products
	        )
	    );
	}

	function getCurrentCategory() {
		$block = Mage::getSingleton('core/layout')->getBlock('category.products');

		if( !$block ) {
			//Mage::log("Cannot load category.products block. This must not be a category page.");
			return;
		}

		$currentCategory = $block->getCurrentCategory();

		if( !$currentCategory ) {
			return;
		}


		$names = array();
		foreach ($currentCategory->getParentCategories() as $parent) {
			$names[] = $parent->getName();
		}

		$this->categoryPath = implode('/', $names);
		
		return $currentCategory->getName();
	}

	function getProductsData() {
		$collection = $this->getProductCollection(); // This is the product collection 

		if( $collection->count() < 1 ) return false;

		$i=0;

		foreach($collection as $product) {
			$thisProductData = $this->getProductData($product);
			
			// On category pages, we know the category name. We would have to do extra queries to load it on search page(s) so we don't do this right now
			if( !empty($this->categoryPath) )
				$thisProductData['category'] = $this->categoryPath;

			$thisProductData['list'] = $this->currentCategory ?: "Search Results";

			$thisProductData['position'] = $i;

			$productsData[] = $thisProductData;

			$i++;
		}

		return $productsData;
	}

}
?>