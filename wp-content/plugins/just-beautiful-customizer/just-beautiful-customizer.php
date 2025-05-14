<?php
/**
 * Plugin Name: Just Beautiful Customizer
 * Description: A custom plugin to enable product personalization for Just Beautiful.
 * Version: 1.0
 * Author: Rui Calvari
 * License: GPL2
 */

/**
 * Add the main Product Customizer submenu page
 */
function jbc_add_customizer_submenu() {
    add_submenu_page(
        'jb-development',          // Parent menu slug (from JB Development plugin)
        'Product Customizer Settings', // Page title
        'Product Customizer',      // Menu title
        'manage_options',          // Capability (admin access)
        'jbc-product-customizer',  // Submenu slug
        'jbc_category_settings_page' // Callback function
    );
}
add_action('admin_menu', 'jbc_add_customizer_submenu');

/**
 * Add the Create New Custom Rule submenu page
 */
function jbc_add_create_customization_page() {
    add_submenu_page(
        'jb-development',             // Parent slug
        'Create New Custom Rule',     // Page title
        'Create New Custom Rule',     // Menu title
        'manage_options',             // Capability
        'jbc-create-customization',   // Menu slug
        'jbc_create_customization_page' // Callback function
    );
}
add_action('admin_menu', 'jbc_add_create_customization_page');

/**
 * Add the Edit Custom Rule submenu page (hidden from menu)
 */
function jbc_add_edit_customization_page() {
    add_submenu_page(
        null,                         // Hidden from menu
        'Edit Custom Rule',           // Page title
        'Edit Custom Rule',           // Menu title
        'manage_options',             // Capability
        'jbc-edit-customization',     // Menu slug
        'jbc_edit_customization_page' // Callback function
    );
}
add_action('admin_menu', 'jbc_add_edit_customization_page');

/**
 * Main settings page (landing page)
 */
function jbc_category_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to access this page.');
    }

    // Handle delete action
    jbc_handle_delete_action();

    ?>
    <div class="wrap woocommerce">
        <h1>Product Customizer Settings</h1>

        <!-- Notifications -->
        <?php do_action('admin_notices'); ?>

        <!-- Create New Custom Rule Button -->
        <p>
            <a href="<?php echo admin_url('admin.php?page=jbc-create-customization'); ?>" class="button button-primary">Create New Custom Rule</a>
        </p>

        <!-- Table of existing rules -->
        <?php jbc_display_manage_table(); ?>
    </div>
    <?php
}

/**
 * Create new customization page
 */
function jbc_create_customization_page() {
    // Log that the function is being called
    error_log('jbc_create_customization_page called');

    // Debug user capabilities (Step 2)
    $user = wp_get_current_user();
    error_log('Current user ID: ' . $user->ID);
    error_log('Has manage_options: ' . (current_user_can('manage_options') ? 'yes' : 'no'));
    echo '<pre>User Capabilities: ';
    print_r($user->allcaps);
    echo '</pre>';

    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to access this page.');
    }

    // Handle form submission for creating a new rule
    if (isset($_POST['jbc_create_customization']) && check_admin_referer('jbc_create_customization_action', 'jbc_nonce')) {
        $category_id = intval($_POST['jbc_category']);
        $allow_image = isset($_POST['jbc_allow_image']) ? 1 : 0;
        $allow_text = isset($_POST['jbc_allow_text']) ? 1 : 0;
        $zones = [];

        if (!empty($_POST['jbc_zone_name'])) {
            foreach ($_POST['jbc_zone_name'] as $index => $name) {
                $zones[] = [
                    'name' => sanitize_text_field($name),
                    'x' => absint($_POST['jbc_zone_x'][$index]),
                    'y' => absint($_POST['jbc_zone_y'][$index]),
                    'width' => absint($_POST['jbc_zone_width'][$index]),
                    'height' => absint($_POST['jbc_zone_height'][$index]),
                ];
            }
        }

        // Save to term meta
        update_term_meta($category_id, 'jbc_allow_image', $allow_image);
        update_term_meta($category_id, 'jbc_allow_text', $allow_text);
        update_term_meta($category_id, 'jbc_zones', $zones);

        // Add success notice and redirect
        add_action('admin_notices', function() use ($category_id) {
            $category = get_term($category_id, 'product_cat');
            ?>
            <div class="notice notice-success is-dismissible">
                <p>Customization settings created for <?php echo esc_html($category->name); ?>.</p>
            </div>
            <?php
        });
        wp_redirect(admin_url('admin.php?page=jbc-product-customizer'));
        exit;
    }

    // Display the creation form
    ?>
    <div class="wrap woocommerce">
        <h1>Create New Custom Rule</h1>
        <?php jbc_display_create_form(); ?>
    </div>
    <?php
}

