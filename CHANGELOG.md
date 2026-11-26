# Changelog

All notable changes to this project will be documented in this file.

## [2.1.0] - Unreleased
[2.1.0]: https://github.com/Smile-SA/magento2-module-store-delivery/compare/2.0.1...2.1.0

- Supported platforms: Magento 2.4.6 and later, Mage-OS 1.0.0 and later (tested up to Magento 2.4.9 and Mage-OS 4.0), PHP 8.1 to 8.5
- `composer.json` now states it: `php ^8.1` and `magento/framework >=103.0.6` (previously `php ^8.1` and `magento/framework >=103.0.4`)
- Magento 2.4.4 and 2.4.5 are supported by the 2.0.x line, from 2.0.2
- CI: static analysis and `setup:di:compile` run on Mage-OS, without Magento credentials

## [2.0.1] - 2024-05-03
[2.0.1]: https://github.com/Smile-SA/magento2-module-store-delivery/compare/2.0.0...2.0.1

- Refactor - same search design everywhere
- Dont show store delivery shipping method if no markers found

## [2.0.0] - 2023-09-20
[2.0.0]: https://github.com/Smile-SA/magento2-module-store-delivery/compare/1.1.10...2.0.0

Dataset compatibility ES 2.11.x and PHP 8.2

- Fix Dynamic type declaration
- Fix Type hinting
- Replace `Zend_Date` by `DateTime`
- Remove `MutationObserver` support
- Fix UI Component Retailer Offer editing
- Replace `Zend_Validate` by `Laminas\Validator`
- Fix Retailer Grid Column Action
- Fix Retailer Grid Mass Actions
- Fix some translations
- Remove Temando/Shipping Plugin
