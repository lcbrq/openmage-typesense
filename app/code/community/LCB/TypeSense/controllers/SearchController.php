<?php
/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 * @copyright (c) 2023, LeftCurlyBracket
 */
class LCB_TypeSense_SearchController extends Mage_Core_Controller_Front_Action
{
    /**
     * @inheritDoc
     */
    public function indexAction()
    {
        Mage::register('current_layer', Mage::getSingleton('lcb_typesense/layer'));

        $this->loadLayout();
        $this->renderLayout();
    }
}
