<?php
class BubbleUp_Gtmdatalayer_Model_Observer
{
    public function logCartRemove($observer)
    {
        $item = $observer->getQuoteItem();

        /*        Mage::getModel('core/session')->setProductFromShoppingCart(array(
                    'parent_id'  => $item->getParentItemId(),
                    'product_id' => $item->getProductId(),
                    'item_id'    => $item->getItemId(),
                    'qty'        => $item->getQty()
                ));


        */
        
        $productData = array(
            'product_id' => $item->getProductId(),
            'qty'        => $item->getQty(),
            'price'      => $item->getPrice()
        );

        if ($item->getProductType() === 'configurable') {
            $productData['variant_sku'] = $item->getSku();
        }

        #Mage::getModel('core/session')->setProductFromShoppingCart($productData);
        $this->pushToSession($productData, BubbleUp_Gtmdatalayer_Block_Cart::SESSION_DATA_KEY_CART_REMOVE);

        ////Mage::log("LOGGING Removed datalayer item");
        ////Mage::log(Mage::getModel('core/session')->getProductFromShoppingCart());
    }

    public function logCartAdd($observer)
    {
        $item = $observer->getEvent()->getQuoteItem();

        
        $productData = array(
            'product_id' => $item->getProductId(),
            'qty'        => $item->getQty(),
            'price'      => $item->getPrice()
        );

        if ($item->getParentItem() && $item->getParentItem()->getProductType() === 'configurable') {
            $productData['variant_id'] = $item->getProductId();
            $productData['product_id'] = $item->getParentItem()->getProductId();
            #$productData['variant'] = $item->getProduct()->getAttributeText('color');
            $productData['variant'] = Mage::helper('gtmdatalayer/data')->getConfigurableVariantData($item->getParentItem()->getProduct(), $item->getProduct());
        }

        $this->pushToSession($productData, BubbleUp_Gtmdatalayer_Block_Cart::SESSION_DATA_KEY_CART_ADD);

        $this->processRelatedProducts($item);
        #Mage::getModel('core/session')->setProductToShoppingCart($productData);
        

        ////Mage::log("LOGGING Added datalayer item");
        
        ////Mage::log(Mage::getModel('core/session')->getProductToShoppingCart());
    }

    public function processRelatedProducts($item)
    {
        if (!$item) {
            return;
        } // have to be very careful about checking for odd inputs here. Never know how this observer will get triggered, and we REALLY don't want to break add-to-cart!

        $buyRequest = $item->getBuyRequest();

        if (!$buyRequest) {
            return;
        }

        $relatedProducts = $buyRequest->getRelatedProducts();
        if (!$relatedProducts) {
            return;
        }
        
        foreach ($relatedProducts as $productId) {
            if (!$productId) {
                continue;
            }

            $productData = array(
                'product_id' => $productId,
                'qty'        => 1,
                'related_to' => $item->getProductId()
            );
            $this->pushToSession($productData, BubbleUp_Gtmdatalayer_Block_Cart::SESSION_DATA_KEY_CART_ADD);
        }
    }

   

    public function pushToSession($productData, $sessionDataKey)
    {
        //Mage::log("Pushing new product onto {$sessionDataKey}. Product Data Below:");
        //Mage::log($productData);

        $currentData = Mage::getModel('core/session')->getData($sessionDataKey, false);

        if (!isset($currentData) || !is_array($currentData)) {
            //Mage::log("{$sessionDataKey} was not yet set. Making it an empty array.");
            $currentData = array();
        }

        $currentData[] = $productData;
        
        //Mage::log("Pushed productData onto {$sessionDataKey}. It now contains ".count($currentData)." products.");

        Mage::getModel('core/session')->setData($sessionDataKey, $currentData);
        //Mage::log("Committed {$sessionDataKey} to session. Full data below.");
        //Mage::log($currentData);
    }

    public function getAttributesToInclude(Varien_Event_Observer $observer)
    {
        $attributesTransfer = $observer->getEvent()->getAttributes();

        $this->log(var_export($attributesTransfer->getData(), 1), Zend_Log::DEBUG);

        $toJoin = $this->getConfigurableAttributeCodes();

        $toAdd = array();
        foreach ($toJoin as $attribute) {
            $toAdd[ $attribute ] = '';
        }
        $attributesTransfer->addData($toAdd);

        $this->log(var_export($attributesTransfer->getData(), 1), Zend_Log::DEBUG);
    }

    public function getConfigurableAttributeCodes()
    {
        $resource = Mage::getSingleton('core/resource');

        $query = "SELECT attribute_code FROM {$resource->getTableName('eav/attribute')} WHERE attribute_id IN (SELECT attribute_id FROM {$resource->getTableName('catalog/product_super_attribute')})";

        return $resource->getConnection('core_read')->fetchCol($query);
    }

    public function log($msg, $level=null)
    {
        // See http://framework.zend.com/manual/1.12/en/zend.log.overview.html for an explanation of log level constants.
        if ($level === null) {
            $level = Zend_Log::DEBUG;
        }

        if (!Mage::getStoreConfig('design/head/demonotice') && $level > Zend_Log::WARN) {
            // If demo-notice is not enabled, we must be in production. We don't want to log DEBUG INFO or NOTICE in prod.
            return;
        }

        Mage::log($msg, $level, 'bubbleup_gtmdatalayer.log', true);
    }
}
