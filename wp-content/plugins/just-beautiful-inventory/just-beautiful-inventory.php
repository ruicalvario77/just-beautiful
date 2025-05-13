<?php
/**
 * Plugin Name: Just Beautiful Inventory
 * Description: A custom plugin to manage inventory and material usage for Just Beautiful.
 * Version: 1.0
 * Author: Rui Calvario
 * License: GPL2
 */

 function jbi_add_test_menu() {
    add_menu_page(
        'Just Beautiful Inventory Test',
        'Inventory Test',
        'manage_options',
        'jbi-test',
        'jbi_test_page_content'
    );
}
add_action('admin_menu', 'jbi_add_test_menu');

function jbi_test_page_content() {
    echo '<h1>Just Beautiful Inventory is active!</h1>';
}