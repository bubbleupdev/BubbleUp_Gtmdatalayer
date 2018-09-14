<?php 
class BubbleUp_Gtmdatalayer_Block_Conversion extends BubbleUp_Gtmdatalayer_Block_Json
{
    /*
        This block is for setting the snippet shown here:
            https://developers.google.com/tag-manager/enhanced-ecommerce#purchases
    */
    public $orderId = false; // for testing...

    public function getDatalayer()
    {
        $order = $this->getOrder();
        
        $orderData = array(
            'event' => 'purchase',
            "ecommerce" => array(
                "purchase" => array( // Optional. Dynamic.
                    "actionField" => array( // Optional. Dynamic.
                        "id"          => $order->getIncrementId(), // Optional. Dynamic. String value.
                     //   "affiliation" => , // Optional. Static. String value.
                        "revenue"     => round($order->getBaseGrandTotal(), 2), // Optional. Dynamic. Numeric value.
                        "tax"         => round($order->getBaseTaxAmount(), 2), // Optional. Dynamic. Numeric value.
                        "shipping"    => round($order->getBaseShippingAmount(), 2), // Optional. Dynamic. Numeric value.
                        "coupon"      =>  $order->getCouponCode()// Optional. Dynamic. String value.

                    ),
                    "products" => Mage::helper('gtmdatalayer')->getLineItemData($order)
                )
            )
        );

        if (Mage::getStoreConfig('google/gtmdatalayer/include_billing_region')) {
            $orderData['ecommerce']['purchase']['actionField']['country'] = $order->getBillingAddress()->getCountry();
            $orderData['ecommerce']['purchase']['actionField']['state']   = $order->getBillingAddress()->getRegionCode();
        }

        return $orderData;
    }

    public function getOrder()
    {
        $orderId = $this->orderId ?: Mage::getSingleton('checkout/session')->getLastRealOrderId();
        return Mage::getModel('sales/order')->loadByIncrementId($orderId);
    }
}
