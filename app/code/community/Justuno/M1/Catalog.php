<?php
use Justuno_M1_Filter as Filter;
use Justuno_M1_Lib as L;
use Justuno_M1_Response as R;
use Justuno_M1_Settings as Ss;
use Mage_Catalog_Model_Category as C;
use Mage_Catalog_Model_Product as P;
use Mage_Catalog_Model_Product_Visibility as V;
use Mage_Catalog_Model_Resource_Category_Collection as CC;
use Mage_Catalog_Model_Resource_Product_Collection as PC;
use Mage_Core_Model_Store as S;
use Mage_Review_Model_Review_Summary as RS;
use Mage_Tag_Model_Resource_Tag_Collection as TC;
use Mage_Tag_Model_Tag as T;
# 2019-10-31
final class Justuno_M1_Catalog {
	/**
	 * 2019-10-31
	 * @used-by Justuno_M1_ResponseController::catalogAction()
	 */
	static function p() {R::p(function(S $s) {
		/**
		 * 2020-11-27
		 * 1) "Disable the «Use Flat Catalog Product» option for the `jumagext/response/catalog` request":
		 * https://github.com/justuno-com/m1/issues/50
		 * 2) We can not use @see \Mage_Catalog_Helper_Product_Flat::disableFlatCollection()
		 * because it exists only in Magento ≥ 1.9.4.0:
		 * https://github.com/OpenMage/magento-mirror/blob/1.9.4.0/app/code/core/Mage/Catalog/Helper/Product/Flat.php#L175-L187
		 * https://github.com/OpenMage/magento-mirror/blob/1.9.3.0/app/code/core/Mage/Catalog/Helper/Product/Flat.php
		 */
		Justuno_M1_Rewrite_Catalog_Helper_Product_Flat::$JU_DISABLE = true;
		$pc = new PC; /** @var PC $pc */
		$pc->addStoreFilter($s); # 2021-01-30 "Make the module multi-store aware": https://github.com/justuno-com/m1/issues/51
		$pc->addAttributeToSelect('*');
		/**
		 * 2019-10-30
		 * 1) «if a product has a Status of "Disabled" we'd still want it in the feed,
		 * but we'd want to set the inventoryquantity to -9999»:
		 * https://github.com/justuno-com/m1/issues/4
		 * 2) I do not use
		 * 		$pc->setVisibility([V::VISIBILITY_BOTH, V::VISIBILITY_IN_CATALOG, V::VISIBILITY_IN_SEARCH]);
		 * because it filters out disabled products.
		 */
		$pc->addAttributeToFilter('visibility', ['in' => [
			V::VISIBILITY_BOTH, V::VISIBILITY_IN_CATALOG, V::VISIBILITY_IN_SEARCH
		]]);
		$brand = Ss::brand(); /** @var string $brand */
		return array_values(array_map(function(P $p) use($brand) { /** @var array(string => mixed) $r */
			$rs = new RS; /** @var RS $rs */
			$rs->load($p->getId());
			$cc = $p->getCategoryCollection(); /** @var CC $cc */
			$r = [
				'Categories' => array_values(array_map(function(C $c) {return [
					'Description' => $c['description']
					# 2019-10-30
					# «json construct types are not correct for some values»:
					# https://github.com/justuno-com/m1/issues/8
					,'ID' => $c->getId()
					# 2019-10-30
					# «In Categories imageURL is being sent back as a boolean in some cases,
					# it should always be sent back as a string,
					# if there is not url just don't send the property back»:
					# https://github.com/justuno-com/m1/issues/12
					,'ImageURL' => $c->getImageUrl() ?: null
					,'Keywords' => $c['meta_keywords']
					,'Name' => $c->getName()
					,'URL' => $c->getUrl()
				];}, $cc->addAttributeToSelect('*')->addFieldToFilter('level', ['neq' => 1])->getItems()))
				,'CreatedAt' => $p['created_at']
				# 2019-10-30
				# «The parent ID is pulling the sku, it should be pulling the ID like the variant does»:
				# https://github.com/justuno-com/m1/issues/19
				,'ID' => $p->getId()
				# 2019-10-30
				# 1) «MSRP, Price, SalePrice, Variants.MSRP, and Variants.SalePrice all need to be Floats,
				# or if that is not possible then Ints»: https://github.com/justuno-com/m1/issues/10
				# 2) «If their isn't an MSRP for some reason just use the salesprice»:
				# https://github.com/justuno-com/m1/issues/6
				# 2019-10-31
				# «The MSRP should pull in this order MSRP > Price > Dynamic Price»: https://github.com/justuno-com/m1/issues/20
				# 2021-03-24 "Replace `getPrice` with `getFinalPrice`": https://github.com/justuno-com/m1/issues/54
				,'MSRP' => (float)($p['msrp'] ?: ($p['price'] ?: $p->getFinalPrice()))
				# 2019-10-30
				# «MSRP, Price, SalePrice, Variants.MSRP, and Variants.SalePrice all need to be Floats,
				# or if that is not possible then Ints»: https://github.com/justuno-com/m1/issues/10
				# 2019-10-31 «Price should be Price > Dynamic Price»: https://github.com/justuno-com/m1/issues/21
				# 2021-03-24 "Replace `getPrice` with `getFinalPrice`": https://github.com/justuno-com/m1/issues/54
				,'Price' => (float)($p['price'] ?: $p->getFinalPrice())
				# 2019-10-30 «ReviewsCount and ReviewSums need to be Ints»: https://github.com/justuno-com/m1/issues/11
				,'ReviewsCount' => (int)$rs->getReviewsCount()
				# 2019-10-30
				# 1) "Add the `ReviewsCount` and `ReviewsRatingSum` values to the `catalog` response":
				# https://github.com/justuno-com/m1/issues/15
				# 2) «ReviewsCount and ReviewSums need to be Ints»: https://github.com/justuno-com/m1/issues/11
				,'ReviewsRatingSum' => (int)$rs->getRatingSummary()
				# 2019-10-30
				# «MSRP, Price, SalePrice, Variants.MSRP, and Variants.SalePrice all need to be Floats,
				# or if that is not possible then Ints»: https://github.com/justuno-com/m1/issues/10
				# 2021-03-24 "Replace `getPrice` with `getFinalPrice`": https://github.com/justuno-com/m1/issues/54
				,'SalePrice' => (float)$p->getFinalPrice()
				,'Tags' => self::tags($p)
				,'Title' => $p['name']
				,'UpdatedAt' => $p['updated_at']
				# 2019-10-30 https://github.com/justuno-com/m1/issues/16
				,'URL' => R::url($p['url_path'] ?: $p['url_key'])
				/**
				 * 2019-10-30
				 * «if a product doesn't have parent/child like structure,
				 * I still need at least one variant in the Variants array»:
				 * https://github.com/justuno-com/m1/issues/5
				 */
				,'Variants' => Justuno_M1_Catalog_Variants::p($p)
			] + Justuno_M1_Catalog_Images::p($p);
			if ('configurable' === $p->getTypeId()) {
				$ct = L::productTI($p); /** @var Mage_Catalog_Model_Product_Type_Configurable $ct */
				$opts = array_column($ct->getConfigurableAttributesAsArray($p), 'attribute_code', 'id');
				/**
				 * 2019-10-30
				 * «within the ProductResponse and the Variants OptionType is being sent back as OptionType90, 91, etc...
				 * We need these sent back starting at OptionType1, OptionType2»:
				 * https://github.com/justuno-com/m1/issues/14
				 */
				foreach (array_values($opts) as $id => $code) {$id++; /** @var int $id */ /** @var string $code */
					$r["OptionType$id"] = $code;
				}
			}
			/**
			 * 2019-11-01
			 * If $brand is null, then @uses Mage_Catalog_Model_Product::getAttributeText() fails.
			 * https://www.upwork.com/messages/rooms/room_e6b2d182b68bdb5e9bf343521534b1b6/story_4e29dacff68f2d918eff2f28bb3d256c
			 */
			return $r + ['BrandId' => $brand, 'BrandName' => !$brand ? null : ($p->getAttributeText($brand) ?: null)];
		}, Filter::p($pc)->getItems()));
	});}

	/**
	 * 2019-10-27
	 * @used-by catalogAction()
	 * @param P $p
	 * @return array(array(string => int|string))
	 */
	private static function tags(P $p) {
		$tc = new TC; /** @var TC $tc */
		$tc->addPopularity();
		$tc->addStatusFilter(T::STATUS_APPROVED);
		$tc->addProductFilter($p->getId());
		$tc->setFlag('relation', true);
		$tc->setActiveFilter();
		return array_values(array_map(function(T $t) {return [
			'ID' => $t->getId(), 'Name' => $t->getName()
		];}, $tc->getItems()));
	}
}