// Spec: https://developers.google.com/tag-manager/enhanced-ecommerce#checkout

(function() {

	// Hard-coding this seems safer than trying to pull it out of the DOM. Never know what sort of template customizations there will be...
	var sections = {
		'login'           : 1,
		'billing'         : 2,
		'shipping'        : 3,
		'shipping_method' : 4,
		'payment'         : 5,
		'review'          : 6
	};

    document.observe("dom:loaded", function() {
	    
	    // Push current checkout section to google analytics as the user progresses through the checkout.
	    hookFunction(Checkout.prototype, 'gotoSection', function(returnedValue, originalFunction, originalArgs) {
	        var section = originalArgs[0];

            var step = sections[ section ];

            // If the user has the same billing and shipping address, we must specifically report this step.
            if( section === 'shipping_method' && $('shipping:same_as_billing').checked ) {
	            MagentoGTM.onCheckoutStep(sections['shipping'], 'shipping');
            }

	        MagentoGTM.onCheckoutStep(step, section);
	    });


	    // For "Measuring Checkout Options" on radio buttons
	    $('checkoutSteps').on('change', 'input[type="radio"]', function(event, element) {
			var optionName = element.name + ':' + element.value;

			var parent = getParentData(element);

			MagentoGTM.onCheckoutOption(parent.step, optionName);
		});

	    // For "Measuring Checkout Options" on checkboxes
		$('checkoutSteps').on('change', 'input[type="checkbox"]', function(event, element) {
			var value = element.checked ? "yes" : 'no';
			
			var optionName = element.name + ':' + value;

			var parent = getParentData(element);

			MagentoGTM.onCheckoutOption(parent.step, optionName);
		});

		// For "Measuring Checkout Options" on credit card type
		$('checkoutSteps').on('change', '#authorizenet_cc_type', function(event, element) {
			var cardType = element[element.selectedIndex].text.trim();
		
			var optionName = 'credit_card_type:' + cardType;

			var parent = getParentData(element);

			MagentoGTM.onCheckoutOption(parent.step, optionName);
		});

		

	});

    function getParentData(element) {
    	var parentSection = element.up('li.section');
    	var sectionName = parentSection.id.replace('opc-', '');

    	return {
    		section : sectionName,
    		step    : sections[ sectionName ]
    	};
    }

    
    // Lets us call our own code after the Magento code, without affecting forwards compatability
    function hookFunction(object, functionName, callback) {
        (function(originalFunction) {
            object[functionName] = function() {
                var returnValue = originalFunction.apply(this, arguments);

                callback.apply(this, [returnValue, originalFunction, arguments]);

                return returnValue;
            };
        }(object[functionName]));
    }

}());


// Utility functions for pushing data to GTM
window.MagentoGTM = {
	'onCheckoutStep' : function(step, section) {
        dataLayer.push({
            'event': 'checkout',
            'ecommerce': {
                'checkout': {
                    'actionField': {
                        'step': step,
                        'stepLabel': section
                    }
                }
            }
        })	   
	},

	'onCheckoutOption' : function(step, checkoutOption) {
	    dataLayer.push({
	        'event': 'checkoutOption',
	        'ecommerce': {
	            'checkout_option': {
	                'actionField': {
	                    'step': step,
	                    'option': checkoutOption
	                }
	            }
	        }
	    });
	}
};