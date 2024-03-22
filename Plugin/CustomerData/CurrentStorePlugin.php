<?php

declare(strict_types=1);

namespace Smile\StoreDelivery\Plugin\CustomerData;

use Magento\Framework\DataObject;
use Smile\Retailer\Api\Data\RetailerInterface;
use Smile\StoreLocator\CustomerData\CurrentStore;

class CurrentStorePlugin
{
    /**
     * Add current store allow_store_delivery value to customerData
     */
    public function afterGetSectionData(
        CurrentStore $subject,
        array $result
    ): array {
        $result['allow_store_delivery'] = 0;
        /** @var DataObject|RetailerInterface|null $retailer */
        $retailer = $subject->getRetailer();

        if ($retailer) {
            $result['allow_store_delivery'] = (int) $retailer->getData('allow_store_delivery');
        }

        return $result;
    }
}
