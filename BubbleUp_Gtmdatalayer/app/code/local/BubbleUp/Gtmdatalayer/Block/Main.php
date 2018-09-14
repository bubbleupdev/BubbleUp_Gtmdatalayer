<?php 
class BubbleUp_Gtmdatalayer_Block_Main extends Mage_Core_Block_Template
{
    public function _toHtml()
    {
        return "<script>dataLayer = [];</script>";
    }
}