/**
 * Edit customization page
 */
function jbc_edit_customization_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to access this page.');
    }

    if (!isset($_GET['category'])) {
        wp_die('Invalid category.');
    }

    $category_id = intval($_GET['category']);
    $category = get_term($category_id, 'product_cat');

    if (is_wp_error($category)) {
        wp_die('Invalid category.');
    }

    // Handle form submission for updating the rule
    if (isset($_POST['jbc_update_customization']) && check_admin_referer('jbc_update_customization_action', 'jbc_nonce')) {
        $allow_image = isset($_POST['jbc_allow_image']) ? 1 : 0;
        $allow_text = isset($_POST['jbc_allow_text']) ? 1 : 0;
        $zones = [];

        if (!empty($_POST['jbc_zone_name'])) {
            foreach ($_POST['jbc_zone_name'] as $index => $name) {
                $zones[] = [
                    'name' => sanitize_text_field($name),
                    'x' => absint($_POST['jbc_zone_x'][$index]),
                    'y' => absint($_POST['jbc_zone_y'][$index]),
                    'width' => absint($_POST['jbc_zone_width'][$index]),
                    'height' => absint($_POST['jbc_zone_height'][$index]),
                ];
            }
        }

        // Update term meta
        update_term_meta($category_id, 'jbc_allow_image', $allow_image);
        update_term_meta($category_id, 'jbc_allow_text', $allow_text);
        update_term_meta($category_id, 'jbc_zones', $zones);

        // Add success notice and redirect
        add_action('admin_notices', function() use ($category) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p>Customization settings updated for <?php echo esc_html($category->name); ?>.</p>
            </div>
            <?php
        });
        wp_redirect(admin_url('admin.php?page=jbc-product-customizer'));
        exit;
    }

    // Display the edit form with pre-filled data
    ?>
    <div class="wrap woocommerce">
        <h1>Edit Custom Rule</h1>
        <?php jbc_display_edit_form($category_id); ?>
    </div>
    <?php
}

/**
 * Handle delete action
 */
function jbc_handle_delete_action() {
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['category'])) {
        $category_id = intval($_GET['category']);
        if (check_admin_referer('jbc_delete_' . $category_id)) {
            delete_term_meta($category_id, 'jbc_allow_image');
            delete_term_meta($category_id, 'jbc_allow_text');
            delete_term_meta($category_id, 'jbc_zones');

            // Add success notice
            add_action('admin_notices', function() use ($category_id) {
                $category = get_term($category_id, 'product_cat');
                ?>
                <div class="notice notice-success is-dismissible">
                    <p>Customization settings deleted for <?php echo esc_html($category->name); ?>.</p>
                </div>
                <?php
            });

            wp_redirect(admin_url('admin.php?page=jbc-product-customizer'));
            exit;
        }
    }
}

/**
 * Display the create form
 */
