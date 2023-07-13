<?php

declare(strict_types=1);

namespace Smile\StoreDelivery\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;

/**
 * Store Delivery Carrier model.
 */
class Carrier extends AbstractCarrier implements CarrierInterface
{
    public const METHOD_CODE = 'smilestoredelivery';

    // phpcs:disable SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
    protected $_code = self::METHOD_CODE;
    protected $_isFixed = true;
    // phpcs:enable

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        protected ResultFactory $rateResultFactory,
        protected MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @inheritdoc
     */
    public function getAllowedMethods()
    {
        return [$this->getCarrierCode() => __($this->getConfigData('name'))];
    }

    /**
     * @inheritdoc
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /** @var Result|DataObject $result */
        $result = $this->rateResultFactory->create();

        /** @var Method $method */
        $method = $this->rateMethodFactory->create();

        $method->setCarrier($this->getCarrierCode());
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->getCarrierCode());
        $method->setMethodTitle($this->getConfigData('name'));

        $amount = (float) $this->getConfigData('price');

        $price = $this->getFinalPriceWithHandlingFee($amount);

        $method->setPrice($price);
        $method->setCost($price);

        $result->append($method);

        return $result;
    }
}
