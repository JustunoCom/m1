<?php
use DateTimeZone as TZ;
use Justuno_M1_Lib as L;
use Mage_Catalog_Model_Resource_Product_Collection as PC;
use Mage_Sales_Model_Resource_Order_Collection as OC;
use Varien_Data_Collection_Db as C;
# 2019-10-31
final class Justuno_M1_Filter {
	/**
	 * 2019-10-31
	 * @used-by Justuno_M1_Catalog::p()
	 * @used-by Justuno_M1_Orders::p()
	 * @param C|OC|PC $r
	 * @return OC|PC
	 */
	static function p(C $r) {
		self::byDate($r);
		self::byProduct($r);
		/** @var string $dir */ /** @var string $suffix */
		list($dir, $suffix) = $r instanceof PC ? ['DESC', 'Products'] : ['ASC', 'Orders'];
		if ($field = L::req("sort$suffix")) { /** @var string $field */
			$r->getSelect()->order("$field $dir");
		}
		# 2019-11-06
		# Fix the `offset` argument of the `Varien_Db_Select::limit()` call
		# from the `Justuno_M1_Filter::p()` method: https://github.com/justuno-com/m1/issues/34
		$size = L::reqI('pageSize', 10); /** @var int $size */
		$r->getSelect()->limit($size, $size * (L::reqI('currentPage', 1) - 1));
		return $r;
	}

	/**
	 * 2019-10-31
	 * @used-by p()
	 * @param C|OC|PC $c
	 */
	private static function byDate(C $c) {
		if ($since = L::req('updatedSince')) { /** @var string $since */
			/**
			 * 2021-03-24 "`updatedSince` should be interpreted in the UTC timezone": https://github.com/justuno-com/m1/issues/55
			 * 2021-03-27
			 * Using @see TZ::UTC leads to the error: «DateTimeZone::__construct(): Unknown or bad timezone (1024)»
			 * https://github.com/justuno-com/m1/issues/57
			 */
			$tz = new TZ('UTC'); /** @var TZ $tz */
			/**
			 * 2019-10-31
			 * @param string $s
			 * @return string
			 */
			$d = function($s) use($tz) {
				$f = 'Y-m-d H:i:s'; /** @var string $f */
				$dt = new DateTime(date($f, strtotime($s)), $tz);	/** @var DateTime $dt */
				return date($f, $dt->format('U'));
			};
			$c->addFieldToFilter('updated_at', ['from' => $d($since), 'to' => $d('2035-01-01 23:59:59')]);
		}
	}

	/**
	 * 2020-05-06
	 * "Provide an ability to filter the `jumagext/response/catalog` response by a concrete product":
	 * https://github.com/justuno-com/m1/issues/44
	 * @used-by p()
	 * @param C|OC|PC $c
	 */
	private static function byProduct(C $c) {
		if ($id = L::req('id')) { /** @var string $id */
			$c->addFieldToFilter('entity_id', $id);
		}
		if ($name = L::req('title')) { /** @var string $name */
			/**
			 * 2020-05-06
			 * @uses \Mage_Eav_Model_Entity_Collection_Abstract::addFieldToFilter()
			 * works even if the Flat Mode is disabled because it just delegates the work to
			 * @see \Mage_Eav_Model_Entity_Collection_Abstract::addAttributeToFilter():
			 *	public function addFieldToFilter($attribute, $condition = null) {
			 *		return $this->addAttributeToFilter($attribute, $condition);
			 *	}
			 * https://github.com/OpenMage/magento-mirror/blob/1.9.4.5/app/code/core/Mage/Eav/Model/Entity/Collection/Abstract.php#L333-L342
			 * https://github.com/OpenMage/magento-mirror/blob/1.4.0.0/app/code/core/Mage/Eav/Model/Entity/Collection/Abstract.php#L305-L314
			 */
			$c->addFieldToFilter('name', [['like' => "%$name%"]]);
		}
		if ($sku = L::req('sku')) { /** @var string $sku */
			$c->addFieldToFilter('sku', [['like' => "%$sku%"]]);
		}
	}
}