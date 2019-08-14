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
 * Class ET_IpSecurity_Block_Adminhtml_System_Config_Form_Field_Token_Update_Date
 */
class ET_IpSecurity_Block_Adminhtml_System_Config_Form_Field_Token_Update_Date
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * @inheritdoc
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $value = $element->getValue();
        if ($value) {
            $value = $this->formatDate($value, Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, true);
        } else {
            $value = Mage::helper('etipsecurity')->__(ET_IpSecurity_Helper_Data::MESSAGE_TOKEN_NOT_UPDATED);
        }
        return "<span id='etipsecurity_ipsecuritytoken_last_updated_date'>" . $value . "</span>";
    }
}