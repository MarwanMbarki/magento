<?php
/*
 * ---------------------------------------------------------------------------------
 *  PARACHUTE SMART SHIPPING MODULE
 * ---------------------------------------------------------------------------------
 * http://thisisparachute.com
 * Copyright Parachute Digital Solutions 2018. All rights reserved.
 * ---------------------------------------------------------------------------------
 *
 * ============
 *  CODE HINTS
 * ============
 * Model Naming Convention Reference: https://code.tutsplus.com/tutorials/magento-custom-module-development--cms-20643
 *
 * Pattern: <Namespace>_<Module Name>_<"Helper" Keyword>_<Helper Name>
 * Underscores represent a directory path to Magento, i.e: Parachute/StoreDelivery/Helper/Data.php
 *
 * (Helper location defined elsewhere in your module config.xml)
 *
 * Tips on module helpers here: http://inchoo.net/magento/custom-shipping-method-in-magento/
 * and: https://alanstorm.com/custom_magento_system_configuration/
 *
 * - Ross@Parachute
 */

class Parachute_SmartShipping_Helper_Data
	extends Mage_Core_Helper_Abstract
{
	/* config.xml default value URIs */
    const XML_SHPNG = 'carriers/parachute_smartshipping';
	const XML_SHPNG_ACTIVE = self::XML_SHPNG . '/active';

	/* Shipping Price Tiers */
	const XML_SHPNG_TR_ROUTE = self::XML_SHPNG . '/shipping_price_tiers';

	const XML_SHPNG_TR_ONE_MIN = XML_SHPNG_TR_ROUTE . '/shipping_tier_one/range/min';
	const XML_SHPNG_TR_ONE_MAX = XML_SHPNG_TR_ROUTE . '/shipping_tier_one/range/max';
    const XML_SHPNG_TR_ONE_STD_RATE = XML_SHPNG_TR_ROUTE . '/shipping_tier_one/standard/rate';
    const XML_SHPNG_TR_ONE_STD_TIME = XML_SHPNG_TR_ROUTE . '/shipping_tier_one/standard/time';
	const XML_SHPNG_TR_ONE_STD_ACTIVE = XML_SHPNG_TR_ROUTE . '/shipping_tier_one/standard/active';
    const XML_SHPNG_TR_ONE_EXP_RATE = XML_SHPNG_TR_ROUTE . '/shipping_tier_one/express/rate';
    const XML_SHPNG_TR_ONE_EXP_TIME = XML_SHPNG_TR_ROUTE . '/shipping_tier_one/express/time';
	const XML_SHPNG_TR_ONE_EXP_ACTIVE = XML_SHPNG_TR_ROUTE . '/shipping_tier_one/express/active';

	const XML_SHPNG_TR_TWO_MIN = XML_SHPNG_TR_ROUTE . '/shipping_tier_two/range/min';
	const XML_SHPNG_TR_TWO_MAX = XML_SHPNG_TR_ROUTE . '/shipping_tier_two/range/max';
    const XML_SHPNG_TR_TWO_STD_RATE = XML_SHPNG_TR_ROUTE . '/shipping_tier_two/standard/rate';
	const XML_SHPNG_TR_TWO_STD_TIME = XML_SHPNG_TR_ROUTE . '/shipping_tier_two/standard/time';
    const XML_SHPNG_TR_TWO_STD_ACTIVE = XML_SHPNG_TR_ROUTE . '/shipping_tier_two/standard/active';
    const XML_SHPNG_TR_TWO_EXP_RATE = XML_SHPNG_TR_ROUTE . '/shipping_tier_two/express/rate';
	const XML_SHPNG_TR_TWO_EXP_TIME = XML_SHPNG_TR_ROUTE . '/shipping_tier_two/express/time';
    const XML_SHPNG_TR_TWO_EXP_ACTIVE = XML_SHPNG_TR_ROUTE . '/shipping_tier_two/express/active';

	const XML_SHPNG_TR_THREE_MIN = XML_SHPNG_TR_ROUTE . '/shipping_tier_three/range/min';
	const XML_SHPNG_TR_THREE_MAX = XML_SHPNG_TR_ROUTE . '/shipping_tier_three/range/max';
    const XML_SHPNG_TR_THREE_STD_RATE = XML_SHPNG_TR_ROUTE . '/shipping_tier_three/standard/rate';
	const XML_SHPNG_TR_THREE_STD_TIME = XML_SHPNG_TR_ROUTE . '/shipping_tier_three/standard/time';
    const XML_SHPNG_TR_THREE_STD_ACTIVE = XML_SHPNG_TR_ROUTE . '/shipping_tier_three/standard/active';
    const XML_SHPNG_TR_THREE_EXP_RATE = XML_SHPNG_TR_ROUTE . '/shipping_tier_three/express/rate';
	const XML_SHPNG_TR_THREE_EXP_TIME = XML_SHPNG_TR_ROUTE . '/shipping_tier_three/express/time';
    const XML_SHPNG_TR_THREE_EXP_ACTIVE = XML_SHPNG_TR_ROUTE . '/shipping_tier_three/express/active';

    const XML_SHPNG_TR_BACKORDER_MIN = XML_SHPNG_TR_ROUTE . '/backorder_shipping/range/min';
    const XML_SHPNG_TR_BACKORDER_MAX = XML_SHPNG_TR_ROUTE . '/backorder_shipping/range/max';
    const XML_SHPNG_TR_BACKORDER_STD_RATE = XML_SHPNG_TR_ROUTE . '/backorder_shipping/standard/rate';
    const XML_SHPNG_TR_BACKORDER_STD_TIME = XML_SHPNG_TR_ROUTE . '/backorder_shipping/standard/time';
    const XML_SHPNG_TR_BACKORDER_STD_ACTIVE = XML_SHPNG_TR_ROUTE . '/backorder_shipping/standard/active';
    const XML_SHPNG_TR_BACKORDER_EXP_RATE = XML_SHPNG_TR_ROUTE . '/backorder_shipping/express/rate';
    const XML_SHPNG_TR_BACKORDER_EXP_TIME = XML_SHPNG_TR_ROUTE . '/backorder_shipping/express/time';
    const XML_SHPNG_TR_BACKORDER_EXP_ACTIVE = XML_SHPNG_TR_ROUTE . '/backorder_shipping/express/active';

    /**
     * Get the config details for all our pricing tiers
     *
     * @return array | null
     */
    public function getAllPricingTiers($from_configxml = false, $store_id = null)
    {
        $data = array();

        // Get our store id if it hasn't been sent through as a parameter so we're explicit
        if($store_id === null) $store_id = Mage::app()->getStore()->getStoreId();

        // Get these values from the module's config.xml file if stated, if not, try the Admin
        if($from_configxml == true){
            return Mage::getStoreConfig(self::XML_SHPNG_TR_ROUTE, $store_id);
        }
        else{
            $tiers = array('one', 'two', 'three', 'backorder');

            foreach($tiers as $key=>$tier){
                $uri = ($tier === 'backorder') ? 'backorder_shipping' : 'shipping_tier_' . $tier;
                $data[$uri] = array();
                $data[$uri]['range']['min'] = Mage::getStoreConfig(self::XML_SHPNG . '/' . $uri . '_min', $store_id);
                $data[$uri]['range']['max'] = Mage::getStoreConfig(self::XML_SHPNG . '/' . $uri . '_max', $store_id);
                $data[$uri]['standard']['rate'] = Mage::getStoreConfig(self::XML_SHPNG . '/' . $uri . '_standard', $store_id);
                $data[$uri]['standard']['time'] = Mage::getStoreConfig(self::XML_SHPNG . '/' . $uri . '_standard_time', $store_id);
                $data[$uri]['standard']['active'] = Mage::getStoreConfig(self::XML_SHPNG . '/' . $uri . '_standard_active', $store_id);
                $data[$uri]['express']['rate'] = Mage::getStoreConfig(self::XML_SHPNG . '/' . $uri . '_express', $store_id);
                $data[$uri]['express']['time'] = Mage::getStoreConfig(self::XML_SHPNG . '/' . $uri . '_express_time', $store_id);
                $data[$uri]['express']['active'] = Mage::getStoreConfig(self::XML_SHPNG . '/' . $uri . '_express_active', $store_id);
            }
            
            return $data;
        }
    }
}