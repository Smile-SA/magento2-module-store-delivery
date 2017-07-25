<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\StorePickup
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2017 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\StorePickup\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Shipping\Model\CarrierFactoryInterface;
use Smile\Map\Api\MapInterface;
use Smile\Map\Model\AddressFormatter;
use Smile\Retailer\Api\Data\RetailerInterface;
use Smile\Retailer\Model\ResourceModel\Retailer\CollectionFactory;

/**
 * Specific JS Layout processor for StorePickup.
 * Inject Map, Geolocation and Stores into checkout UI Components.
 *
 * @category Smile
 * @package  Smile\StorePickup
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class LayoutProcessor implements LayoutProcessorInterface
{
    /**
     * @var string
     */
    private $methodCode = \Smile\StorePickup\Model\Carrier::METHOD_CODE;

    /**
     * @var MapInterface
     */
    private $map;

    /**
     * @var CollectionFactory
     */
    private $retailerCollectionFactory;

    /**
     * @var \Smile\StoreLocator\Helper\Data
     */
    private $storeLocatorHelper;

    /**
     * @var \Smile\Map\Model\AddressFormatter
     */
    private $addressFormatter;

    /**
     * @var \Smile\StoreLocator\Helper\Schedule
     */
    private $scheduleHelper;

    /**
     * @var \Smile\StoreLocator\Model\Retailer\ScheduleManagement
     */
    private $scheduleManager;

    /**
     * @var \Magento\Shipping\Model\CarrierFactoryInterface
     */
    private $carrierFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    private $cache;

    /**
     * Constructor.
     *
     * @param \Smile\Map\Api\MapProviderInterface                            $mapProvider               Map provider.
     * @param \Smile\Retailer\Model\ResourceModel\Retailer\CollectionFactory $retailerCollectionFactory Retailer collection factory.
     * @param \Smile\StoreLocator\Helper\Data                                $storeLocatorHelper        Store locator helper.
     * @param \Smile\Map\Model\AddressFormatter                              $addressFormatter          Address formatter tool.
     * @param \Smile\StoreLocator\Helper\Schedule                            $scheduleHelper            Schedule Helper
     * @param \Smile\StoreLocator\Model\Retailer\ScheduleManagement          $scheduleManagement        Schedule Management
     * @param CarrierFactoryInterface                                        $carrierFactory            Carrier Factory
     * @param \Magento\Framework\UrlInterface                                $urlBuilder                URL Builder
     * @param \Magento\Framework\App\CacheInterface                          $cacheInterface            Cache Interface
     */
    public function __construct(
        \Smile\Map\Api\MapProviderInterface $mapProvider,
        \Smile\Retailer\Model\ResourceModel\Retailer\CollectionFactory $retailerCollectionFactory,
        \Smile\StoreLocator\Helper\Data $storeLocatorHelper,
        \Smile\Map\Model\AddressFormatter $addressFormatter,
        \Smile\StoreLocator\Helper\Schedule $scheduleHelper,
        \Smile\StoreLocator\Model\Retailer\ScheduleManagement $scheduleManagement,
        CarrierFactoryInterface $carrierFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\CacheInterface $cacheInterface
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
    public function process($jsLayout)
    {
        if ($this->carrierFactory->getIfActive($this->methodCode)) {
            // @codingStandardsIgnoreStart
            $storePickup = $jsLayout['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']['address-list']
            ['rendererTemplates']['store-pickup']['children']['smile-store-pickup']
            ['children']['store-pickup'];
            // @codingStandardsIgnoreEnd

            $storePickup['provider'] = $this->map->getIdentifier();
            $storePickup['markers']  = $this->getStores();
            $storePickup             = array_merge($storePickup, $this->map->getConfig());

            // @codingStandardsIgnoreStart
            $jsLayout['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']['address-list']
            ['rendererTemplates']['store-pickup']['children']['smile-store-pickup']
            ['children']['store-pickup'] = $storePickup;
            // @codingStandardsIgnoreEnd

            // @codingStandardsIgnoreStart
            $geocoder = $jsLayout['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']['address-list']
            ['rendererTemplates']['store-pickup']['children']['smile-store-pickup']
            ['children']['store-pickup']['children']['geocoder'];
            // @codingStandardsIgnoreEnd

            $geocoder['provider'] = $this->map->getIdentifier();
            $geocoder             = array_merge($geocoder, $this->map->getConfig());

            // @codingStandardsIgnoreStart
            $jsLayout['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']['address-list']
            ['rendererTemplates']['store-pickup']['children']['smile-store-pickup']
            ['children']['store-pickup']['children']['geocoder'] = $geocoder;
            // @codingStandardsIgnoreEnd
        }

        return $jsLayout;
    }

    /**
     * List of markers displayed on the map.
     *
     * @return array
     */
    private function getStores()
    {
        $collection = $this->getRetailerCollection();
        $cacheKey   = sprintf("%s_%s", 'checkout_storepickup', $collection->getStoreId());

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
                $retailer->getCacheTags(),
                7200
            );
        }

        return json_decode($markers);
    }

    /**
     * Collection of displayed retailers.
     *
     * @return \Smile\Retailer\Model\ResourceModel\Retailer\Collection
     */
    private function getRetailerCollection()
    {
        $retailerCollection = $this->retailerCollectionFactory->create();
        $retailerCollection->addAttributeToSelect('*');
        $retailerCollection->addOrder('name', 'asc');

        return $retailerCollection;
    }

    /**
     * Get the JSON post data used to build the set store link.
     *
     * @param \Smile\Retailer\Api\Data\RetailerInterface $retailer The store
     *
     * @return string
     */
    private function getSetStorePostData($retailer)
    {
        $setUrl   = $this->urlBuilder->getUrl('storelocator/store/set');
        $postData = ['id' => $retailer->getId()];

        return ['action' => $setUrl, 'data' => $postData];
    }
}
