<?php
/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 * @copyright (c) 2024, LeftCurlyBracket
 */
class LCB_TypeSense_Model_Layer extends Mage_CatalogSearch_Model_Layer
{
    /**
     * Returns product collection for current query
     *
     * @return LCB_TypeSense_Model_Layer
     */
    public function getProductCollection()
    {
        $query = Mage::helper('catalogsearch')->getQuery()->getQueryText();

        $filters = [];
        $result = Mage::getSingleton('lcb_typesense/api')->searchIds($query, $filters);
        if (!$result['count']) {
            return new Varien_Data_Collection();
        }

        /** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addFieldToFilter('entity_id', ['in' => $result['ids']]);
        $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());
        $collection->addStoreFilter()->addFinalPrice();
        $collection->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes());

        return $collection;
    }
}
