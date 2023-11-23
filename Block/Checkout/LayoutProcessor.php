<?php

declare(strict_types=1);

namespace Smile\StoreDelivery\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\DataObject;
use Magento\Framework\UrlInterface;
use Magento\Shipping\Model\CarrierFactoryInterface;
use Smile\Map\Api\MapInterface;
use Smile\Map\Api\MapProviderInterface;
use Smile\Map\Model\AddressFormatter;
use Smile\Retailer\Api\Data\RetailerExtensionInterface;
use Smile\Retailer\Api\Data\RetailerInterface;
use Smile\Retailer\Model\ResourceModel\Retailer\Collection;
use Smile\Retailer\Model\ResourceModel\Retailer\CollectionFactory;
use Smile\StoreDelivery\Model\Carrier;
use Smile\StoreLocator\Api\Data\RetailerAddressInterface;
use Smile\StoreLocator\Helper\Data;
use Smile\StoreLocator\Helper\Schedule;
use Smile\StoreLocator\Model\Retailer\ScheduleManagement;

/**
 * Specific JS Layout processor for StoreDelivery.
 * Inject Map, Geolocation and Stores into checkout UI Components.
 */
class LayoutProcessor implements LayoutProcessorInterface
{
    private string $methodCode = Carrier::METHOD_CODE;
    private MapInterface $map;

    public function __construct(
        MapProviderInterface $mapProvider,
        private CollectionFactory $retailerCollectionFactory,
        private Data $storeLocatorHelper,
        private AddressFormatter $addressFormatter,
        private Schedule $scheduleHelper,
        private ScheduleManagement $scheduleManagement,
        private CarrierFactoryInterface $carrierFactory,
        private UrlInterface $urlBuilder,
        private CacheInterface $cache
    ) {
        $this->map = $mapProvider->getMap();
    }

    /**
     * @inheritdoc
     */
    public function process($jsLayout)
    {
        if ($this->carrierFactory->getIfActive($this->methodCode)) {
            $markers = $this->getStores();

            if ($markers) {
                // @codingStandardsIgnoreStart
                $storeDelivery = $jsLayout['components']['checkout']['children']['steps']['children']
                ['shipping-step']['children']['shippingAddress']['children']['address-list']
                ['rendererTemplates']['store-delivery']['children']['smile-store-delivery']
                ['children']['store-delivery'];
                // @codingStandardsIgnoreEnd

                $storeDelivery['provider'] = $this->map->getIdentifier();
                $storeDelivery['markers'] = $markers;
                $storeDelivery['searchPlaceholderText'] = $this->storeLocatorHelper->getSearchPlaceholder();
                $storeDelivery = array_merge($storeDelivery, $this->map->getConfig());

                // @codingStandardsIgnoreStart
                $jsLayout['components']['checkout']['children']['steps']['children']
                ['shipping-step']['children']['shippingAddress']['children']['address-list']
                ['rendererTemplates']['store-delivery']['children']['smile-store-delivery']
                ['children']['store-delivery'] = $storeDelivery;
                // @codingStandardsIgnoreEnd

                // @codingStandardsIgnoreStart
                $geocoder = $jsLayout['components']['checkout']['children']['steps']['children']
                ['shipping-step']['children']['shippingAddress']['children']['address-list']
                ['rendererTemplates']['store-delivery']['children']['smile-store-delivery']
                ['children']['store-delivery']['children']['geocoder'];
                // @codingStandardsIgnoreEnd

                $geocoder['provider'] = $this->map->getIdentifier();
                $geocoder = array_merge($geocoder, $this->map->getConfig());

                // @codingStandardsIgnoreStart
                $jsLayout['components']['checkout']['children']['steps']['children']
                ['shipping-step']['children']['shippingAddress']['children']['address-list']
                ['rendererTemplates']['store-delivery']['children']['smile-store-delivery']
                ['children']['store-delivery']['children']['geocoder'] = $geocoder;
                // @codingStandardsIgnoreEnd
            }

            // unset store-delivery if $markers found
            if (!$markers) {
                unset($jsLayout['components']['checkout']['children']['steps']['children']
                ['shipping-step']['children']['shippingAddress']['children']['address-list']
                ['rendererTemplates']['store-delivery']);
                unset($jsLayout['components']['checkout']['children']['steps']['children']
                    ['shipping-step']['children']['smile-store-delivery-address-provider']);
                unset($jsLayout['components']['checkout']['children']['sidebar']['children']
                    ['shipping-information']['children']['ship-to']['rendererTemplates']['store-delivery']);
            }
        }

        return $jsLayout;
    }

    /**
     * List of markers displayed on the map.
     */
    private function getStores(): array
    {
        $collection = $this->getRetailerCollection();
        $cacheKey   = sprintf("%s_%s", 'checkout_storedelivery', $collection->getStoreId());

        $markers = $this->cache->load($cacheKey);
        if (!$markers) {
            $markers = [];
            /** @var RetailerInterface $retailer */
            foreach ($collection as $retailer) {
                /** @var RetailerExtensionInterface $retailerExtensionAttr */
                $retailerExtensionAttr = $retailer->getExtensionAttributes();
                /** @var DataObject|RetailerAddressInterface $address */
                $address               = $retailerExtensionAttr->getAddress();
                $coords                = $address->getCoordinates();
                $markerData = [
                    'id' => $retailer->getId(),
                    'latitude' => $coords->getLatitude(),
                    'longitude' => $coords->getLongitude(),
                    'name' => $retailer->getName(),
                    'address' => $this->addressFormatter->formatAddress($address, AddressFormatter::FORMAT_ONELINE),
                    'url' => $this->storeLocatorHelper->getRetailerUrl($retailer),
                    'directionUrl' => $this->map->getDirectionUrl($address->getCoordinates()),
                    'setStoreData' => $this->getSetStorePostData($retailer),
                    'addressData' => $address->getData(),
                    'postCode' => $address->getPostcode(),
                    'city' => $address->getCity(),
                ];

                // phpcs:ignore Magento2.Performance.ForeachArrayMerge.ForeachArrayMerge
                $markerData['schedule'] = array_merge(
                    $this->scheduleHelper->getConfig(),
                    [
                        'calendar' => $this->scheduleManagement->getCalendar($retailer),
                        'openingHours' => $this->scheduleManagement->getWeekOpeningHours($retailer),
                        'specialOpeningHours' => $retailerExtensionAttr->getSpecialOpeningHours(),
                    ]
                );

                $markers[] = $markerData;
            }

            $markers = json_encode($markers);
            $this->cache->save(
                $markers,
                $cacheKey,
                $collection->getNewEmptyItem()->getCacheTags()
            );
        }

        return json_decode($markers);
    }

    /**
     * Collection of displayed retailers.
     */
    private function getRetailerCollection(): Collection
    {
        $retailerCollection = $this->retailerCollectionFactory->create();
        $retailerCollection->addAttributeToSelect(
            ['name', 'seller_code', 'contact_phone', 'contact_fax', 'contact_mail']
        );
        $retailerCollection->addFieldToFilter('allow_store_delivery', 1);
        $retailerCollection->addFieldToFilter('is_active', 1);
        $retailerCollection->addOrder('name', 'asc');

        return $retailerCollection;
    }

    /**
     * Get the JSON post data used to build the set store link.
     */
    private function getSetStorePostData(RetailerInterface $retailer): array
    {
        $setUrl   = $this->urlBuilder->getUrl('storelocator/store/set');
        $postData = ['id' => $retailer->getId()];

        return ['action' => $setUrl, 'data' => $postData];
    }
}
