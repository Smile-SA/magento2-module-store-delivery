### Smile Store Delivery 

This module is a plugin for [ElasticSuite](https://github.com/Smile-SA/elasticsuite).

This module add the ability to be delivered in store. Store delivery is a shipping method.

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
