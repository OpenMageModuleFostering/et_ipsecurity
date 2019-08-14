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
 * Class ET_IpSecurity_Block_Adminhtml_Token_Log_Grid
 */
class ET_IpSecurity_Block_Adminhtml_Token_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('etIpSecurityTokenLogGrid');
        $this->setDefaultSort('create_time');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare grid collection object
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        /** @var ET_IpSecurity_Model_Iptokenlog $model */
        $model = Mage::getModel('etipsecurity/iptokenlog');

        $collection = $model->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }


    /**
     * Prepare grid columns
     *
     * @return $this
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        /** @var ET_IpSecurity_Helper_Data $helper */
        $helper = Mage::helper('etipsecurity');


        $this->addColumn('create_time', array(
            'header' => $helper->__('Date'),
            'align' => 'left',
            'width' => '160px',
            'index' => 'create_time',
            'type' => 'datetime',
        ));

        $this->addColumn('last_block_rule', array(
            'header' => $helper->__('Event'),
            'align' => 'left',
            'width' => '300px',
            'index' => 'last_block_rule',
            'renderer' => 'etipsecurity/adminhtml_log_renderer_translaterule',
            'filter' => false,
        ));

        $this->addColumn('blocked_ip', array(
            'header' => $helper->__('IP'),
            'align' => 'left',
            'width' => '150px',
            'index' => 'blocked_ip',
        ));

        $this->addColumn('blocked_from', array(
            'header' => $helper->__('Url'),
            'align' => 'left',
            //'width'     => '100px',
            'index' => 'blocked_from',
        ));


        return parent::_prepareColumns();
    }




}