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
 * Class ET_IpSecurity_Adminhtml_Etipsecurity_TokenController
 */
class ET_IpSecurity_Adminhtml_Etipsecurity_TokenController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Action Delete Token
     */
    public function deleteAction()
    {
        /** @var ET_IpSecurity_Helper_Data $helper */
        $helper = Mage::helper('etipsecurity');

        $response = array(
            'frontUrl' => $helper->__(ET_IpSecurity_Helper_Data::MESSAGE_TOKEN_NOT_CREATED),
            'adminUrl' => $helper->__(ET_IpSecurity_Helper_Data::MESSAGE_TOKEN_NOT_CREATED),
            'date' => $helper->__(ET_IpSecurity_Helper_Data::MESSAGE_TOKEN_NOT_UPDATED)
        );

        $helper->resetTokenLinks();
        $helper->resetLastUpdateTokenTime();

        $body = Mage::helper('core')->jsonEncode($response);
        $this->getResponse()->setBody($body);
    }
    


    /**
     * action generate token
     */
    public function generateAction()
    {
        $response = array(
            'frontUrl' => '',
            'adminUrl' => '',
            'date' => ''
        );

        $value = $this->getRequest()->getParam('ipsecurity_token_name');

        if ($value != '') {
            /** @var ET_IpSecurity_Helper_Data $helper */
            $helper = Mage::helper('etipsecurity');
            
            $date = $helper->setLastUpdateToken();
            $date = Mage::helper('core')->formatDate($date, Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, true);
            
            $value = trim($value);

            $helper->setToken($value);

            $response['frontUrl'] = $helper->getFrontTokenUrl();
            $response['adminUrl'] = $helper->getAdminTokenUrl();

            $response['date'] = $date;
        }

        $body = Mage::helper('core')->jsonEncode($response);
        $this->getResponse()->setBody($body);
    }


    /**
     * check ACL
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config/etipsecurity');
    }
}