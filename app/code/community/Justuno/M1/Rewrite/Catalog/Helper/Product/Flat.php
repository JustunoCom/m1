<?php
/**
 * 2020-11-27
 * 1) "Disable the «Use Flat Catalog Product» option for the `jumagext/response/catalog` request":
 * https://github.com/justuno-com/m1/issues/50
 * 2) We can not use @see \Mage_Catalog_Helper_Product_Flat::disableFlatCollection()
 * because it exists only in Magento ≥ 1.9.4.0:
 * https://github.com/OpenMage/magento-mirror/blob/1.9.4.0/app/code/core/Mage/Catalog/Helper/Product/Flat.php#L175-L187
 * https://github.com/OpenMage/magento-mirror/blob/1.9.3.0/app/code/core/Mage/Catalog/Helper/Product/Flat.php
 */
final class Justuno_M1_Rewrite_Catalog_Helper_Product_Flat extends Mage_Catalog_Helper_Product_Flat {
	/**
	 * 2020-11-27
	 * @override
	 * @see Mage_Catalog_Helper_Product_Flat::isEnabled()
	 * @used-by Mage_Catalog_Model_Resource_Product_Collection::isEnabledFlat()
	 * @param int|string|null|Mage_Core_Model_Store $store
	 * @return bool
	 */
	function isEnabled($store = null) {return !self::$JU_DISABLE && parent::isEnabled($store);}

	/**
	 * 2020-11-27
	 * @used-by isEnabled()
	 * @used-by Justuno_M1_Catalog::p()
	 * @var bool
	 */
	static $JU_DISABLE;
}