<?php
/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 * @copyright (c) 2024, LeftCurlyBracket
 */
class LCB_Typesense_Block_Adminhtml_System_Config_Verify extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        if (!class_exists('Typesense\Client')) {
            $message = $this->__('Typesense client was not found. Please install it first with composer require typesense/typesense-php');
            return '<div class="warning" style="color: red"><p>' . $message . '</p></div>';
        }

        return '';
    }
}
