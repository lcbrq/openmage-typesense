<?php
/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 * @copyright (c) 2023, LeftCurlyBracket
 */
class LCB_TypeSense_Block_Searchbox extends Mage_Core_Block_Template
{
    /**
     * @return string
     */
    public function getResultUrl()
    {
        $this->getUrl('typesense');
    }
}
