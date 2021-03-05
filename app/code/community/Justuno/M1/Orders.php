<?php
use Justuno_M1_DB as DB;
use Justuno_M1_Filter as Filter;
use Justuno_M1_OI as OIH;
use Justuno_M1_Response as R;
use Mage_Core_Model_Store as S;
use Mage_Customer_Model_Customer as C;
use Mage_Sales_Model_Order as O;
use Mage_Sales_Model_Order_Address as A;
use Mage_Sales_Model_Order_Item as OI;
use Mage_Sales_Model_Resource_Order_Collection as OC;
# 2019-10-31
final class Justuno_M1_Orders {
	/**
	 * 2019-10-31
	 * @used-by Justuno_M1_ResponseController::ordersAction()
	 */
	static function p() {R::p(function(S $s) {return array_values(array_map(function(O $o) {return [
		'CountryCode' => $o->getBillingAddress()->getCountryId()
		,'CreatedAt' => $o->getCreatedAt()
		,'Currency' => $o->getOrderCurrencyCode()
		# 2019-10-31
		# Orders: «if the customer checked out as a guest
		# we need still need a Customer object and it needs the ID to be a randomly generated UUID
		# or other random string»: https://github.com/justuno-com/m1/issues/30
		,'Customer' => self::customer($o)
		# 2019-10-31
		# Orders: «if the customer checked out as a guest
		# we need still need a Customer object and it needs the ID to be a randomly generated UUID
		# or other random string»: https://github.com/justuno-com/m1/issues/30
		,'CustomerId' => $o->getCustomerId() ?: $o->getCustomerEmail()
		,'Email' => $o->getCustomerEmail()
		,'ID' => $o->getIncrementId()
		,'IP' => $o->getRemoteIp()
		,'LineItems' => array_values(array_map(function(OI $i) {return [
			'OrderId' => $i->getOrderId()
			# 2019-10-31
			# Orders: «lineItem prices currently being returned in the orders feed are 0 always»:
			# https://github.com/justuno-com/m1/issues/31
			,'Price' => OIH::price($i)
			,'ProductId' => OIH::top($i)->getProductId()
			,'TotalDiscount' => (float)OIH::top($i)->getDiscountAmount()
			# 2019-10-31
			# Orders: «VariantID for lineItems is currently hardcoded as ''»: https://github.com/justuno-com/m1/issues/29
			,'VariantId' => $i->getProductId()
		];}, array_filter($o->getAllItems(), function(OI $i) {return !$i->getChildrenItems();})))
		,'OrderNumber' => $o->getId()
		,'ShippingPrice' => (float)$o->getShippingAmount()
		,'Status' => $o->getStatus()
		,'SubtotalPrice' => (float)$o->getSubtotal()
		,'TotalDiscounts' =>(float) $o->getDiscountAmount()
		,'TotalItems' => (int)$o->getTotalItemCount()
		,'TotalPrice' => (float)$o->getGrandTotal()
		,'TotalTax' => (float)$o->getTaxAmount()
		,'UpdatedAt' => $o->getUpdatedAt()
	# 2021-01-30 "Make the module multi-store aware": https://github.com/justuno-com/m1/issues/51
	];}, Filter::p(new OC)->addFieldToFilter('store_id', $s->getId())->getItems()));});}

	/**
	 * 2019-10-27
	 * 2019-10-31
	 * Orders: «if the customer checked out as a guest
	 * we need still need a Customer object and it needs the ID to be a randomly generated UUID
	 * or other random string»: https://github.com/justuno-com/m1/issues/30
	 * @used-by p()
	 * @param O $o
	 * @return array(string => mixed)
	 */
	private static function customer(O $o) {
		$c = new C; /** @var C $c */
		if ($o->getCustomerId()) {
			$c->load($o->getCustomerId());
		}
		$ba = $o->getBillingAddress(); /** @var A $ba */
		return [
			'Address1' => $ba->getStreet(1)
			,'Address2' => $ba->getStreet(2)
			,'City' => $ba->getCity()
			,'CountryCode' => $ba->getCountryId()
			,'CreatedAt' => $c['created_at']
			,'Email' => $o->getCustomerEmail()
			,'FirstName' => $o->getCustomerFirstname()
			# 2019-10-31
			# Orders: «if the customer checked out as a guest
			# we need still need a Customer object and it needs the ID to be a randomly generated UUID
			# or other random string»: https://github.com/justuno-com/m1/issues/30
			,'ID' => $o->getCustomerId() ?: $o->getCustomerEmail()
			,'LastName' => $o->getCustomerLastname()
			,'OrdersCount' => (int)self::stat($o, 'COUNT(*)')
			,'ProvinceCode' => $ba->getRegionCode()
			,'Tags' => ''
			,'TotalSpend' => (float)self::stat($o, 'SUM(grand_total)')
			,'UpdatedAt' => $c['updated_at']
			,'Zip' => $ba->getPostcode()
		];
	}

	/**
	 * 2019-11-07
	 * 2019-11-07
	 * 1) «Allowed memory size exausted» on `'OrdersCount' => $oc->count()`: https://github.com/justuno-com/m1/issues/36
	 * 2) I have replaced the customer collection with direct SQL queries.
	 * @used-by ordersCount()
	 * @used-by totalSpent()
	 * @param O $o
	 * @param string $v
	 * @return string
	 */
	private static function stat(O $o, $v) {
		$k = $o->getCustomerId() ? 'customer_id' : 'customer_email'; /** @var string $k */
		return DB::conn()->fetchOne(DB::select()->from(DB::t('sales_flat_order'), ['v' => $v])->where("? = $k", $o[$k]));
	}
}