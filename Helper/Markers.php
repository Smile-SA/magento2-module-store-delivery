<?php

/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\StoreDelivery
 * @author    Remy LESCALLIER <remy.lescallier@smile.fr>
 * @copyright 2020 Smile
 */

declare(strict_types=1);

namespace Smile\StoreDelivery\Helper;

use Smile\Retailer\Api\Data\RetailerInterface;
use Smile\Retailer\Model\ResourceModel\Retailer\Collection as RetailerCollection;
use Smile\StoreLocator\Helper\Markers as BaseMarkersHelper;

/**
 * Class Markers
 *
 * @author    Remy Lescallier <remy.lescallier@smile.fr>
 * @copyright 2020 Smile
 */
class Markers extends BaseMarkersHelper
{
    const MARKERS_DATA_CACHE_KEY = 'checkout_storedelivery';
    const PROFILER_NAME = 'SmileStoreDelivery';
    const CACHE_TAG = 'smile_store_delivery_markers';

    /**
     * Get marker data (add addressData to marker)
     *
     * @param RetailerInterface $retailer   Retailer model
     * @param array             $attributes Attributes
     *
     * @return array
     */
    protected function getMarkerData(RetailerInterface $retailer, array $attributes)
    {
        $address    = $retailer->getExtensionAttributes()->getAddress();
        $markerData = parent::getMarkerData($retailer, $attributes);
        $markerData['addressData'] = $address->getData();

        return $markerData;
    }

    /**
     * Get retailer collection
     *
     * @param array $attributesToSelect Attributes to select
     *
     * @return RetailerCollection
     */
    protected function getRetailerCollection($attributesToSelect)
    {
        if (!in_array('seller_code', $attributesToSelect)) {
            $attributesToSelect[] = 'seller_code';
        }

        $retailerCollection = parent::getRetailerCollection($attributesToSelect);
        $retailerCollection->addFieldToFilter('allow_store_delivery', 1);

        return $retailerCollection;
    }
}
