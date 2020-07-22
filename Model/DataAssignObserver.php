<?php

namespace FrontCommerce\HeadlessAffirm\Model;

use Astound\Affirm\Gateway\Command\CaptureStrategyCommand;
use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * @see https://devdocs.magento.com/guides/v2.4/payments-integrations/base-integration/get-payment-info.html#read-additional-data
 */
class DataAssignObserver extends AbstractDataAssignObserver
{
    /**
     * @var array
     */
    protected $additionalInformationList = [
        CaptureStrategyCommand::CHECKOUT_TOKEN
    ];

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_array($additionalData)) {
            return;
        }

        $paymentInfo = $this->readPaymentModelArgument($observer);

        foreach ($this->additionalInformationList as $additionalInformationKey) {
            if (isset($additionalData[$additionalInformationKey])) {
                $paymentInfo->setAdditionalInformation(
                    $additionalInformationKey,
                    $additionalData[$additionalInformationKey]
                );
            }
        }
    }
}
