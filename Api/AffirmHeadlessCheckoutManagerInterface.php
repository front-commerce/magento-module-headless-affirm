<?php

namespace FrontCommerce\HeadlessAffirm\Api;

use Astound\Affirm\Api\AffirmCheckoutManagerInterface;

interface AffirmHeadlessCheckoutManagerInterface extends AffirmCheckoutManagerInterface
{
    /**
     * @param int $paymentId Payment id
     * @param string|null $customerId Customer id
     *
     * @return string
     */
    public function initHeadlessCheckout($paymentId, $customerId = null);

    /**
     * @return \FrontCommerce\Integration\Api\Data\ConfigInterface[]
     */
    public function getPublicCheckoutConfigs();
}
