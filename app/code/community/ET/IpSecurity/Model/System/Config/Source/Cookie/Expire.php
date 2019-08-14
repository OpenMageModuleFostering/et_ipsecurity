<?php
/**
 * NOTICE OF LICENSE
 *
 * You may not sell, sub-license, rent or lease
 * any portion of the Software or Documentation to anyone.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @category   ET
 * @package    ET_IpSecurity
 * @copyright  Copyright (c) 2016 ET Web Solutions (http://etwebsolutions.com)
 * @contacts   support@etwebsolutions.com
 * @license    http://shop.etwebsolutions.com/etws-license-free-v1/   ETWS Free License (EFL1)
 */

/**
 * Class ET_IpSecurity_Model_System_Config_Source_Cookie_Expire
 */
class ET_IpSecurity_Model_System_Config_Source_Cookie_Expire
{
    const COOKIE_DISABLED_AFTER_1_HOUR = 1;
    const COOKIE_DISABLED_AFTER_24_HOUR = 24;

    public function toOptionArray()
    {
        $option = array();
        /** @var ET_IpSecurity_Helper_Data $helper */
        //$helper = Mage::helper('etipsecurity');

        $option[] = array(
            //'label' => '1 ' . $helper->__('hour'),
            'label' => self::COOKIE_DISABLED_AFTER_1_HOUR,
            'value' => self::COOKIE_DISABLED_AFTER_1_HOUR
        );

        $option[] = array(
            //'label' => '24 ' . $helper->__('hour'),
            'label' => self::COOKIE_DISABLED_AFTER_24_HOUR,
            'value' => self::COOKIE_DISABLED_AFTER_24_HOUR
        );

        return $option;
    }

    /**
     * return timestamp + cookie time life
     *
     * @return int
     */
    public function getCookieExpiredTime()
    {
        /** @var ET_IpSecurity_Helper_Data $helper */
        $helper = Mage::helper('etipsecurity');
        $cookieTimeInDays = $helper->getCookieExpireTime();
        return time() + 60 * 60 * $cookieTimeInDays;
    }

}