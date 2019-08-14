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
 * Class ET_IpSecurity_Model_System_Config_Source_Token_Expire
 */
class ET_IpSecurity_Model_System_Config_Source_Token_Expire
{
    const TOKEN_DISABLED_AFTER_3_DAYS = 3;
    const TOKEN_DISABLED_AFTER_5_DAYS = 5;
    const TOKEN_DISABLED_AFTER_10_DAYS = 10;

    public function toOptionArray()
    {
        $option = array();
        /** @var ET_IpSecurity_Helper_Data $helper */
        //$helper = Mage::helper('etipsecurity');

        $option[] = array(
            //'label' => '3 ' . $helper->__('days'),
            'label' => self::TOKEN_DISABLED_AFTER_3_DAYS,
            'value' => self::TOKEN_DISABLED_AFTER_3_DAYS
        );

        $option[] = array(
            //'label' => '5 ' . $helper->__('days'),
            'label' => self::TOKEN_DISABLED_AFTER_5_DAYS,
            'value' => self::TOKEN_DISABLED_AFTER_5_DAYS
        );

        $option[] = array(
            //'label' => '10 ' . $helper->__('days'),
            'label' => self::TOKEN_DISABLED_AFTER_10_DAYS,
            'value' => self::TOKEN_DISABLED_AFTER_10_DAYS
        );

        return $option;
    }

    /**
     * return timestamp(LastTimeUpdate + token time life)
     *
     * @return int
     */
    public function getTokenExpiredTimestamp()
    {
        /** @var ET_IpSecurity_Helper_Data $helper */
        $helper = Mage::helper('etipsecurity');
        $tokenTimeInDays = $helper->getTokenExpireTime();

        $tokenLastUpdate = $helper->getLastUpdateToken();

        if ($tokenLastUpdate) {
            $tokenLastUpdate = strtotime($tokenLastUpdate);
        }

        return $tokenLastUpdate + 60 * 60 * 24 * $tokenTimeInDays;
    }

    /**
     * @return bool
     */
    public function isTokenExpired()
    {
        if (time() > $this->getTokenExpiredTimestamp()) {
            return true;
        } else {
            return false;
        }
    }

}