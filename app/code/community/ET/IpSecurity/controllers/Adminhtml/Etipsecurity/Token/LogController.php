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
 * Class ET_IpSecurity_Adminhtml_Etipsecurity_Token_LogController
 */
class ET_IpSecurity_Adminhtml_Etipsecurity_Token_LogController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        /** @var ET_IpSecurity_Helper_Data $helper */
        $helper = Mage::helper('etipsecurity');

        $this->loadLayout()->_setActiveMenu('customers')->_addBreadcrumb(
            Mage::helper('adminhtml')->__('Customers'),
            $helper->__('ET IP Security Token log')
        );

        return $this;
    }


    /**
     * Default Action
     */
    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }
}