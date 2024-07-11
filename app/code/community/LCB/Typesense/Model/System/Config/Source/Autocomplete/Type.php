<?php
/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 * @copyright (c) 2024, LeftCurlyBracket
 */
class LCB_Typesense_Model_System_Config_Source_Autocomplete_Type
{
    /**
     * @var string
     */
    public const BACKEND = 'backend';

    /**
     * @var string
     */
    public const FRONTEND = 'frontend';

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            self::BACKEND => Mage::helper('lcb_typesense')->__('Backend'),
            self::FRONTEND => Mage::helper('lcb_typesense')->__('Frontend'),
        ];
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::BACKEND,
                'label' => Mage::helper('lcb_typesense')->__('Backend'),
            ),
            array(
                'value' => self::FRONTEND,
                'label' => Mage::helper('lcb_typesense')->__('Frontend'),
            ),
        );
    }
}
