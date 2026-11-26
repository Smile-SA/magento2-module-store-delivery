# Changelog

All notable changes to this project will be documented in this file.

## [2.0.2] - Unreleased
[2.0.2]: https://github.com/Smile-SA/magento2-module-store-delivery/compare/2.0.1...2.0.2

- Supported platforms: Magento 2.4.4 and 2.4.5 only, PHP 8.1
- `composer.json` now states it: `php ^8.1` and `magento/framework >=103.0.4 <103.0.6` (previously `php ^8.1` and `magento/framework >=103.0.4`)
- Magento 2.4.6 and later and Mage-OS are supported by the 2.1.x line
- Fix `setup:di:compile` on Magento 2.4.4 and 2.4.5: native `mixed` and union types are removed from method signatures and kept in docblocks
- Require the Smile modules of the same line: `smile/module-store-locator ~2.2.5`
- CI: static analysis on Magento 2.4.5, `setup:di:compile` on Magento 2.4.4 and 2.4.5

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
