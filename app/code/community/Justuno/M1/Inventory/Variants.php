<?php
use Justuno_M1_Lib as L;
use Mage_Catalog_Model_Product as P;
use Mage_CatalogInventory_Model_Stock_Item as SI;
# 2020-05-06 "Implement an endpoint to return product quantities": https://github.com/justuno-com/m1/issues/45
final class Justuno_M1_Inventory_Variants {
	/**
	 * 2020-05-06
	 * @used-by Justuno_M1_ResponseController::inventoryAction()
	 * @param P $p
	 * @return array(array(string => mixed))
	 */
	static function p(P $p) { /** @var array(array(string => mixed)) $r */
		if ('configurable' !== $p->getTypeId()) {
			# 2019-30-31
			# "Products: some Variants are objects instead of arrays of objects": https://github.com/justuno-com/m1/issues/32
			$r = [self::variant($p)];
		}
		else {
			$ct = L::productTI($p); /** @var Mage_Catalog_Model_Product_Type_Configurable $ct */
			/**
			 * 2020-05-06
			 * 1) «We would only want records for Products where the product and at least one of its variants are active.
			 * We don't want to include products that have been disabled or have only disabled variants»:
			 * https://github.com/justuno-com/m2/issues/13#issue-612869130
			 * 2) @see Mage_Catalog_Model_Product_Type_Configurable::getUsedProducts()
			 * does not filter the disabled products:
			 * https://github.com/OpenMage/magento-mirror/blob/1.9.4.5/app/code/core/Mage/Catalog/Model/Product/Type/Configurable.php#L323-L374
			 * https://github.com/OpenMage/magento-mirror/blob/1.4.0.0/app/code/core/Mage/Catalog/Model/Product/Type/Configurable.php#L307-L347
			 */
			$r = !($ch = array_filter($ct->getUsedProducts(null, $p), function(P $p) {return !$p->isDisabled();}))
				? [self::variant($p)] : array_values(array_map(function(P $c) {return self::variant($c);}, $ch))
			;
		}
		return $r;
	}

	/**
	 * 2020-05-06
	 * @used-by p()
	 * @param P $p
	 * @return array(string => mixed)
	 */
	private static function variant(P $p) {
		$si = new SI; /** @var SI $si */
		$si->loadByProduct($p);
		return ['ID' => $p->getId(), 'Quantity' => (int)$si->getQty()];
	}
}