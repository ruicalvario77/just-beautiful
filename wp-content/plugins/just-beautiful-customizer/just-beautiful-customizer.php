<?php
/**
 * Plugin Name: Just Beautiful Customizer
 * Description: A custom plugin to enable product personalization for Just Beautiful.
 * Version: 1.0
 * Author: Rui Calvario
 * License: GPL2
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

function jbc_category_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to access this page.');
    }

    // Handle form submissions and actions
    jbc_handle_form_submissions();

    ?>
    <div class="wrap woocommerce">
        <h1>Product Customizer Settings</h1>

        <!-- Notifications -->
        <?php do_action('admin_notices'); ?>

        <!-- Section 1: Create New Customization -->
        <h2>Create New Customization</h2>
        <?php jbc_display_create_form(); ?>

        <!-- Section 2: Manage Existing Customizations -->
        <h2>Manage Existing Customizations</h2>
        <?php jbc_display_manage_table(); ?>
    </div>
    <?php
}

function jbc_handle_form_submissions() {
    // Handle create new customization
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

        // Add success notice
        add_action('admin_notices', function() use ($category_id) {
            $category = get_term($category_id, 'product_cat');
            ?>
            <div class="notice notice-success is-dismissible">
                <p>Customization settings created for <?php echo esc_html($category->name); ?>.</p>
            </div>
            <?php
        });
    }

    // Handle delete action
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
                button.removeEventListener('click', removeZone); // Prevent duplicate listeners
                button.addEventListener('click', removeZone);
            });
        }

        function removeZone() {
            this.parentElement.remove();
        }
    </script>
    <?php
}

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
                        <a href="<?php echo admin_url('admin.php?page=jbc-product-customizer&action=edit&category=' . $category->term_id); ?>" class="button">Edit</a>
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