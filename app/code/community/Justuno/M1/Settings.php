<?php
# 2020-01-15
final class Justuno_M1_Settings {
	/**
	 * 2020-01-15
	 * @used-by app/design/frontend/base/default/template/justuno/m1.phtml
	 * @return string
	 */
	static function ajaxUrl() {return self::v('juajaxurl');}

	/**
	 * 2020-01-15
	 * @used-by Justuno_M1_Catalog::p()
	 * @return string
	 */
	static function brand() {return self::v('brand_attributure');}

	/**
	 * 2020-01-15 A string like «262BA7FF-9F54-4AC3-9812-6C236890785A»
	 * @used-by app/design/frontend/base/default/template/justuno/m1.phtml
	 * @return string
	 */
	static function id() {return self::v('accid');}

	/**
	 * 2020-01-15
	 * @used-by ajaxUrl()
	 * @used-by brand()
	 * @used-by id()
	 * @param string $k
	 * @return string
	 */
	private static function v($k) {return Mage::getStoreConfig("justuno/justuno_settings/$k");}
}