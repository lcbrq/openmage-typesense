<?php

class LCB_Typesense_Model_Resource_Catalog_Eav_Attribute extends Mage_Catalog_Model_Resource_Eav_Attribute
{
    /**
     * @return string
     */
    public function getTypesenseType()
    {
        $type = '';
        switch ($this->getBackendType()) {
            case 'text':
                $type = 'string';
                break;
            case 'decimal':
                $type = 'float';
                break;
            case 'varchar':
                $type = 'string';
                break;
            case 'static':
                $type = 'string';
                break;
            case 'int':
                $type = 'string';
                break;
        }

        return $type;
    }
}
