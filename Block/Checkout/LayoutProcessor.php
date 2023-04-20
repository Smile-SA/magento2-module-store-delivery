<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\StoreDelivery
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2017 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\StoreDelivery\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\UrlInterface;
use Magento\Shipping\Model\CarrierFactoryInterface;
use Smile\Map\Api\MapInterface;
use Smile\Map\Api\MapProviderInterface;
use Smile\Map\Model\AddressFormatter;
use Smile\Retailer\Api\Data\RetailerInterface;
use Smile\Retailer\Model\ResourceModel\Retailer\Collection;
use Smile\Retailer\Model\ResourceModel\Retailer\CollectionFactory;
use Smile\StoreDelivery\Model\Carrier;
use Smile\StoreLocator\Helper\Data;
use Smile\StoreLocator\Helper\Schedule;
use Smile\StoreLocator\Model\Retailer\ScheduleManagement;

/**
 * Specific JS Layout processor for StoreDelivery.
 * Inject Map, Geolocation and Stores into checkout UI Components.
 *
 * @category Smile
 * @package  Smile\StoreDelivery
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class LayoutProcessor implements LayoutProcessorInterface
{
    /**
     * @var string
     */
    private string $methodCode = Carrier::METHOD_CODE;

    /**
     * @var MapInterface
     */
    private MapInterface $map;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $retailerCollectionFactory;

    /**
     * @var Data
     */
    private Data $storeLocatorHelper;

    /**
     * @var AddressFormatter
     */
    private AddressFormatter $addressFormatter;

    /**
     * @var Schedule
     */
    private Schedule $scheduleHelper;

    /**
     * @var ScheduleManagement
     */
    private ScheduleManagement $scheduleManager;

    /**
     * @var CarrierFactoryInterface
     */
    private CarrierFactoryInterface $carrierFactory;

    /**
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * @var CacheInterface
     */
    private CacheInterface $cache;

    /**
     * Constructor.
     *
     * @param MapProviderInterface                $mapProvider               $mapProvider Map provider.
     * @param CollectionFactory                   $retailerCollectionFactory Retailer collection factory.
     * @param Data                                $storeLocatorHelper        Store locator helper.
     * @param AddressFormatter                    $addressFormatter          Address formatter tool.
     * @param Schedule                            $scheduleHelper            Schedule Helper
     * @param ScheduleManagement                  $scheduleManagement        Schedule Management
     * @param CarrierFactoryInterface             $carrierFactory            Carrier Factory
     * @param UrlInterface                        $urlBuilder                URL Builder
     * @param CacheInterface                      $cacheInterface            Cache Interface
     */
    public function __construct(
        MapProviderInterface                $mapProvider,
        CollectionFactory                   $retailerCollectionFactory,
        Data                                $storeLocatorHelper,
        AddressFormatter                    $addressFormatter,
        Schedule                            $scheduleHelper,
        ScheduleManagement                  $scheduleManagement,
        CarrierFactoryInterface             $carrierFactory,
        UrlInterface                        $urlBuilder,
        CacheInterface                      $cacheInterface
    ) {
        $this->map                       = $mapProvider->getMap();
        $this->retailerCollectionFactory = $retailerCollectionFactory;
        $this->storeLocatorHelper        = $storeLocatorHelper;
        $this->addressFormatter          = $addressFormatter;
        $this->scheduleHelper            = $scheduleHelper;
        $this->scheduleManager           = $scheduleManagement;
        $this->carrierFactory            = $carrierFactory;
        $this->urlBuilder                = $urlBuilder;
        $this->cache                     = $cacheInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function process($jsLayout): array
    {
        if ($this->carrierFactory->getIfActive($this->methodCode)) {
            // @codingStandardsIgnoreStart
            $storeDelivery = $jsLayout['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']['address-list']
            ['rendererTemplates']['store-delivery']['children']['smile-store-delivery']
            ['children']['store-delivery'];
            // @codingStandardsIgnoreEnd

            $storeDelivery['provider'] = $this->map->getIdentifier();
            $storeDelivery['markers']  = $this->getStores();
            $storeDelivery             = array_merge($storeDelivery, $this->map->getConfig());

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
            $geocoder             = array_merge($geocoder, $this->map->getConfig());

            // @codingStandardsIgnoreStart
            $jsLayout['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']['address-list']
            ['rendererTemplates']['store-delivery']['children']['smile-store-delivery']
            ['children']['store-delivery']['children']['geocoder'] = $geocoder;
            // @codingStandardsIgnoreEnd
        }

        return $jsLayout;
    }

    /**
     * List of markers displayed on the map.
     *
     * @return array
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
                $address    = $retailer->getExtensionAttributes()->getAddress();
                $coords     = $address->getCoordinates();
                $markerData = [
                    'id'           => $retailer->getId(),
                    'latitude'     => $coords->getLatitude(),
                    'longitude'    => $coords->getLongitude(),
                    'name'         => $retailer->getName(),
                    'address'      => $this->addressFormatter->formatAddress($address, AddressFormatter::FORMAT_ONELINE),
                    'url'          => $this->storeLocatorHelper->getRetailerUrl($retailer),
                    'directionUrl' => $this->map->getDirectionUrl($address->getCoordinates()),
                    'setStoreData' => $this->getSetStorePostData($retailer),
                    'addressData'  => $address->getData(),
                ];

                $markerData['schedule'] = array_merge(
                    $this->scheduleHelper->getConfig(),
                    [
                        'calendar'            => $this->scheduleManager->getCalendar($retailer),
                        'openingHours'        => $this->scheduleManager->getWeekOpeningHours($retailer),
                        'specialOpeningHours' => $retailer->getExtensionAttributes()->getSpecialOpeningHours(),
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
     *
     * @return Collection
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
     *
     * @param RetailerInterface $retailer The store
     *
     * @return array
     */
    private function getSetStorePostData($retailer): array
    {
        $setUrl   = $this->urlBuilder->getUrl('storelocator/store/set');
        $postData = ['id' => $retailer->getId()];

        return ['action' => $setUrl, 'data' => $postData];
    }
}
