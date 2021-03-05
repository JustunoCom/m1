<?php
use Justuno_M1_Response as R;
use Mage_Catalog_Model_Product as P;
use Mage_Catalog_Model_Product_Visibility as V;
use Mage_Catalog_Model_Resource_Product_Collection as PC;
use Mage_Core_Model_Store as S;
# 2020-05-06 "Implement an endpoint to return product quantities": https://github.com/justuno-com/m1/issues/45
final class Justuno_M1_Inventory {
	/**
	 * 2020-05-06
	 * @used-by Justuno_M1_ResponseController::inventoryAction()
	 */
	static function p() {R::p(function(S $s) {
		$pc = new PC; /** @var PC $pc */
		$pc->addStoreFilter($s); # 2021-01-30 "Make the module multi-store aware": https://github.com/justuno-com/m1/issues/51
		/**
		 * 2020-05-06
		 * 1) «We don't want to include products that have been disabled or have only disabled variants»:
		 * https://github.com/justuno-com/m2/issues/13#issue-612869130
		 * 2) @uses Mage_Catalog_Model_Resource_Product_Collection::setVisibility()
		 * filters out the disabled products.
		 */
		$pc->setVisibility([V::VISIBILITY_BOTH, V::VISIBILITY_IN_CATALOG, V::VISIBILITY_IN_SEARCH]);
		return array_values(array_map(function(P $p) {return [
			'ID' => $p->getId(), 'Variants' => Justuno_M1_Inventory_Variants::p($p)
		];}, $pc->getItems()));
	});}
}