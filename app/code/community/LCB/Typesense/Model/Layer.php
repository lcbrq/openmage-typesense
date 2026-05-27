<?php

/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 * @copyright (c) 2026, LeftCurlyBracket
 */
class LCB_Typesense_Model_Layer extends Mage_CatalogSearch_Model_Layer
{
    /**
     * Returns product collection for current query
     *
     * @return LCB_Typesense_Model_Layer
     */
    public function getProductCollection()
    {
        $query = Mage::helper('catalogsearch')->getQuery()->getQueryText();
        $filters = $this->getState()->getFilters();
        $result = Mage::getSingleton('lcb_typesense/api')->searchIds($query, $filters);

        /** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getModel('catalog/product')->getCollection();

        if (!$result['count']) {
            $collection->addFieldToFilter('entity_id', array('eq' => 0));
            return $collection;
        }

        $collection->addFieldToFilter('entity_id', ['in' => $result['ids']]);
        $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());
        $collection->addStoreFilter()->addPriceData();
        $collection->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes());

        return $collection;
    }
}
