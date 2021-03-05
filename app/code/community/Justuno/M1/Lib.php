<?php
use Exception as E;
use Mage_Bundle_Model_Product_Type as ptBundle;
use Mage_Catalog_Model_Product as P;
use Mage_Catalog_Model_Product_Type_Abstract as ptAbstract;
use Mage_Catalog_Model_Product_Type_Configurable as ptConfigurable;
use Mage_Catalog_Model_Product_Type_Grouped as ptGrouped;
use Mage_Catalog_Model_Product_Type_Simple as ptSimple;
use Mage_Catalog_Model_Product_Type_Virtual as ptVirtual;
# 2020-01-15
final class Justuno_M1_Lib {
	/**
	 * 2021-01-29
	 * @used-by Justuno_M1_Response::store()
	 * @param array(int|string => mixed) $a
	 * @param string|string[]|int|null $k
	 * @param mixed $d
	 * @return mixed|null|array(string => mixed)
	 */
	static function a(array $a, $k, $d = null) {return is_null($k) ? $a : (isset($a[$k]) ? $a[$k] : $d);}

	/**
	 * 2020-01-21
	 * @used-by Justuno_M1_CartController::product()
	 * @used-by Justuno_M1_Response::store()
	 * @param mixed $cond
	 * @param null $m
	 * @return mixed
	 * @throws E
	 */
	static function assert($cond, $m = null) {return $cond ?: self::error($m);}

	/**
	 * 2020-03-13
	 * @used-by Justuno_M1_Catalog_Variants::variant()
	 * @param boolean $v
	 * @return string
	 */
	static function bts($v) {return $v ? 'true' : 'false';}

	/**
	 * 2020-09-29
	 * @used-by Justuno_M1_Catalog_Images::p()
	 * @param string $haystack
	 * @param string $needle
	 * @return bool
	 */
	static function contains($haystack, $needle) {return false !== strpos($haystack, $needle);}

	/**
	 * 2020-01-16 It formats $v as a value which can be used in the `var name = <?= df_ejs($v); ?>;` expression.
	 * @used-by js()
	 * @used-by app/design/frontend/base/default/template/justuno/m1.phtml
	 * @param mixed $v
	 * @return string
	 */
	static function ejs($v) {return !is_string($v) ? self::json_encode($v) : implode(
		str_replace("'", '\u0027', trim(json_encode($v), '"')), ["'", "'"]
	);}

	/**
	 * 2020-01-21
	 * @used-by assert()
	 * @used-by Justuno_M1_Response::store()
	 * @param string|string[]|mixed|E|null ...$m
	 * @throws E
	 */
	static function error(...$m) {throw $m instanceof E ? $m : new E(
		is_null($m) ? null : (is_array($m) ? implode("\n\n", $m) : sprintf(...$m))
	);}

	/**
	 * 2021-01-29
	 * @used-by \Justuno_M1_Response::store()
	 * @param array $a
	 * @return mixed|null
	 */
	static function first(array $a) {return !$a ? null : reset($a);}
	
	/**
	 * 2020-01-15
	 * @used-by app/design/frontend/base/default/template/justuno/m1.phtml
	 * @param string $k
	 * @param mixed $v
	 * @param bool $tag [optional]
	 * @return string
	 */
	static function js($k, $v, $tag = true) {
		$r = sprintf("window.$k = %s;", self::ejs($v)); /** @var string $r */
		return !$tag ? $r : "<script>$r</script>";
	}

	/**
	 * 2020-01-15
	 * @used-by ejs()
	 * @used-by Justuno_M1_Response::res()
	 * @param mixed $v
	 * @return string
	 */
	static function json_encode($v) {return json_encode($v,
		JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE
	);}

	/**
	 * 2021-01-27
	 * 1) In Magento 2, the \Magento\Catalog\Model\Product::getTypeInstance() method does not have arguments:
	 * https://github.com/magento/magento2/blob/2.0.0/app/code/Magento/Catalog/Model/Product.php#L628-L640
	 * It always returns a singleton:
	 * 1.1) \Magento\Catalog\Model\Product\Type::factory():
	 * https://github.com/magento/magento2/blob/2.0.0/app/code/Magento/Catalog/Model/Product/Type.php#L114-L135
	 * 1.2) \Magento\Catalog\Model\Product\Type\Pool::get()
	 * https://github.com/magento/magento2/blob/2.0.0/app/code/Magento/Catalog/Model/Product/Type/Pool.php#L31-L49
	 * 2) In Magento 1, the method has an optional $singleton argument with the default `false` value:
	 * @uses \Mage_Catalog_Model_Product::getTypeInstance()
	 * https://github.com/OpenMage/magento-mirror/blob/1.9.4.5/app/code/core/Mage/Catalog/Model/Product.php#L252-L275
	 * @used-by Justuno_M1_CartController::addAction()
	 * @used-by Justuno_M1_Catalog::p()
	 * @used-by Justuno_M1_Catalog_Variants::p()
	 * @used-by Justuno_M1_Inventory_Variants::p()
	 * @param P $p
	 * @return ptAbstract|ptBundle|ptConfigurable|ptGrouped|ptSimple|ptVirtual
	 */
	static function productTI(P $p) {return $p->getTypeInstance(true);}

	/**
	 * 2020-01-21
	 * @used-by reqI()
	 * @used-by Justuno_M1_Filter::byDate()
	 * @used-by Justuno_M1_Filter::byProduct()
	 * @used-by Justuno_M1_Filter::p()
	 * @param string $k
	 * @param mixed|null $d [optional]
	 * @return string
	 */
	static function req($k, $d = null) {return Mage::app()->getRequest()->getParam($k, $d);}

	/**
	 * 2020-01-21
	 * @used-by Justuno_M1_CartController::addAction()
	 * @used-by Justuno_M1_CartController::product()
	 * @used-by Justuno_M1_Filter::p()
	 * @param string $k
	 * @param mixed|null $d [optional]
	 * @return string
	 */
	static function reqI($k, $d = null) {return (int)self::req($k, $d);}

	/**
	 * 2021-01-29
	 * @used-by Justuno_M1_Response::store()
	 * @param array(int|string => mixed) $a
	 * @param \Closure|string|null $f [optional]
	 * @return array(int|string => mixed)
	 */
	static function sort(array $a, $f = null) {
		$isAssoc = self::is_assoc($a); /** @var bool $isAssoc */
		if (!$f) {
			$isAssoc ? asort($a) : sort($a);
		}
		else {
			if (!$f instanceof \Closure) {
				$m = $f ?: 'getId'; /** @var string $m */ /** @uses Mage_Core_Model_Abstract::getId() */
				$f = function($a, $b) use($m) {return !is_object($a) ? $a - $b : $a->$m() - $b->$m();};
			}
			/** @noinspection PhpUsageOfSilenceOperatorInspection */
			$isAssoc ? @uasort($a, $f) : @usort($a, $f);
		}
		return $a;
	}

	/**
	 * 2021-01-29
	 * @used-by Justuno_M1_Response::store()
	 * @param int|string $v
	 * @param array(int|string => mixed) $map
	 * @return int|string|mixed
	 */
	static function tr($v, array $map) {return self::a($map, $v, $v);}

	/**
	 * 2021-01-29
	 * @used-by Justuno_M1_Lib::sort()
	 * @param array(int|string => mixed) $a
	 * @return bool
	 */
	private static function is_assoc(array $a) {
		if (!($r = !$a)) { /** @var bool $r */
			foreach (array_keys($a) as $k => $v) {
				if ($k !== $v) {
					$r = true;
					break;
				}
			}
		}
		return $r;
	}
}