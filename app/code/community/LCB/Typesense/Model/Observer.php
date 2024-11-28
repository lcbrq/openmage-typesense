<?php
/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 * @copyright (c) 2023, LeftCurlyBracket
 */
class LCB_Typesense_Model_Observer
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
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::helper('lcb_typesense')->log($e->getMessage());
            } catch (Error $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::helper('lcb_typesense')->log($e->getMessage());
            }
        }
    }

    /**
     * Update Typesense schema
     *
     * @param Varien_Event_Observer
     * @return void
     */
    public function saveAttributeAfter($observer): void
    {
        $attribute = $observer->getDataObject();

        try {
            $attributeHasChanged = $attribute->getIsSearchable() !== $attribute->getOrigData('is_searchable')
                || $attribute->getIsFilterableInSearch() !== $attribute->getOrigData('is_filterable_in_search');
            if ($attributeHasChanged) {
                Mage::getModel('lcb_typesense/attribute_api')->update($attribute);
            }
        } catch (Exception $e) {
            Mage::logException($e);
        } catch (Error $e) {
            Mage::logException($e);
        }
    }

    /**
     * Send product to Typesense
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

    /**
     * Delete product in Typesense
     *
     * @param Varien_Event_Observer
     * @return void
     */
    public function deleteProductAfter($observer): void
    {
        $product = $observer->getProduct();

        try {
            Mage::getModel('lcb_typesense/index')->delete($product->getId());
        } catch (Exception $e) {
            Mage::helper('lcb_typesense')->log($e->getMessage());
        } catch (Error $e) {
            Mage::helper('lcb_typesense')->log($e->getMessage());
        }
    }

    /**
     * Redirect standard search action
     *
     * @param Varien_Event_Observer
     * @return void
     */
    public function redirectOnCatalogSearch($observer): void
    {
        if (Mage::helper('lcb_typesense')->getRedirectCatalogSearch()) {
            $controller = $observer->getEvent()->getControllerAction();
            $request = $controller->getRequest();
            $queryParams = $request->getQuery();

            $typesenseUrl = Mage::getUrl('typesense');
            $queryString = http_build_query($queryParams);
            $redirectUrl = $typesenseUrl . '?' . $queryString;

            $controller->getResponse()
                ->setRedirect($redirectUrl)
                ->sendResponse();
        }
    }
}
