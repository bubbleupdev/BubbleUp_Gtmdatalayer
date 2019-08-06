<?php   
class BubbleUp_Gtmdatalayer_Block_Conversion extends BubbleUp_Gtmdatalayer_Block_Json
{   
	/*
		This block is for setting the snippet shown here:
 			https://developers.google.com/tag-manager/enhanced-ecommerce#purchases
	*/
 	var $orderId = false; // for testing...

	function getDatalayer() {
		$order = $this->getOrder();
        $dataLayers = [];
		$orderData = array(
            'event' => 'purchase',
		    "ecommerce" => array(
		        "purchase" => array( // Optional. Dynamic.
		            "actionField" => array( // Optional. Dynamic.
		                "id"          => $order->getIncrementId(), // Optional. Dynamic. String value.
		                "revenue"     => Mage::helper('gtmdatalayer/data')->getOrderRevenue($order), // Optional. Dynamic. Numeric value.
		                "tax"         => Mage::helper('gtmdatalayer/data')->getOrderTax($order), // Optional. Dynamic. Numeric value.
		                "shipping"    => Mage::helper('gtmdatalayer/data')->getOrderShipment($order), // Optional. Dynamic. Numeric value.
		                "coupon"      => $order->getCouponCode()// Optional. Dynamic. String value.
		            ),
		            "products" => Mage::helper('gtmdatalayer')->getLineItemData($order)
		        )
		    )
		);
		if( Mage::getStoreConfig('google/gtmdatalayer/include_uid_anonymus') ) {
			$orderData['customer'] = hash('md5',$order->getData('customer_email'));
		}


		if( Mage::getStoreConfig('google/gtmdatalayer/include_billing_region')) {
			$orderData['ecommerce']['purchase']['actionField']['country'] = $order->getBillingAddress()->getCountry();
		    $orderData['ecommerce']['purchase']['actionField']['state']   = $order->getBillingAddress()->getRegionCode();
		}
        if($orderData) {
            $dataLayers[] = $orderData;
        }
        if($remarketingData = $this->getRemarketingQuoteContent($order ,'purchase')) {
            $dataLayers[] = $remarketingData;
        }

		return $dataLayers;
	}

	function getOrder() {
		$orderId = $this->orderId ?: Mage::getSingleton('checkout/session')->getLastRealOrderId();
		return Mage::getModel('sales/order')->loadByIncrementId($orderId);
	}

}
?>
