<?php
use Justuno_M1_DB as DB;
use Varien_Db_Adapter_Pdo_Mysql as C;
/** @var Mage_Core_Model_Resource_Setup $this */
$t_catalog_product_entity = DB::t('catalog_product_entity');
$t_catalog_product_super_link = DB::t('catalog_product_super_link');
$t_cataloginventory_stock_status = DB::t('cataloginventory_stock_status');
$c = DB::conn(); /** @var C $c */
foreach (['insert', 'update'] as $i) {/** @var string $i */
	$name = "justuno__cataloginventory_stock_status__$i"; /** @var string $name */
	# 2020-08-27
	# «This version of MariaDB doesn't yet support 'multiple triggers with the same action time and event for one table»:
	# https://github.com/justuno-com/m2/issues/15
	$c->query("DROP TRIGGER IF EXISTS $name;");
	# 2021-03-05
	# «trigger for updating catalog_product_entity running very slow for large catalog»:
	# https://github.com/justuno-com/m1/issues/52
	$c->query("
		CREATE TRIGGER $name AFTER $i ON $t_cataloginventory_stock_status
		FOR EACH ROW BEGIN
			UPDATE $t_catalog_product_entity
				SET updated_at = CURRENT_TIMESTAMP()
				WHERE entity_id = NEW.product_id
			;
			UPDATE $t_catalog_product_super_link l
				INNER JOIN $t_catalog_product_entity e ON (e.entity_id = l.parent_id)
				SET e.updated_at = CURRENT_TIMESTAMP()
				WHERE l.product_id = NEW.product_id
			;
		END
	");
}