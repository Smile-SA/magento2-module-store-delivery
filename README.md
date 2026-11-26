### Smile Store Delivery 

This module is a plugin for [ElasticSuite](https://github.com/Smile-SA/elasticsuite).

This module add the ability to be delivered in store. Store delivery is a shipping method.

### Compatibility

| Module version | Magento | Mage-OS | PHP |
| --- | --- | --- | --- |
| 2.1.x | 2.4.6 and later (tested up to 2.4.9) | 1.0.0 and later (tested up to 4.0) | 8.1 to 8.5, as supported by the platform |
| 2.0.x, from 2.0.2 | 2.4.4 and 2.4.5 | not available | 8.1 |

Releases up to 2.0.1 are not covered by this table: they declared `php ^8.1` and `magento/framework >=103.0.4`, and their method signatures use native `mixed` or union types, which the code generator of Magento 2.4.4 and 2.4.5 rejects (`setup:di:compile` fails).

### Requirements

The module requires:

- [Store Locator](https://github.com/Smile-SA/magento2-module-store-locator) >= 2.2.*

### How to use

1. Install the module via Composer:

``` composer require smile/module-store-delivery ```

2. Enable it:

``` bin/magento module:enable Smile_StoreDelivery ```

3. Install the module and rebuild the DI cache:

``` bin/magento setup:upgrade ```

### How to configure

> Stores > Configuration > Sales > Shipping Methods > Store Delivery

Field                        | Type    
-----------------------------|----------------------------------------------
Enabled                      | Yes/No
Title                        | Varchar
Method Name                  | Varchar
Price                        | Decimal
Calculate Handling Fee       | Fixed/Percent
Handling Fee                 | Varchar
Displayed Error Message      | Text
Ship to Applicable Countries | All Allowed Countries/Specific Countries
Ship to Specific Countries   | Varchar (Multiselect countries)
Sort Order                   | Integer 
