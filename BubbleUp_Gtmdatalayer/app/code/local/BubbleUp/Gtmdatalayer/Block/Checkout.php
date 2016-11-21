<?php   
class BubbleUp_Gtmdatalayer_Block_Checkout extends BubbleUp_Gtmdatalayer_Block_Json
{   
    /*
        This block is for setting the snippet shown here:
            https://developers.google.com/tag-manager/enhanced-ecommerce#purchases
    */


    function getDatalayer() {
        $quote = Mage::getSingleton('checkout/cart')->getQuote();

        return array(
            'event'       => 'checkout',
            'ecommerce'   => array(
                'checkout'    => array(
                    'actionField' => array(
                        'step'        => 1,
                    ),
                    'products'    => Mage::helper('gtmdatalayer')->getLineItemData($quote)
                )
            )
        );
    }


    


}
?>