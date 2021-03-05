<?php
use Exception as E;
use Justuno_M1_DB as DB;
use Justuno_M1_Lib as L;
use Mage_Adminhtml_Block_System_Config_Form as F;
use Mage_Core_Model_App as App;
use Mage_Core_Model_Store as S;
use Varien_Db_Select as Sel;
# 2019-10-30
final class Justuno_M1_Response {
	/**
	 * 2019-11-20
	 * @used-by Justuno_M1_CartController::addAction()
	 * @used-by Justuno_M1_Catalog::p()
	 * @used-by Justuno_M1_Inventory::p()
	 * @used-by Justuno_M1_Orders::p()
	 * @param \Closure $f
	 * @param bool $auth [optional]
	 */
	static function p(\Closure $f, $auth = true) {/** @var array(string => mixed) $r */
		try {$r = !$auth ? $f() : $f(self::store());}
		catch (\Exception $e) {$r = ['message' => $e->getMessage()];}
		self::res($r);
	}

	/**
	 * 2019-10-31
	 * @used-by Justuno_M1_Catalog::p()
	 * @param string $path
	 * @return string
	 */
	static function url($path) {return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, true) . $path;}

	/**
	 * 2019-10-30
	 * «if a property is null or an empty string do not send it back»: https://github.com/justuno-com/m1/issues/9
	 * @used-by filter()
	 * @used-by res()
	 * @param array(string => mixed) $a
	 * @return array(string => mixed)
	 */
	private static function filter(array $a) {
		$r = []; /** @var array(string => mixed) $r */
		foreach ($a as $k => $v) { /** @var string $k */ /** @var mixed $v */
			if (!in_array($v, ['', null], true)) {
				$r[$k] = !is_array($v) ? $v : self::filter($v);
			}
		}
		return $r;
	}

	/**
	 * 2019-10-31
	 * @used-by p()
	 * @param mixed|mixed[] $v
	 */
	private static function res($v) {
		$r = Mage::app()->getResponse(); /** @var Zend_Controller_Response_Http $r */
		$r->clearHeaders()->setHeader('Content-type','application/json', true);
		$r->setBody(L::json_encode(is_null($v) ? 'OK' : (!is_array($v) ? $v : self::filter($v))));
	}

	/**
	 * 2021-01-29 "Make the module multi-store aware": https://github.com/justuno-com/m1/issues/51
	 * @used-by p()
	 * @return S
	 * @throws E
	 */
	private static function store() {/** @var S $r */
		$app = Mage::app(); /** @var App $app */
		if (!($token = $app->getRequest()->getHeader('Authorization'))) { /** @var string|null $token */
			$r = isset($_SERVER['DF_DEVELOPER']) ? $app->getStore() : L::error('Please provide a valid token key');
		}
		else {
			$sel = DB::select()->from(DB::t('core_config_data'), ['scope', 'scope_id']); /** @var Sel $sel */
			$sel->where('? = path', 'justuno/justuno_settings/jutoken');
			$sel->where('? = value', $token);
			$w = function(array $a) {return L::tr(L::a($a, 'scope'), array_flip([
				F::SCOPE_STORES, F::SCOPE_WEBSITES, F::SCOPE_DEFAULT
			]));};
			/** @var array(string => string) $row */
			$row = L::first(L::sort(DB::conn()->fetchAll($sel), function(array $a, array $b) use($w) {return $w($a) - $w($b);}));
			L::assert($row, "The token $token is not registered in Magento.");
			$scope = L::a($row, 'scope'); /** @var string $scope */
			$scopeId = L::a($row, 'scope_id'); /** @var string $scopeId */
			$r = F::SCOPE_STORES === $scope ? $app->getStore($scopeId) : (
				F::SCOPE_DEFAULT === $scope ? $app->getStore() :
					$app->getWebsite($scopeId)->getDefaultStore()
			);
		}
		return $r;
	}
}