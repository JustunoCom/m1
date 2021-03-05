<?php
# 2019-10-27
final class Justuno_M1_ResponseController extends Mage_Core_Controller_Front_Action {
	/**
	 * 2019-10-27
	 * /jumagext/response/catalog?pageSize=2&currentPage=1&sortProducts=entity_id&updatedSince=2014-01-01
	 * https://www.upwork.com/messages/rooms/room_e6b2d182b68bdb5e9bf343521534b1b6/story_2e22707221dd053eab677398848c8ea3
	 * 2020-01-15 https://support.justuno.com/ai-upsell-cross-sell-product-recommendations-plus-feeds
	 */
	function catalogAction() {Justuno_M1_Catalog::p();}

	/** 2020-05-06 "Implement an endpoint to return product quantities": https://github.com/justuno-com/m1/issues/45 */
	function inventoryAction() {Justuno_M1_Inventory::p();}

	/**
	 * 2019-10-27
	 * 2020-01-15 https://support.justuno.com/ai-upsell-cross-sell-product-recommendations-plus-feeds
	 */
	function ordersAction() {Justuno_M1_Orders::p();}
}