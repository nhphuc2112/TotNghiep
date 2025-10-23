=== Antom Payments ===
Contributors: antomintegration
Tags: woocommerce, antom, payment gateway
Requires at least: 5.3
Tested up to: 6.6
Requires PHP: 7.2
Stable tag: 1.0.13
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Antom, the official payment gateway from Ant Group - parent company of Alipay, allows your business to access a world of payment methods.

== Description ==

Antom Payment Gateway is developed and maintained by Ant Group, the parent company of Alipay.

We are the leading payment service provider with acquiring licenses and a robust network of global partners. Our local and cross-border payment services allow you to tap into global card users as well as over 1.5 billion digital payment consumers in APAC, ensuring seamless transactions and global accessibility for your business.

From collections to payouts, Antom product suite gives you the convenience of meeting both your business and your customer needs - all in one platform with one integration.

**WooCommerce Antom Payments** lets you offer digital wallet payments — all designed to help you maximize conversion.

== Installation ==

**From merchant’s WordPress admin**
1. Go to plugin section-> Add new
2. Search for "Antom"
3. Click on Install Now
4. Click on Activate
5. Click on Settings to configure the module
6. Configure the webhook url in your Antom merchant portal.
You can learn more details by reading the documentation at this [doc](https://global.alipay.com/docs/ac/plugins/woocommerce).


== Frequently Asked Questions ==
= What's the relationship between alipay and antom? =

Antom is the official payment service provider offered by the parent company of Alipay, Ant Group.

= Does this plugin rely on any third-party services? =
This plugin rely on Antom Payment Service.
When you enable our plugin and select the payment method provided by Antom in your WooCommerce store, our plugin will request payment from Antom's payment system and guide users through the payment process.
If the user selects card payment, we will also encrypt the card information through Antom's service to ensure that the payment meets PCI-DSS requirements.
You can check related service in this [document](https://global.alipay.com/docs/ac/ams/api).
We also provide links in plugin to let you regitster and login into antom dashboard to manage your transactions.

= How do I contact the Payment Provider’s Support? =
global.service@alipay.com

== Changelog ==
v1.0.0 30th September 2024
- support digital wallet payments

v1.0.1 18th October 2024
- fix headers fetch problem in nginx

v1.0.2 22th October 2024
- import FAIL status, clear cart after payment

v1.0.3 8th November 2024
- remove on-hold status, keep order pending payment after place order

v1.0.4 8th November 2024
- improve card experience in block mode

v1.0.5 31th December 2024
- improve register experience

v1.0.6 9th January 2025
- support no login user use card payment

v1.0.7 28th January 2025
- fix ssl problem

v1.0.8 28th January 2025
- fix card input problem

v1.0.9 28th January 2025
- update icon content

v1.0.10 21th February 2025
- improve card experience in block mode

v1.0.11 18th March 2025
- support payment retry for card

v1.0.13 18th March 2025
- modify card expierence, add fail request log