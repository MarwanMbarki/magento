<?php
/*
 * ---------------------------------------------------------------------------------
 *  PARACHUTE STORE DELIVERY MODULE
 * ---------------------------------------------------------------------------------
 * http://thisisparachute.com
 * Copyright Parachute Digital Solutions 2017. All rights reserved.
 * ---------------------------------------------------------------------------------
 *
 * ============
 *  CODE HINTS
 * ============
 * Model Naming Convention Reference: https://code.tutsplus.com/tutorials/magento-custom-module-development--cms-20643
 *
 * Pattern: <Namespace>_<Module Name>_<"Model" Keyword>_<Model Name>
 * Underscores represent a directory path to Magento, i.e: Parachute/StoreDelivery/Model/StoreDelivery.php
 *
 * (Model location defined elsewhere in your module config.xml)
 *
 * Tips on shipping carrier modules here: http://inchoo.net/magento/custom-shipping-method-in-magento/
 * 
 * - Ross@Parachute
 */

class Parachute_SmartShipping_Model_Carrier 
	extends Mage_Shipping_Model_Carrier_Abstract 
	implements Mage_Shipping_Model_Carrier_Interface
{
	protected $_code = 'parachute_smartshipping';

	/* Required for a custom carrier */
	public function getAllowedMethods()
	{
		return array(
			'standard' => 'Standard Delivery',
			'express' => 'Express Delivery'
		);
	}

	/* Required for a custom carrier */
	public function collectRates(Mage_Shipping_Model_Rate_Request $request)
	{
		/** @var Mage_Shipping_model_Rate_Result $result **/
		$result = Mage::getModel('shipping/rate_result');

		$contains_backorder = false;
		$standard_available = true;
		$express_available = true;

		/* Get the cart grand total without shipping charges so we can decide which shipping tier this order is eligible for */
		$quote = Mage::getModel('checkout/session')->getQuote();
		$grand_total = $quote->getGrandTotal();
		$cart_items = $quote->getAllItems();

		/* Loop through the items in our cart to determine whether or not there are any backorders present if backorders are enabled */
		if(intval(Mage::getStoreConfig('cataloginventory/item_options/backorders')) == true){
			foreach($cart_items as $item){
				$id = $item->getProductId();
				$product = Mage::getModel('catalog/product')->load($id);
				$stock_item = $product->getStockItem();

				if($stock_item->getQty() < 1) $contains_backorder = true;
			}
		}

		$tier = $this->getShippingTier($grand_total, $contains_backorder);

		if(!isset($tier['standard']['active']) || $tier['standard']['active'] != true) $standard_available = false;
		if(!isset($tier['express']['active']) || $tier['express']['active'] != true) $express_available = false;

		/* Get the rates for our express delivery if available */
		if($express_available){
			$result->append($this->_getExpressRate($tier, $contains_backorder));
		}

		/* Get the rates for standard delivery */
		if($standard_available){
			$result->append($this->_getStandardRate($tier, $contains_backorder));
		}

		return $result;
	}

	protected function getShippingTier($grand_total, $contains_backorder = false)
	{
		$helper = Mage::helper('smartshipping');
		$tiers = $helper->getAllPricingTiers();
		$shipping_tier = array();

		/* If this order contains ANY backorders then only use the backorder shipping tier */
		if(
			$contains_backorder == true && 
			isset($tiers['backorder_shipping']) && 
			$grand_total >= floatval($tiers['backorder_shipping']['range']['min']) && 
			floatval($grand_total <= $tiers['backorder_shipping']['range']['max'])
		){
			$shipping_tier = $tiers['backorder_shipping'];
			return $shipping_tier;
		}

		/* Loop through our pricing tiers until we find the relevant tier for our cart total */
		foreach($tiers as $tier){
			if( $grand_total >= floatval($tier['range']['min']) && floatval($grand_total <= $tier['range']['max']) ){
				$shipping_tier = $tier;
				break;
			}
		}

		return $shipping_tier;
	}

	protected function _getStandardRate($tier, $contains_backorder = false)
	{
	    /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
	    $rate = Mage::getModel('shipping/rate_result_method');
	    $rate->setCarrier($this->_code);
	    $rate->setCarrierTitle($this->getConfigData('title'));
	    $rate->setMethod('standard');
	    $rate->setMethodTitle('Standard Delivery' . ($contains_backorder ? ' [Backorder]' : '') . ' (' . $tier['standard']['time'] . ') -');
	    $rate->setPrice( floatval($tier['standard']['rate']) );
	    $rate->setCost(0);
	 
	    return $rate;
	}

	protected function _getExpressRate($tier, $contains_backorder = false)
	{
	    /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
	    $rate = Mage::getModel('shipping/rate_result_method');
	 
	    $rate->setCarrier($this->_code);
	    $rate->setCarrierTitle($this->getConfigData('title'));
	    $rate->setMethod('express');
	    $rate->setMethodTitle('Express Delivery' . ($contains_backorder ? ' [Backorder]' : '') . ' (' . $tier['express']['time'] . ') -');
	    $rate->setPrice( floatval($tier['express']['rate']) );
	    $rate->setCost(0);
	 
	    return $rate;
	}
}