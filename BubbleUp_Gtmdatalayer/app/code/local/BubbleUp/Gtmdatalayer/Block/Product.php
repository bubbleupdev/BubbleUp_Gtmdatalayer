<?php   
class BubbleUp_Gtmdatalayer_Block_Product extends BubbleUp_Gtmdatalayer_Block_Json
{   
	/*
		This block is for setting the snippet shown here:
			https://developers.google.com/tag-manager/enhanced-ecommerce#details
	*/

	function getDatalayer() {
	    $dataLayer = array(
           "event" => "productDetail",
	       "ecommerce" => array(
	            "detail" => array( // Optional. Dynamic.
	                /*"actionField" => array( // Optional. Dynamic.
	                    "list" =>  , // Optional. Dynamic. String value.
	                ),*/
	                "products" =>  array($this->getProductData()) // this function is inherited...
	            )
	        )
	    );

	    $impressions = $this->getImpressions();
	    if( $impressions ) {
	    	$dataLayer['ecommerce']['impressions'] = $impressions;
	    }


	    return $dataLayer;
	}

	function getImpressions() {
		$related = Mage::app()->getLayout()->createBlock('gtmdatalayer/related');

		if( !$related ) {
			Mage::log("No related products block could be loaded. No related impressions being added.");
			return;
		}

		$relatedCollection = $related->getItems();

		// Magento best practice says not to use count() on a collection: http://magento.stackexchange.com/questions/4036/difference-between-getsize-and-count-on-collection
		// This is an exception though.
		// The collection has already loaded by now, so no sense using getSize().
		// Calling $collection->count() can cause a fatal error if $collection is not actually a Varien_Data_Collection_Db object.
		// Count will just reutrn false or zero if this is the case.
		if( count($relatedCollection) < 1 ) {
			Mage::log("No related products were found for this page. No related impressions being added.");
			return;
		}

		$impressionBlock = Mage::app()->getLayout()->createBlock('gtmdatalayer/impression');
		$impressionBlock->currentCategory = "Related Products"; // Despite being called "category", this is the value that goes into the 'list' key in the datalayer.
		$impressionBlock->setProductCollection($relatedCollection);


		return $impressionBlock->getProductsData();
	}

	

}
?>