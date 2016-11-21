<?php   
class BubbleUp_Gtmdatalayer_Block_Identify extends BubbleUp_Gtmdatalayer_Block_Json
{
	/*
		This block is for setting the snippet with 'event'='identify' shown like here:
		https://developers.google.com/tag-manager/enhanced-ecommerce#details
	*/  

        function getDatalayer() {
            if( !Mage::getStoreConfig('google/gtmdatalayer/include_push_identify') ) {
                return null; // See /app/code/local/BubbleUp/Gtmdatalayer/etc/system.xml
            }

            if ( !Mage::getSingleton('customer/session')->isLoggedIn() ) {
                return null;
            };

            $customer      = Mage::getSingleton('customer/session')->getCustomer();
            $custEmail     = $customer->getEmail();     // E-mail
            $custFirstName = $customer->getFirstname(); // First Name
            $custLastName  = $customer->getLastname();  // Last Name

            return array(
                'event'       => 'identify',
                'ecommerce'   => array(
                    'customer'    => array(
                        'firstname'    => $custFirstName,
                        'lastname'     => $custLastName,
                        'email'        => $custEmail
                    )
                )
            );
        }
}