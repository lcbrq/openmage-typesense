<?php
/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 * @copyright (c) 2023, LeftCurlyBracket
 */
class LCB_Typesense_SearchController extends Mage_Core_Controller_Front_Action
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

    /**
     * Perform AJAX search
     *
     * @return Mage_Core_Controller_Response_Http
     */
    public function ajaxAction()
    {
        $collection = Mage::getSingleton('lcb_typesense/layer')->getProductCollection();
        $attributes = Mage::getResourceModel('lcb_typesense/catalog_product_attribute_collection')->addSearchableAttributeFilter();
        $results = new Varien_Data_Collection();
        foreach ($collection as $product) {
            $result = new Varien_Object();
            foreach ($attributes as $attribute) {
                $code = $attribute->getAttributeCode();
                if ($attribute->getBackendType() === 'decimal') {
                    $result->setData($code, (float) $product->getData($code));
                } elseif (in_array($code, ['status', 'visibility'])) {
                    $result->setData($code, (int) $product->getData($code));
                } elseif ($attribute->getFrontendInput() === 'select') {
                    $result->setData($code, (string) $product->getAttributeText($code));
                } else {
                    $result->setData($code, (string) $product->getData($code));
                }
            }
            $results->addItem($result);
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        return $this->getResponse()->setBody(json_encode($results->toArray()));
    }
}
