<?php
/*
* Plugin Name: Add To Cart Rate
*
* Description: Adds a sortable column to your product list to show the ratio of people that add the product to cart.
*
* Author: Josh Kohlbach
* Author URI: http://rymera.com.au
* Plugin URI: http://rymera.com.au
* Requires at least: 3.4
* Tested up to: 3.9.1
* Version: 1.0
*/

require_once('helperFunctions.php');

/*******************************************************************************
** AddToCartRate
** Main handler for the plugin
** @since 1.0
*******************************************************************************/
class AddToCartRate {

    /*******************************************************************************
	** __construct
	** Construct and initialise the plugin
	** @since 1.0
	*******************************************************************************/
	public function __construct() {

        /* Figure out what plugin we're dealing with and setup and initiate the
        ** handler for the detected plugin */

        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        if (is_plugin_active('woocommerce/woocommerce.php')) {
            // WooCommerce
            require_once('handlers/ATCR_WooCommerce_Handler.php');
            $handler = new ATCR_WooCommerce_Handler();
        } else if (is_plugin_active('wp-e-commerce/wp-shopping-cart.php')) {
            // WP eCommerce
            require_once('handlers/ATCR_WPECommerce_Handler.php');
            $handler = new ATCR_WPECommerce_Handler();
        } else if (is_plugin_active('easy-digital-downloads/easy-digital-downloads.php')) {
            // Easy Digital Downloads
            require_once('handlers/ATCR_EDD_Handler.php');
            $handler = new ATCR_EDD_Handler();
        }

	}

}

// Create a new instance of the plugin's handler class
$addToCartRate = new AddToCartRate();
