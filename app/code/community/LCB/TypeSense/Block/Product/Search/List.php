<?php
/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 * @copyright (c) 2023, LeftCurlyBracket
 */
class LCB_TypeSense_Block_Product_Search_List extends Mage_Catalog_Block_Product_List
{
    /**
     * Prepare and return saerch product collection with pagination support
     *
     * @deprecated in favor of LCB_TypeSense_Model_Layer
     * @return Mage_Catalog_Model_Resource_Product_Collection|Varien_Data_Collection
     */
    protected function _getProductCollection()
    {
        /** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getModel('catalog/product')->getCollection();

        $query = $this->getRequest()->getParam('q');
        $filters = $this->getRequest()->getParam('fq');
        $result = Mage::getSingleton('lcb_typesense/api')->searchIds($query, $filters);
        if (!$result['count']) {
            return new Varien_Data_Collection();
        }

        $collection->addFieldToFilter('entity_id', ['in' => $result['ids']]);
        $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());
        $collection->addStoreFilter()->addFinalPrice();
        $collection->addAttributeToSelect('*');
        $collection->setPageSize($this->getRequest()->getParam('limit', 12))->setCurPage($this->getRequest()->getParam('p', 1));

        return $collection;
    }

    /**
     * Allias for _getProductCollection
     *
     * @return type
     */
    public function getLoadedProductCollection()
    {
        return $this->_getProductCollection();
    }
}
