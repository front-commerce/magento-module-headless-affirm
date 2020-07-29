<?php
if (class_exists("\Astound\Affirm\Api\AffirmCheckoutManagerInterface")) {
    \Magento\Framework\Component\ComponentRegistrar::register(
        \Magento\Framework\Component\ComponentRegistrar::MODULE,
        'FrontCommerce_HeadlessAffirm',
        __DIR__
    );
}
