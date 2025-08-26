<?php

if (!defined('ABSPATH')) {
    exit;
}

// Create the settings page in the dashboard
add_action('admin_menu', 'bcdl_invoice_add_settings_menu');

function bcdl_invoice_add_settings_menu() {
    add_menu_page(
        __('BCDL Invoice Settings', 'bcdl-invoice'),          // Page title
        __('BCDL Invoice Settings', 'bcdl-invoice'),          // Menu title
        'manage_options',            // Capability
        'bcdl-invoice-settings',     // Menu slug
        'bcdl_invoice_settings_page',// Callback function
        'dashicons-media-spreadsheet', // Icon
        26                           // Position
    );
}

// Fill the settings page with contents
function bcdl_invoice_settings_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bcdl_invoice_companies';

    // 1. Ensure the table exists
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        company_id INT(11) NOT NULL AUTO_INCREMENT,
        company_name VARCHAR(255) NOT NULL,
        address TEXT NOT NULL,
        crn VARCHAR(50) DEFAULT '',
        vat VARCHAR(50) DEFAULT '',
        mrp VARCHAR(100) DEFAULT '',
        email VARCHAR(100) DEFAULT '',
        phone VARCHAR(50) DEFAULT '',
        PRIMARY KEY (company_id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // 2. Fetch the first company record if it exists
    $company = $wpdb->get_row("SELECT * FROM $table_name ORDER BY company_id ASC LIMIT 1");

    // 3. Handle form submission
    if (isset($_POST['bcdl_save_company'])) {
        $data = [
            'company_name' => stripslashes(sanitize_text_field($_POST['name'])),
            'address'      => stripslashes(sanitize_textarea_field($_POST['address'])),
            'crn'          => stripslashes(sanitize_text_field($_POST['crn'])),
            'vat'          => stripslashes(sanitize_text_field($_POST['vat'])),
            'mrp'          => stripslashes(sanitize_text_field($_POST['mrp'])),
            'email'        => sanitize_email($_POST['email']),
            'phone'        => stripslashes(sanitize_text_field($_POST['phone']))
        ];

        if ($company) {
            $result = $wpdb->update($table_name, $data, ['company_id' => $company->company_id]);
        } else {
            $result = $wpdb->insert($table_name, $data);
        }

        echo $result === false
            ? '<div class="error"><p>'.__('Database error: ', 'bcdl-invoice').'' . esc_html($wpdb->last_error) . '</p></div>'
            : '<div class="updated"><p>'.__('Company info saved successfully!', 'bcdl-invoice').'</p></div>';

        // Refresh record
        $company = $wpdb->get_row("SELECT * FROM $table_name ORDER BY company_id ASC LIMIT 1");
    }

    // 4. Render the form
    ?>
    <div class="wrap">
        <h1><?php _e('Company Settings', 'bcdl-invoice') ?></h1>
        <form method="post">
            <table class="form-table">
                <tr><th><?php _e('Company Name', 'bcdl-invoice') ?></th>
                    <td><input type="text" name="name" value="<?php echo esc_attr($company->company_name ?? ''); ?>" class="regular-text" required></td></tr>
                <tr><th><?php _e('Address', 'bcdl-invoice') ?></th>
                    <td><textarea name="address" rows="4" class="large-text" required><?php echo esc_textarea($company->address ?? ''); ?></textarea></td></tr>
                <tr><th><?php _e('CRN', 'bcdl-invoice') ?></th>
                    <td><input type="text" name="crn" value="<?php echo esc_attr($company->crn ?? ''); ?>" class="regular-text"></td></tr>
                <tr><th><?php _e('VAT Number', 'bcdl-invoice') ?></th>
                    <td><input type="text" name="vat" value="<?php echo esc_attr($company->vat ?? ''); ?>" class="regular-text"></td></tr>
                <tr><th><?php _e('MRP', 'bcdl-invoice') ?></th>
                    <td><input type="text" name="mrp" value="<?php echo esc_attr($company->mrp ?? ''); ?>" class="regular-text"></td></tr>
                <tr><th><?php _e('Email', 'bcdl-invoice') ?></th>
                    <td><input type="email" name="email" value="<?php echo esc_attr($company->email ?? ''); ?>" class="regular-text"></td></tr>
                <tr><th><?php _e('Phone', 'bcdl-invoice') ?></th>
                    <td><input type="text" name="phone" value="<?php echo esc_attr($company->phone ?? ''); ?>" class="regular-text"></td></tr>
            </table>
            <p><input type="submit" name="bcdl_save_company" class="button button-primary" value="<?php _e('Save Company Data', 'bcdl-invoice') ?>"></p>
        </form>
    </div>
    <?php
}

// Save the new company to the database
function bcdl_company_save($customer) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bcdl_invoice_companies';

    // Don't allow saving ID=1 (our own company)
    if ($customer->custid === 1) {
        return $customer;
    }

    // 1. Check if this customer ID exists
    $exists = null;
    if ($customer->custid > 1) {
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT company_id FROM $table_name WHERE company_id = %d",
            $customer->custid
        ));
    }

    // 2. If no ID match, check by CRN or VAT (unique identifiers)
    if (!$exists && (!empty($customer->crn) || !empty($customer->vat))) {
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT company_id FROM $table_name WHERE crn = %s OR vat = %s",
            $customer->crn,
            $customer->vat
        ));
    }

    // 3. If still not found, insert as new company
    if (!$exists) {
        $wpdb->insert($table_name, [
            'company_name' => $customer->name,
            'address'      => $customer->address,
            'crn'          => $customer->crn,
            'vat'          => $customer->vat,
            'mrp'          => $customer->mrp,
            'email'        => $customer->email,
            'phone'        => $customer->phone
        ]);

        $customer->custid = $wpdb->insert_id;
    } else {
        // If found, update $customer->custid to the existing one
        $customer->custid = (int) $exists;
    }

    return $customer;
}
