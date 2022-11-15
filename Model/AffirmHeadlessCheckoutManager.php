<?php

namespace FrontCommerce\HeadlessAffirm\Model;

use Astound\Affirm\Helper\FinancingProgram;
use Astound\Affirm\Logger\Logger;
use Astound\Affirm\Model\AffirmCheckoutManager;
use FrontCommerce\HeadlessAffirm\Api\AffirmHeadlessCheckoutManagerInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\ResourceModel\Quote\Payment\CollectionFactory;
use Magento\Sales\Model\Order\Payment;
use FrontCommerce\Integration\Api\Data\ConfigInterfaceFactory;

class AffirmHeadlessCheckoutManager extends AffirmCheckoutManager implements AffirmHeadlessCheckoutManagerInterface
{
    /**
     * @var ConfigInterfaceFactory
     */
    private $configFactory;
    /**
     * @var CollectionFactory
     */
    private $quotePaymentCollectionFactory;

    public function __construct(
        Session $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        ProductMetadataInterface $productMetadata,
        \Magento\Framework\Module\ResourceInterface $moduleResource,
        ObjectManagerInterface $objectManager,
        FinancingProgram $helper,
        \Astound\Affirm\Model\Config $affirmConfig,
        CollectionFactory $quotePaymentCollectionFactory,
        ConfigInterfaceFactory $configFactory,
        Logger $logger
    ) {
        parent::__construct(
            $checkoutSession,
            $quoteRepository,
            $productMetadata,
            $moduleResource,
            $objectManager,
            $helper,
            $affirmConfig,
            $logger
        );
        $this->quotePaymentCollectionFactory = $quotePaymentCollectionFactory;
        $this->configFactory = $configFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function initHeadlessCheckout($paymentId, $customerId = null)
    {
        $quote = $this->getQuoteFromPayment($paymentId);
        if (!$quote->getCustomerIsGuest() && $customerId !== $quote->getCustomerId()) {
            throw new AuthorizationException(__('You are not allowed to act upon this quote'));
        }

        $this->quote = $quote;
        return $this->initCheckout();
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicCheckoutConfigs()
    {
        // see \Astound\Affirm\Model\Ui\ConfigProvider::getConfig()
        return [
            $this->configFactory->create([
                'key' => 'apiKeyPublic',
                'value' => $this->affirmConfig->getPublicApiKey()
            ]),
            $this->configFactory->create([
                'key' => 'scriptUrl',
                'value' => $this->affirmConfig->getScript()
            ]),
        ];
    }

    private function getQuoteFromPayment(int $paymentId): CartInterface
    {
        /**
         * @var Payment $currentPayment
         */
        $quotePaymentCollection = $this->quotePaymentCollectionFactory->create();
        $idFieldName = $quotePaymentCollection->getResource()->getIdFieldName();
        $currentPayment = $quotePaymentCollection
            ->addFieldToFilter($idFieldName, $paymentId)
            ->getFirstItem();
        if (!$currentPayment->getId()) {
            throw new NotFoundException(__('Payment not found'));
        }

        return $this->quoteRepository->get($currentPayment->getQuoteId());
    }
}
