<?php

use BCDL\Invoice\Party;
use BCDL\Invoice\Invoice;

if (!defined('ABSPATH')) {
    exit;
}

// Create the settings page in the dashboard
add_action('admin_menu', 'bcdl_invoice_add_settings_menu');

function bcdl_invoice_add_settings_menu() {
    add_menu_page(
        __('BCDL Invoice Settings', 'bcdl-invoice'),          // Page title
        __('BCDL Invoice Settings', 'bcdl-invoice'),          // Menu title
        'manage_options',               // Capability
        'bcdl-invoice-settings',        // Menu slug
        'bcdl_invoice_settings_page',   // Callback function
        'dashicons-media-spreadsheet',  // Icon
        26                              // Position
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
        iban VARCHAR(50) DEFAULT '',
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
            'phone'        => stripslashes(sanitize_text_field($_POST['phone'])),
            'iban'         => stripslashes(sanitize_text_field($_POST['iban']))
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
                <tr><th>IBAN</th>
                    <td><input type="text" name="iban" value="<?php echo esc_attr($company->iban ?? ''); ?>" class="regular-text"></td></tr>
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
    if ($customer->id === 1) {
        return $customer;
    }

    // 1. Check if this customer ID exists
    $exists = null;
    if ($customer->id > 1) {
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT company_id FROM $table_name WHERE company_id = %d",
            $customer->id
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
            'phone'        => $customer->phone,
            'iban'        => $customer->iban
        ]);

        $customer->id = $wpdb->insert_id;
    } else {
        // If found, update $customer->id to the existing one
        $customer->id = (int) $exists;
    }

    return $customer;
}

function bcdl_footer_html() {
    $footerHTML = '<div class="invfooter"><p class="mainpara"><strong>'; 
    $footerHTML .= __('Thank you for trusting DATTEQ Ltd.!', 'bcdl-invoice');
    $footerHTML .= '</strong></p><p class="invfooterpara">';
    $footerHTML .= __('This invoice was created by <strong>BCDL Invoice</strong> by <strong>DATTEQ</strong>. For more information visit ', 'bcdl-invoice');
    $footerHTML .= '<a href="https://datteq.com/" target="_blank">https://datteq.com/</a></p></div>';
    return $footerHTML;
}

function bcdl_get_company($id) {
    global $wpdb;
    $table = $wpdb->prefix . 'bcdl_invoice_companies';
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE company_id = %d", $id), ARRAY_A);
    return $row ?: [
        'company_id' => 0,
        'company_name' => '',
        'address' => '',
        'crn' => '',
        'vat' => '',
        'mrp' => '',
        'email' => '',
        'phone' => '',
        'iban' => ''
    ];
}

function bcdl_save_invoice_to_database(
    Party $customer,
    Party $supplier,
    array $services,
    ?DateTime $issueDate = null,
    ?DateTime $eventDate = null,
    ?DateTime $dueDate = null,
    string $meta,
): ?Invoice {
    global $wpdb;

    // 1. Insert invoice (without number yet)
    $wpdb->insert(
        "{$wpdb->prefix}bcdl_invoices",
        [
            'supplier_id' => $supplier->id,
            'customer_id' => $customer->id,
            'issue_date'  => $issueDate?->format('Y-m-d'),
            'event_date'  => $eventDate?->format('Y-m-d'),
            'due_date'    => $dueDate?->format('Y-m-d'),
            'meta'        => $meta
        ],
        ['%d', '%d', '%s', '%s', '%s', '%s']
    );

    $invoice_id = $wpdb->insert_id;
    if (!$invoice_id) {
        return null;
    }

    // 2. Generate invoice number (10 digits, zero-padded)
    $invoice_number = str_pad((string)$invoice_id, 10, '0', STR_PAD_LEFT);
    $wpdb->update(
        "{$wpdb->prefix}bcdl_invoices",
        ['invoice_number' => $invoice_number],
        ['invoice_id' => $invoice_id],
        ['%s'],
        ['%d']
    );

    // 3. Save services
    foreach ($services as $service) {
        $wpdb->insert(
            "{$wpdb->prefix}bcdl_invoice_services",
            [
                'invoice_id'  => $invoice_id,
                'description' => $service->description,
                'measure'     => $service->measure,
                'quantity'    => $service->quantity,
                'unit_price'  => $service->unitPrice,
                'tax_rate'    => $service->taxRate,
                'discount'    => $service->discount,
            ],
            ['%d', '%s', '%s', '%f', '%f', '%f', '%f']
        );
    }

    // 4. Build Invoice object to return
    $invoice = new Invoice(
        $supplier,
        $customer,
        $invoice_number,
        $eventDate
    );
    $invoice->id      = $invoice_id;
    $invoice->dueDate = $dueDate;

    foreach ($services as $service) {
        $invoice->addService($service);
    }

    return $invoice;
}

function bcdl_create_invoices_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'bcdl_invoices';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        invoice_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        supplier_id INT(11) NOT NULL,
        customer_id INT(11) NOT NULL,
        invoice_number VARCHAR(20) NOT NULL,
        issue_date DATE NOT NULL,
        event_date DATE NOT NULL,
        due_date DATE DEFAULT NULL,
        tax_base DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        tax_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        grand_total DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        meta LONGTEXT DEFAULT NULL, -- JSON string with extra data
        PRIMARY KEY (invoice_id),
        UNIQUE KEY invoice_number_unique (invoice_number)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * Create the invoice services table if it doesn't exist
 */
function bcdl_create_invoice_services_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'bcdl_invoice_services';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        service_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        invoice_id BIGINT(20) UNSIGNED NOT NULL,
        description TEXT NOT NULL,
        measure VARCHAR(50) DEFAULT '',
        quantity DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        unit_price DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        tax_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
        discount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        PRIMARY KEY (service_id),
        KEY invoice_id_idx (invoice_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}