<?php

class BubbleUp_Gtmdatalayer_Block_Related extends Mage_Catalog_Block_Product_List_Related {

	function getItems() {
		$this->_prepareData();

		return $this->_itemCollection;
	}

}