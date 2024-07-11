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

    /**
     * Perform AJAX search with adjusted output
     *
     * @return Mage_Core_Controller_Response_Http
     */
    public function algoliaAction()
    {
        $hits = [];
        $collection = Mage::getSingleton('lcb_typesense/layer')->getProductCollection();
        $attributes = Mage::getResourceModel('lcb_typesense/catalog_product_attribute_collection')->addSearchableAttributeFilter();
        foreach ($collection as $product) {
            $document = new Varien_Object();
            foreach ($attributes as $attribute) {
                $code = $attribute->getAttributeCode();
                if ($attribute->getBackendType() === 'decimal') {
                    $document->setData($code, (float) $product->getData($code));
                } elseif (in_array($code, ['status', 'visibility'])) {
                    $document->setData($code, (int) $product->getData($code));
                } elseif ($attribute->getFrontendInput() === 'select') {
                    $document->setData($code, (string) $product->getAttributeText($code));
                } else {
                    $document->setData($code, (string) $product->getData($code));
                }
            }

            $document->setData('request_path', $product->getRequestPath());
            $document->setData('thumbnail', $product->getThumbnail());

            $hits[] = [
                'text_match' => '',
                'highlights' => array_values([
                    'field' => '',
                    'snippet' => '',
                    'value' => '',
                ]),
                'document' => $document->toArray()
            ];
        }

        $result = [
            'found' => count($hits),
            'hits' => $hits,
        ];

        $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json');
        return $this->getResponse()->setBody(json_encode($result));
    }
}
