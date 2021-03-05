<?php
use Mage_Core_Model_Resource as RC;
use Varien_Db_Adapter_Pdo_Mysql as MySQL;
use Varien_Db_Select as Select;
# 2019-11-07
final class Justuno_M1_DB {
	/**
	 * 2019-11-07
	 * @used-by select()
	 * @used-by Justuno_M1_Orders::stat()
	 * @used-by Justuno_M1_Response::store()
	 * @used-by app/code/community/Justuno/M1/sql/Justuno_M1/mysql4-install-1.4.3.php
	 * @return MySQL
	 */
	static function conn() {return self::res()->getConnection('write');}

	/**
	 * 2019-11-07
	 * @used-by Justuno_M1_Orders::stat()
	 * @used-by Justuno_M1_Response::store()
	 * @return Select
	 */
	static function select() {return self::conn()->select();}

	/**
	 * 2019-11-07
	 * @used-by Justuno_M1_Orders::stat()
	 * @used-by Justuno_M1_Response::store()
	 * @used-by app/code/community/Justuno/M1/sql/Justuno_M1/mysql4-install-1.4.3.php
	 * @param string $s
	 * @return string
	 */
	static function t($s) {return self::res()->getTableName($s);}

	/**
	 * 2019-11-07
	 * @used-by conn()
	 * @used-by t()
	 * @return RC
	 */
	private static function res() {static $r; return $r ?: new RC;}
}


