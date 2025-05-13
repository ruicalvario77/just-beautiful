<?php
/**
 * Plugin Name: Just Beautiful Collection Booking
 * Description: A custom plugin to manage collection slot booking during checkout.
 * Version: 1.0
 * Author: Rui Calvario
 * License: GPL2
 */

 add_action( 'admin_menu', function() {
    add_submenu_page(
        'woocommerce',
        'Collection Booking Test',
        'Collection Booking',
        'manage_options',
        'jbc-test',
        function() {
            echo '<h1>Collection Booking is active!</h1>';
        }
    );
});