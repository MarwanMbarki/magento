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
 * Controller Naming Convention Reference: https://code.tutsplus.com/tutorials/magento-custom-module-development--cms-20643
 *
 * Pattern: <Namespace>_<Module Name>_<Controller Name>_<"Controller" Keyword>
 * Underscores represent a directory path to Magento, i.e: Parachute/StoreDelivery/Controllers/IndexController.php
 *
 * (Controller location defined elsewhere in your module config.xml)
 * 
 * - Ross@Parachute
 */

class Parachute_SmartShipping_IndexController extends Mage_Core_Controller_Front_Action
{
	/*
	 * Routing pattern and controller naming convention reference: https://code.tutsplus.com/tutorials/magento-custom-module-development--cms-20643
	 *
	 * Route: yoursite.com/index.php/frontendname/actionControllername/actionmethod
     *
	 * frontendname = mymodule
	 * actionControllername = Index
	 * actionmethodname = Index
	 */

    public function indexAction()
    {
    	/* We can debug easier via this default view */
    	$this->loadLayout()->renderLayout();

    	// $h = Mage::helper('smartshipping');
        // var_dump($h->getAllPricingTiers());
    	
    	/* For production just redirect to the homepage if someone tries to access this route */
    	$this->getResponse()->setRedirect( Mage::getBaseUrl() );
    }
}