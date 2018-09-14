# BubbleUp_Gtmdatalayer
Adds GTM DataLayer variables and events to Magento for Enhanced eCommerce tracking

## Installation Instructions
1. Download [the zip file], or `git clone` the repo
2. Copy all of the contents from the `BubbleUp_Gtmdatalayer` directory into your Magento Root directory. This can be done with drag-and-drop.
3. Refresh the caches in Magento admin.
4. Go to System => Configuration => Sales => Google API => GTM Data Layer and set your GTM container ID(s). Refresh caches again.
5. Go to a product or category page, and check the value of the `dataLayer` variable in developer tools console.

## Common Issues
1. I don't see any Data Layer variables on the front-end.
> Check that the module is enabled. In Magento admin, go to System => Configuration => Sales => Google API => GTM Data Layer, and set "Include Datalayer" to "On".
2. The Data Layer is there, but not the container snippet.
> Make sure your container ID is set properly under System => Configuration => Sales => Google API => GTM Data Layer.

## Extra Features
*These features are disabled by default because they aren't required by the standard, but you may find them useful if you create your own custom tags in GTM or have other advanced integration use-cases. They can be enabled in the admin panel.*
* **Include Variant Id Sku**
Configurable products are reported based on their parent SKU, and the selected color (the variant.) Some custom tags may also need the actual SKU of the child product. Enable this option to include it as the key "variantId".

* **Include Product Categories**
Include a list of the product's categories when reporting product impressions. This option is disabled by default because pulling a list of product categories is resource-intensive and can slow things down.

* **Include Billing Region**
If enabled, the 2-letter country code, and the 2-letter region code will be added to the datalayer as keys "country" and "state".

* **Include 'Push Identify' feature**
If enabled, turn on 'Push Identify' feature for authorized users (push on all pages: first name, last name and e-mail). If disabled, turn off.

* **Include 'Push Identify' feature**
If enabled, turn on 'Push Identify' feature for authorized users (push on all pages: first name, last name and e-mail). If disabled, turn off.

* **Require Cookie Consent** (for GDPR compliance)
The module can integrate with a 3rd party GDPR consent solution, but does not provide any consent flow of its own. You can enable "Require Cookie Consent" to force the module to check a variable for truthiness before loading the container. By default, that variable is `statisticsCookieConsentGiven`. This variable name can also be changed from the configuration.

* **Multiple container IDs on the same page**
You can list as many container IDs as you want in the config, separated by commas. The module will automatically render a separate snippet for each container.

## Setting up your GTM container itself
This module only not only adds the [GTM DataLayer variables and events] to your pages to enable you to use [Enhanced eCommerce features in Google Analytics]. It now also adds the GTM container snippet to all pages for you.
Previously, this module relied on the [Creare SEO Module] for adding the container snippet itself. This latest version includes a migration script to automatically replace Creare's GTM container with the one provided by this module. This migration script runs automatically at install time, and is multi-store aware, so it should be a seamless migration from Creare_SEO.

[the zip file]: https://github.com/bubbleupdev/BubbleUp_Gtmdatalayer/archive/master.zip
[Creare SEO Module]: https://github.com/adampmoss/CreareSEO
[GTM DataLayer variables and events]: https://developers.google.com/tag-manager/enhanced-ecommerce
[Enhanced eCommerce features in Google Analytics]: https://support.google.com/analytics/answer/6014841?hl=en
[product impressions]: https://developers.google.com/tag-manager/enhanced-ecommerce#product-impressions
