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
use Magento\Shipping\Model\CarrierFactoryInterface;
use Smile\Map\Api\MapInterface;
use Smile\Map\Api\MapProviderInterface;
use Smile\Retailer\Model\ResourceModel\Retailer\CollectionFactory;
use Smile\StoreDelivery\Helper\Markers as MarkersHelper;
use Smile\StoreDelivery\Model\Carrier;

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
    private $methodCode = Carrier::METHOD_CODE;

    /**
     * @var MapInterface
     */
    private $map;

    /**
     * @var CarrierFactoryInterface
     */
    private $carrierFactory;

    /**
     * @var MarkersHelper
     */
    private $markersHelper;

    /**
     * @var array
     */
    private $attributesToSelect;

    /**
     * Constructor.
     *
     * @param MapProviderInterface    $mapProvider                  Map provider.
     * @param CarrierFactoryInterface $carrierFactory               Carrier Factory
     * @param MarkersHelper           $markersHelper                Markers helper
     * @param array                   $additionalAttributesToSelect Retailer additional attributes to select
     */
    public function __construct(
        MapProviderInterface $mapProvider,
        CarrierFactoryInterface $carrierFactory,
        MarkersHelper $markersHelper,
        array $additionalAttributesToSelect = []
    ) {
        $this->map                = $mapProvider->getMap();
        $this->carrierFactory     = $carrierFactory;
        $this->markersHelper      = $markersHelper;
        $this->attributesToSelect = $additionalAttributesToSelect;
    }

    /**
     * {@inheritdoc}
     */
    public function process($jsLayout)
    {
        if ($this->carrierFactory->getIfActive($this->methodCode)) {
            // @codingStandardsIgnoreStart
            $storeDelivery = $jsLayout['components']['checkout']['children']['steps']['children']
                             ['shipping-step']['children']['shippingAddress']['children']['address-list']
                             ['rendererTemplates']['store-delivery']['children']['smile-store-delivery']
                             ['children']['store-delivery'];
            // @codingStandardsIgnoreEnd

            $storeDelivery['provider'] = $this->map->getIdentifier();
            $storeDelivery['markers'] = $this->markersHelper->getMarkersData($this->attributesToSelect);
            $storeDelivery = array_merge($storeDelivery, $this->map->getConfig());

            // @codingStandardsIgnoreStart
            $jsLayout['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']['address-list']
            ['rendererTemplates']['store-delivery']['children']['smile-store-delivery']
            ['children']['store-delivery']
                = $storeDelivery;
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
            ['children']['store-delivery']['children']['geocoder']
                = $geocoder;
            // @codingStandardsIgnoreEnd
        }

        return $jsLayout;
    }
}
