<?php

class BubbleUp_Gtmdatalayer_TestController extends Mage_Core_Controller_Front_Action {

	function conversionAction() {
		$this->loadLayout();

		$block = $this->getLayout()->createBlock(
			'gtmdatalayer/conversion',
			'gtmdatalayer_conversion'
				/*array('template' => 'bubbleup_bandpage/conversion.phtml')*/
		);

		$block->orderId = $_GET['order_id'];

		$this->getLayout()->getBlock('head')->append($block);
		
		$this->renderLayout();
	}
}