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

    /**
     * @return string
     */
    public function getAutocompleteUrl()
    {
        if (Mage::helper('lcb_typesense')->getAutocompleteType() === LCB_Typesense_Model_System_Config_Source_Autocomplete_Type::BACKEND) {
            return str_replace(['http://', 'https://'], '', $this->getUrl('lcb_typesense/search/algolia'));
        }

        return Mage::helper('lcb_typesense')->getHost();
    }

    /**
     * @return string
     */
    public function getAutocompleteProtocol()
    {
        if (Mage::helper('lcb_typesense')->getAutocompleteType() === LCB_Typesense_Model_System_Config_Source_Autocomplete_Type::BACKEND) {
            if (strpos($this->getUrl(), 'http:') === 0) {
                return 'http';
            }
        }

        return 'https';
    }
}
