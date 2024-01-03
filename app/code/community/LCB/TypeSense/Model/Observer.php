<?php
/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 * @copyright (c) 2023, LeftCurlyBracket
 */
class LCB_TypeSense_Model_Observer
{
    /**
     * @var string
     */
    public const ADMIN_SECTION_NAME = 'lcb_typesense';

    /**
     * Save Config After
     *
     * @param Varien_Event_Observer
     * @return void
     */
    public function saveConfigAfter($observer): void
    {
        $section = $observer->getSection();

        if ($section === self::ADMIN_SECTION_NAME) {
            try {
                $client = Mage::getModel('lcb_typesense/api')->createCollection();
            } catch (Exception $e) {
                Mage::helper('lcb_typesense')->log($e->getMessage());
            } catch (Error $e) {
                Mage::helper('lcb_typesense')->log($e->getMessage());
            }
        }
    }

    /**
     * Send product to TypeSense
     *
     * @param Varien_Event_Observer
     * @return void
     */
    public function saveProductAfter($observer): void
    {
        $product = $observer->getProduct();

        try {
            Mage::getModel('lcb_typesense/index')->reindex($product);
        } catch (Exception $e) {
            Mage::helper('lcb_typesense')->log($e->getMessage());
        } catch (Error $e) {
            Mage::helper('lcb_typesense')->log($e->getMessage());
        }
    }
}
