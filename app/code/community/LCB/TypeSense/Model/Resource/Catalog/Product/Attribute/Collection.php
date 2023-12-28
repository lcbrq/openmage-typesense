<?php

class LCB_TypeSense_Model_Resource_Catalog_Product_Attribute_Collection extends Mage_Catalog_Model_Resource_Product_Attribute_Collection
{
    /**
     * Resource model initialization
     *
     */
    protected function _construct()
    {
        $this->_init('lcb_typesense/resource_catalog_eav_attribute', 'eav/entity_attribute');
    }
}
