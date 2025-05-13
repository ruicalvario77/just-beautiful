<?php
/*
 * Plugin Name: JB Development
 * Description: Central hub for JB Development plugins.
 * Version: 1.0
 * Author: Rui Calvario
 */

function jb_development_menu() {
    add_menu_page(
        'JB Development',         // Page title
        'JB Development',         // Menu title
        'manage_options',         // Capability (admin access)
        'jb-development',         // Menu slug
        'jb_development_dashboard', // Callback function
        'dashicons-admin-generic', // Icon
        20                        // Position in the menu
    );
}
add_action('admin_menu', 'jb_development_menu');

function jb_development_dashboard() {
    echo '<div class="wrap">';
    echo '<h1>JB Development Dashboard</h1>';
    echo '<p>Welcome to the central hub for all JB Development plugins!</p>';
    echo '</div>';
}