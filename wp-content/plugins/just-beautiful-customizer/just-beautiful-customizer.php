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

    $categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
    $settings = get_option('jbc_category_customization_settings', []);

    if (isset($_POST['jbc_save_settings'])) {
        $settings = $_POST['jbc_settings'];
        foreach ($settings as $cat_id => $data) {
            $settings[$cat_id]['allow_image'] = isset($data['allow_image']) ? 1 : 0;
            $settings[$cat_id]['allow_text'] = isset($data['allow_text']) ? 1 : 0;
            if (isset($data['zones'])) {
                foreach ($data['zones'] as $index => $zone) {
                    $settings[$cat_id]['zones'][$index] = [
                        'name' => sanitize_text_field($zone['name']),
                        'x' => absint($zone['x']),
                        'y' => absint($zone['y']),
                        'width' => absint($zone['width']),
                        'height' => absint($zone['height'])
                    ];
                }
            }
        }
        update_option('jbc_category_customization_settings', $settings);
        echo '<div class="updated"><p>Settings saved successfully!</p></div>';
    }

    ?>
    <div class="wrap">
        <h1>Category Customization Settings</h1>
        <form method="post">
            <?php foreach ($categories as $category) : ?>
                <h2><?php echo esc_html($category->name); ?></h2>
                <p>
                    <label>
                        <input type="checkbox" name="jbc_settings[<?php echo $category->term_id; ?>][allow_image]"
                            <?php checked(isset($settings[$category->term_id]['allow_image']) && $settings[$category->term_id]['allow_image']); ?>>
                        Allow Image Upload
                    </label><br>
                    <label>
                        <input type="checkbox" name="jbc_settings[<?php echo $category->term_id; ?>][allow_text]"
                            <?php checked(isset($settings[$category->term_id]['allow_text']) && $settings[$category->term_id]['allow_text']); ?>>
                        Allow Text Input
                    </label>
                </p>
                <h3>Placement Zones</h3>
                <div class="jbc-placement-zones" data-cat-id="<?php echo $category->term_id; ?>">
                    <?php
                    $zones = isset($settings[$category->term_id]['zones']) ? $settings[$category->term_id]['zones'] : [];
                    if (empty($zones)) {
                        $zones[] = ['name' => '', 'x' => '', 'y' => '', 'width' => '', 'height' => ''];
                    }
                    foreach ($zones as $index => $zone) :
                    ?>
                        <div class="zone">
                            <label>Name: <input type="text" name="jbc_settings[<?php echo $category->term_id; ?>][zones][<?php echo $index; ?>][name]" value="<?php echo esc_attr($zone['name']); ?>"></label>
                            <label>X: <input type="number" min="0" name="jbc_settings[<?php echo $category->term_id; ?>][zones][<?php echo $index; ?>][x]" value="<?php echo esc_attr($zone['x']); ?>"></label>
                            <label>Y: <input type="number" min="0" name="jbc_settings[<?php echo $category->term_id; ?>][zones][<?php echo $index; ?>][y]" value="<?php echo esc_attr($zone['y']); ?>"></label>
                            <label>Width: <input type="number" min="0" name="jbc_settings[<?php echo $category->term_id; ?>][zones][<?php echo $index; ?>][width]" value="<?php echo esc_attr($zone['width']); ?>"></label>
                            <label>Height: <input type="number" min="0" name="jbc_settings[<?php echo $category->term_id; ?>][zones][<?php echo $index; ?>][height]" value="<?php echo esc_attr($zone['height']); ?>"></label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="add-zone" data-cat-id="<?php echo $category->term_id; ?>">Add Zone</button>
            <?php endforeach; ?>
            <p><input type="submit" name="jbc_save_settings" value="Save Settings" class="button-primary"></p>
        </form>
    </div>
    <script>
    document.querySelectorAll('.add-zone').forEach(button => {
        button.addEventListener('click', function() {
            const catId = this.getAttribute('data-cat-id');
            const container = this.previousElementSibling;
            const zones = container.querySelectorAll('.zone');
            const index = zones.length;
            const newZone = document.createElement('div');
            newZone.className = 'zone';
            newZone.innerHTML = `
                <label>Name: <input type="text" name="jbc_settings[${catId}][zones][${index}][name]"></label>
                <label>X: <input type="number" min="0" name="jbc_settings[${catId}][zones][${index}][x]"></label>
                <label>Y: <input type="number" min="0" name="jbc_settings[${catId}][zones][${index}][y]"></label>
                <label>Width: <input type="number" min="0" name="jbc_settings[${catId}][zones][${index}][width]"></label>
                <label>Height: <input type="number" min="0" name="jbc_settings[${catId}][zones][${index}][height]"></label>
            `;
            container.appendChild(newZone);
        });
    });
    </script>
    <?php
}