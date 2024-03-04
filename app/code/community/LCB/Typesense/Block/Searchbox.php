<?php
/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 * @copyright (c) 2024, LeftCurlyBracket
 */
class LCB_Typesense_Block_Searchbox extends Mage_Core_Block_Template
{
    /**
     * @return string
     */
    public function getResultUrl()
    {
        return $this->getUrl('typesense/search');
    }

    /**
     * @return bool
     */
    public function isAutocompleteEnabled()
    {
        return Mage::helper('lcb_typesense')->getAutocompleteEnabled();
    }
}
