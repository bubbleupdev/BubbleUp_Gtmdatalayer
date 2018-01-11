# BubbleUp_Gtmdatalayer
Adds GTM DataLayer variables and events to Magento for Enhanced eCommerce tracking

## Installation Instructions
1. Download [the zip file], or git clone the repo
2. Copy all of the contents from the BubbleUp_Gtmdatalayer directory into your Magento Root directory. This can be done with drag-and-drop.
3. Refresh the caches in Magento admin.
4. Go to a product or category page, and check the value of the `dataLayer` variable in developer tools console.

## Setting up your GTM container itself
*This module only adds the [GTM DataLayer variables and events] to your pages to enable you to use [Enhanced eCommerce features in Google Analytics]. It does not add your GTM container snippet for you, as there are many utilities that already provide that functionality.*
* If you are not already using it, you should install the [Creare SEO Module]. It provides a very simple interface for putting in your GTM container ID, and rendering the container snippet on the front-end.
* If you would prefer NOT to use the [Creare SEO Module], you can instead add your GTM container snippet in the "Miscellaneous Scripts" section of the Magento configuration by going to System => Configuration => General > Design, and Copy/Pasting it from your GTM account.

## Common Issues
1. I don't see any Data Layer variables on the front-end.
..* Check that the module is enabled. In Magento admin, go to System => Configuration => Sales > Google API => GTM Data Layer, and set "Include Datalayer" to "On".

## Extra Features
*These features are disabled by default because they aren't required by the standard, but you may find them useful if you create your own custom tags in GTM. They can be enabled in the admin panel.*
* **Include Variant Id Sku**
Configurable products are reported based on their parent SKU, and the selected color (the variant.) Some custom tags may also need the actual SKU of the child product. Enable this option to include it as the key "variantId".

* **Include Product Categories**
Include a list of the product's categories when reporting product impressions. This option is disabled by default because pulling a list of product categories is resource-intensive and can slow things down.

* **Include Billing Region**
If enabled, the 2-letter country code, and the 2-letter region code will be added to the datalayer as keys "country" and "state".

* **Include 'Push Identify' feature**
If enabled, turn on 'Push Identify' feature for authorized users (push on all pages: first name, last name and e-mail). If disabled, turn off.

* **Include Google Optimize Anti-Flicker-Snippet**
If enabled, the Google Optimize Anti-Flicker-Snippet is enabled before GTM execution.

### Layout stuff
In order to have products list working on magento default template, you'll need to add the following: 

In catalog_category_default and catalog_category_layered you'll need to add <block type="core/text_list" name="product_list.after" as="after" /> into product_list (do that in your layout.xml

Also you'll need to add the following to your template ij list.phtml

There also may be the need to change your layout id in products_grid.js

[the zip file]: https://github.com/bubbleupdev/BubbleUp_Gtmdatalayer/archive/master.zip
[Creare SEO Module]: https://github.com/adampmoss/CreareSEO
[GTM DataLayer variables and events]: https://developers.google.com/tag-manager/enhanced-ecommerce
[Enhanced eCommerce features in Google Analytics]: https://support.google.com/analytics/answer/6014841?hl=en
[product impressions]: https://developers.google.com/tag-manager/enhanced-ecommerce#product-impressions
