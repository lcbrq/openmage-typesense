<?php
/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 * @copyright (c) 2024, LeftCurlyBracket
 */
class LCB_Typesense_Block_Adminhtml_System_Config_Field_Synonyms extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn('synonyms', array(
            'label' => Mage::helper('adminhtml')->__('Synonyms'),
            'style' => 'width:120px',
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add synonym');
        parent::__construct();
    }
}