function jbc_display_create_form() {
    $categories = get_terms([
        'taxonomy' => 'product_cat',
        'hide_empty' => false,
        'meta_query' => [
            [
                'key' => 'jbc_allow_image',
                'compare' => 'NOT EXISTS',
            ],
        ],
    ]);

    if (empty($categories)) {
        ?>
        <div class="notice notice-info">
            <p>All categories already have customization settings.</p>
        </div>
        <?php
        return;
    }

    ?>
    <form method="post" action="" class="form-table">
        <?php wp_nonce_field('jbc_create_customization_action', 'jbc_nonce'); ?>
        <table class="form-table">
            <tr>
                <th><label for="jbc_category">Category</label></th>
                <td>
                    <select name="jbc_category" id="jbc_category" required>
                        <?php foreach ($categories as $category) : ?>
                            <option value="<?php echo esc_attr($category->term_id); ?>">
                                <?php echo esc_html($category->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">Select a category to apply customization settings.</p>
                </td>
            </tr>
            <tr>
                <th>Options</th>
                <td>
                    <p><label><input type="checkbox" name="jbc_allow_image" value="1"> Allow Image Upload</label></p>
                    <p class="description">Enable customers to upload images for products in this category.</p>
                    <p><label><input type="checkbox" name="jbc_allow_text" value="1"> Allow Text Input</label></p>
                    <p class="description">Enable customers to add text for products in this category.</p>
                </td>
            </tr>
            <tr>
                <th>Placement Zones</th>
                <td>
                    <p class="description">Define areas on the product where customizations can be applied.</p>
                    <div id="jbc_zones"></div>
                    <button type="button" id="jbc_add_zone" class="button">Add Zone</button>
                </td>
            </tr>
        </table>
        <p><input type="submit" name="jbc_create_customization" class="button button-primary" value="Create Customization"></p>
    </form>

    <script>
        document.getElementById('jbc_add_zone').addEventListener('click', function() {
            const zonesDiv = document.getElementById('jbc_zones');
            const index = zonesDiv.children.length;
            zonesDiv.innerHTML += `
                <div class="zone" style="margin-bottom: 10px;">
                    <label>Name: <input type="text" name="jbc_zone_name[${index}]" required></label>
                    <label>X: <input type="number" name="jbc_zone_x[${index}]" min="0" required></label>
                    <label>Y: <input type="number" name="jbc_zone_y[${index}]" min="0" required></label>
                    <label>Width: <input type="number" name="jbc_zone_width[${index}]" min="1" required></label>
                    <label>Height: <input type="number" name="jbc_zone_height[${index}]" min="1" required></label>
                    <button type="button" class="button jbc_remove_zone">Remove</button>
                </div>
            `;
            attachRemoveListeners();
        });

        function attachRemoveListeners() {
            document.querySelectorAll('.jbc_remove_zone').forEach(button => {
                button.removeEventListener('click', removeZone);
                button.addEventListener('click', removeZone);
            });
        }

        function removeZone() {
            this.parentElement.remove();
        }
    </script>
    <?php
}

/**
 * Display the edit form with pre-filled data
 */
function jbc_display_edit_form($category_id) {
    $category = get_term($category_id, 'product_cat');
    $allow_image = get_term_meta($category_id, 'jbc_allow_image', true);
    $allow_text = get_term_meta($category_id, 'jbc_allow_text', true);
    $zones = get_term_meta($category_id, 'jbc_zones', true) ?: [];

    ?>
    <form method="post" action="" class="form-table">
        <?php wp_nonce_field('jbc_update_customization_action', 'jbc_nonce'); ?>
        <table class="form-table">
            <tr>
                <th><label>Category</label></th>
                <td>
                    <p><?php echo esc_html($category->name); ?></p>
                    <input type="hidden" name="jbc_category" value="<?php echo esc_attr($category->term_id); ?>">
                </td>
            </tr>
            <tr>
                <th>Options</th>
                <td>
                    <p><label><input type="checkbox" name="jbc_allow_image" value="1" <?php checked($allow_image); ?>> Allow Image Upload</label></p>
                    <p class="description">Enable customers to upload images for products in this category.</p>
                    <p><label><input type="checkbox" name="jbc_allow_text" value="1" <?php checked($allow_text); ?>> Allow Text Input</label></p>
                    <p class="description">Enable customers to add text for products in this category.</p>
                </td>
            </tr>
            <tr>
                <th>Placement Zones</th>
                <td>
                    <p class="description">Define areas on the product where customizations can be applied.</p>
                    <div id="jbc_zones">
                        <?php foreach ($zones as $index => $zone) : ?>
                            <div class="zone" style="margin-bottom: 10px;">
                                <label>Name: <input type="text" name="jbc_zone_name[<?php echo $index; ?>]" value="<?php echo esc_attr($zone['name']); ?>" required></label>
                                <label>X: <input type="number" name="jbc_zone_x[<?php echo $index; ?>]" value="<?php echo esc_attr($zone['x']); ?>" min="0" required></label>
                                <label>Y: <input type="number" name="jbc_zone_y[<?php echo $index; ?>]" value="<?php echo esc_attr($zone['y']); ?>" min="0" required></label>
                                <label>Width: <input type="number" name="jbc_zone_width[<?php echo $index; ?>]" value="<?php echo esc_attr($zone['width']); ?>" min="1" required></label>
                                <label>Height: <input type="number" name="jbc_zone_height[<?php echo $index; ?>]" value="<?php echo esc_attr($zone['height']); ?>" min="1" required></label>
                                <button type="button" class="button jbc_remove_zone">Remove</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" id="jbc_add_zone" class="button">Add Zone</button>
                </td>
            </tr>
        </table>
        <p><input type="submit" name="jbc_update_customization" class="button button-primary" value="Update Customization"></p>
    </form>

    <script>
        document.getElementById('jbc_add_zone').addEventListener('click', function() {
            const zonesDiv = document.getElementById('jbc_zones');
            const index = zonesDiv.children.length;
            zonesDiv.innerHTML += `
                <div class="zone" style="margin-bottom: 10px;">
                    <label>Name: <input type="text" name="jbc_zone_name[${index}]" required></label>
                    <label>X: <input type="number" name="jbc_zone_x[${index}]" min="0" required></label>
                    <label>Y: <input type="number" name="jbc_zone_y[${index}]" min="0" required></label>
                    <label>Width: <input type="number" name="jbc_zone_width[${index}]" min="1" required></label>
                    <label>Height: <input type="number" name="jbc_zone_height[${index}]" min="1" required></label>
                    <button type="button" class="button jbc_remove_zone">Remove</button>
                </div>
            `;
            attachRemoveListeners();
        });

        function attachRemoveListeners() {
            document.querySelectorAll('.jbc_remove_zone').forEach(button => {
                button.removeEventListener('click', removeZone);
                button.addEventListener('click', removeZone);
            });
        }

        function removeZone() {
            this.parentElement.remove();
        }
    </script>
    <?php
}

/**
 * Display the manage table
 */
function jbc_display_manage_table() {
    $categories = get_terms([
        'taxonomy' => 'product_cat',
        'hide_empty' => false,
        'meta_query' => [
            [
                'key' => 'jbc_allow_image',
                'compare' => 'EXISTS',
            ],
        ],
    ]);

    if (empty($categories)) {
        ?>
        <div class="notice notice-info">
            <p>No customizations exist yet.</p>
        </div>
        <?php
        return;
    }

    ?>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Category</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category) : ?>
                <tr>
                    <td><?php echo esc_html($category->name); ?></td>
                    <td>
                        <a href="<?php echo admin_url('admin.php?page=jbc-edit-customization&category=' . $category->term_id); ?>" class="button">Edit</a>
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=jbc-product-customizer&action=delete&category=' . $category->term_id), 'jbc_delete_' . $category->term_id); ?>" 
                           onclick="return confirm('Are you sure you want to delete customization settings for <?php echo esc_js($category->name); ?>?');" 
                           class="button">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}

function jbc_remove_create_submenu() {
    remove_submenu_page('jb-development', 'jbc-create-customization');
}
add_action('admin_menu', 'jbc_remove_create_submenu', 999);
