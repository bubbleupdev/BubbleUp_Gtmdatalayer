(function($){
	$(attachClickListener); // Attach the event listener when the page loads.

	function getProductIdFromListItem(listItem) {
		var parsableElements = $(listItem).find('img[id^="product-collection-image-"], span[id^="product-price-"]'); // Either of these should have a product ID in the ID attribute.
		
		var productId = $(parsableElements.get(0))
			.attr('id')
			.split('-')
			.pop();

		console.log("Determined product ID for listItem", listItem, 'is', productId);

		return productId;
	}

	function getProductDataById(productId) {
		var productList = toPushToDatalayer['ecommerce']['impressions'];
		// ToDo: Put these in a keyed object so we don't have to loop through to find the product we want.
		for(var i=0; i<productList.length; i++) {
			var product = productList[i];
			if( product['magento_id'] == productId ) {
				return product;
			}
		}

		throw "Product with ID " + productId + " could not be found in the dataLayer data on this page.";
	}

	function pushDatalayerProductClick(productData, redirectUrl) {
		var dataLayerData = {
			'event': 'productClick',
			'ecommerce': {
				'click': {
					'actionField': {
						// ToDo: populate this field with the category name.
						'list': 'Search Results' // Optional list property.
					},
					'products': [productData]
				}
			}
		}

		if( redirectUrl ) {
			dataLayerData['eventCallback'] = function() {
				document.location = redirectUrl
			}
		}

		dataLayer.push(dataLayerData);
	}

	function onProductListItemClick(e){
		var listItem = $(this).closest('li');
		var productId = getProductIdFromListItem(listItem);

		var productData = getProductDataById(productId);

		var redirectUrl = $(this).attr('href');

		if( redirectUrl ) {
			if( e.ctrlKey || e.metaKey ) {
				redirectUrl = false; // This is a ctrl+click to open in a new tab. We don't want to change the current window location.
			} else {
				e.preventDefault(); // Must postpone the navigation and let Datalayer handle it.
			}
		}

		pushDatalayerProductClick(productData, redirectUrl);

		console.log("product Clickthrough data:", productData);
	}

	function attachClickListener(){
		if( document.documentElement.innerHTML.indexOf('www.googletagmanager.com') === -1 ) {
			return; // If there is no GTM on the page, don't do any of this stuff.
			// Unlike the rest of the GTM Datalayer code, this javascript will stop the product link click event!
		}
		
		$('.products-grid').on('click', 'li.item a', onProductListItemClick);	
	}
}(jQuery));