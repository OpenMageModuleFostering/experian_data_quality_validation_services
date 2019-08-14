<?php
    /* @var $installer Mage_Core_Model_Resource_Setup */
    $installer = $this; 

    $installer->startSetup();

    $addressValidationCustomerTable = $installer->getConnection()
        ->newTable($installer->getTable('addressvalidation/customer'))
        ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
            array(
                'auto_increment' => true,
                'indentify' => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true
            ), 'Id')
        ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
            array(
                'nullable'  => false,
                'unsigned'  => true,
                'default'   => '0'
            ), 'Entity Id')
        ->addColumn('matchtype', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
            array(
                'nullable'  => true,
                'default'   => null
            ), 'MatchType')
        ->addColumn('additionaldata', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
            array(
                'nullable'  => true,
                'default'   => null
            ), 'Additional Data')
        ->addColumn('timestamp', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, 
            array(
                'nullable'  => true,
                'default'   => null
            ), 'Timestamp')
            
        ->addIndex($installer->getIdxName('addressvalidation/customer', array('timestamp')), array('timestamp'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX))
        ->addForeignKey('FK_ADDRESSVALIADTION_CUSTOMER', 'entity_id', $installer->getTable('customer_address_entity'), 'entity_id',
                                        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
        ->setOption('type', 'INNODB')
        ->setOption('charset', 'utf8');

    $installer->getConnection()->createTable($addressValidationCustomerTable);

    $addressValidationQuoteTable = $installer->getConnection()
        ->newTable($installer->getTable('addressvalidation/quote'))
        ->addColumn('quote_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
            array(
                'auto_increment' => true,
                'indentify' => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true
            ), 'Id')
        ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10,
            array(
                'nullable'  => false,
                'unsigned'  => true,
                'default'   => '0'
            ), 'Entity Id')
        ->addColumn('address_type', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
            array(
                'nullable'  => true,
                'default'   => null
            ), 'Address Type')
        ->addColumn('matchtype', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
            array(
                'nullable'  => true,
                'default'   => null
            ), 'MatchType')
        ->addColumn('additionaldata', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255,
            array(
                'nullable'  => true,
                'default'   => null
            ), 'Additional Data')
        ->addColumn('timestamp', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, 
            array(
                'nullable'  => true,
                'default'   => null
            ), 'Timestamp')         
        ->addIndex($installer->getIdxName('addressvalidation/quote', array('timestamp')), array('timestamp'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX))
        ->addForeignKey('FK_ADDRESSVALIADTION_QUOTE', 'entity_id', $installer->getTable('sales_flat_quote'), 'entity_id',
                                        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
        ->setOption('type', 'INNODB')
        ->setOption('charset', 'utf8');

    $installer->getConnection()->createTable($addressValidationQuoteTable);
    
    $installer->endSetup();
