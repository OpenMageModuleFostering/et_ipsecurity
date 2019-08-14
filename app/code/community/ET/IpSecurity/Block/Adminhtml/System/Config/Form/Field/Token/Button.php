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
 * Class ET_IpSecurity_Block_Adminhtml_System_Config_Form_Field_Token_Button
 */
class ET_IpSecurity_Block_Adminhtml_System_Config_Form_Field_Token_Button
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /** @var  ET_IpSecurity_Helper_Data $_helper */
    protected $_helper;

    /**
     * @inheritdoc
     * ET_IpSecurity_Block_Adminhtml_System_Config_Form_Field_Token_Button constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->_helper = Mage::helper('etipsecurity');
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('et_ipsecurity/admin_config_generation_button.phtml');
        }
        return $this;
    }


    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        //1400 fix
        if (!($originalData = $element->getOriginalData())) {
            $originalData = array();
            foreach ($element->getData("field_config") as $key => $value) {
                if (!$value->hasChildren()) {
                    $originalData[$key] = (string)$value;
                }
            }
        }

        $this->addData(

            array(
                //'button_label' => $this->_helper->__($originalData['button_label']),

                'button_label' => $this->_helper->__('Generate token'),

                'comment' => $this->_helper->__($originalData['comment']),
                'html_id' => $element->getHtmlId(),
                'token_area' => $originalData["token_area"],

                'ajax_url' => Mage::getSingleton('adminhtml/url')
                    //->getUrl('adminhtml/etcountryblocker_update/addip',
                    ->getUrl('adminhtml/etipsecurity_token/generate',
                        array("token_area" => $originalData["token_area"])),


                
                'button_label_delete' => $this->_helper->__('Delete token'),

                'ajax_url_delete' => Mage::getSingleton('adminhtml/url')
                    //->getUrl('adminhtml/etcountryblocker_update/addip',
                    ->getUrl('adminhtml/etipsecurity_token/delete',
                        array("token_area" => $originalData["token_area"]))
            )

        );

        return $this->_toHtml();
    }


}