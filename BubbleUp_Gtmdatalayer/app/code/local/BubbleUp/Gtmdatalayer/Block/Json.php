<?php   
class BubbleUp_Gtmdatalayer_Block_Json extends Mage_Core_Block_Template
{

	function getProductData($product = false) {
		if( $product === false )
			$product = Mage::registry('current_product');

		return array( // Optional. Dynamic.
            "name"         => $product->getName(), // Required. Dynamic. String value.
            "id"           => $product->getSku(), // Required. Dynamic. String value.
            "price"        => $product->getFinalPrice(), // Required. Dynamic. String value.
            "category"     => Mage::helper('gtmdatalayer/data')->getProductCategories($product), // Required. Dynamic. String value.
            "magento_id"   => $product->getId(), // Included so that we can look up product data by ID for product click support

            "brand"        =>    $product->getAttributeText('manufacturer'), // Required. Dynamic. String value.
        /*    "variant"      =>    '', // Required. Dynamic. String value.
            "quantity"     =>    '', // Required. Dynamic. Numeric value.
            "coupon"       =>    '', // Required. Dynamic. String value.
            "list"         =>    '', // Required. Dynamic. String value.
            "position"     =>    ''  // Required. Dynamic. Numeric value.
         */
        );
	}

	function is_assoc(array $array) {
	  return (bool)count(array_filter(array_keys($array), 'is_string'));
	}

	function _toHtml() {
		if( !Mage::getStoreConfig('google/gtmdatalayer/include_datalayer') ) {
			return; // See /app/code/local/BubbleUp/Gtmdatalayer/etc/system.xml
		}

		$data = $this->getDatalayer();
		if(!$data) {
			return; // If there is no data, show nothing
		}

		// Sometimes we will have multiple data-layers in a non-associative array.
		if( $this->is_assoc($data) ) {
			$data = array($data);
		}

		$scripts = '';
		foreach($data as $script) {
			$scripts .= $this->_toJavascript($script);
		}

		return "<script>$scripts</script>";
	}

	function _toJavascript($data) {
		$json = json_encode($data);

		return "var toPushToDatalayer = ($json); dataLayer.push(toPushToDatalayer);";
	}

	/*function _toHtml() {
		$collection = Mage::helper('gtmdatalayer')->getBuffer();

		if( $collection->getSize() < 1 ) {
			return "<!-- GTM Datalayer :: No data to report... -->";
		}

		foreach($collection as $item) {
			$dataLayer[] = $item->getData();
		}

		$json = json_encode($dataLayer);

		return "<script>dataLayer = ($json);</script>";
	}*/
    function getRemarketingParameter($product = false) {
        if( $product === false )
            $product = Mage::registry('current_product');
        return array(
            "ecomm_prodid" => $product->getSku(),
            "ecomm_pagetype" => 'product',
            "ecomm_totalvalue" => $product->getFinalPrice(),
        );
    }
    function getRemarketingQuoteContent($quote, $pagetype){
//$cart->getAllItems() to get ALL items, parent as well as child, configurable as well as it's simple associated item
        $items = $quote->getAllVisibleItems();
        $product_ids = array();
        //$totals = $quote->getTotals();
        //$rate = $quote->getData('base_to_quote_rate');
        //$subtotalIncTax = $quote->getSubtotal()/$rate;
        foreach ($items as $item) {
            $product_ids[] = $item->getSku();
        }
        return array(
            "ecomm_prodid" => $product_ids,
            "ecomm_pagetype" => $pagetype,
            "ecomm_totalvalue" => $quote->getSubtotal(),
        );


    }
}
