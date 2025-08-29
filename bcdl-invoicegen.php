<?php
// Exit if accessed directly
if ( $_SERVER['REQUEST_METHOD']=='GET' && realpath(__FILE__) == realpath( $_SERVER['SCRIPT_FILENAME'] ) ) {        
    header( 'HTTP/1.0 403 Forbidden', TRUE, 403 );
    die( header( 'location: /index.php' ) );
}

//Errors displaying for debug purposes only
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

// Load WordPress so __() works
require_once dirname(__FILE__, 4) . '/wp-load.php';


require_once __DIR__ . '/bcdl-invclasses.php';
require_once __DIR__ . '/bcdl-invfunctions.php';

use BCDL\Invoice\Party;
use BCDL\Invoice\Service;
use BCDL\Invoice\Invoice;

// Initialize the variables
$customer = new Party(
    (int) $_POST['custid'],
    stripslashes(sanitize_text_field($_POST['customer'])),
    stripslashes(sanitize_textarea_field($_POST['address'])), // better for multi-line
    stripslashes(sanitize_text_field($_POST['crn'])),
    stripslashes(sanitize_text_field($_POST['vat'])),
    stripslashes(sanitize_text_field($_POST['mrp'])),
    sanitize_email($_POST['email']),
    stripslashes(sanitize_text_field($_POST['phone'])),
    stripslashes(sanitize_text_field($_POST['iban']))
);

// Save the new company to the database if not saved already
$customer = bcdl_company_save($customer);

// Load supplier (company issuing the invoice) from DB - record ID 1
$supplier_data = bcdl_get_company(1);

$supplier = new Party(
    (int) $supplier_data['company_id'],
    $supplier_data['company_name'],
    $supplier_data['address'],
    $supplier_data['crn'],
    $supplier_data['vat'],
    $supplier_data['mrp'],
    $supplier_data['email'],
    $supplier_data['phone'],
    $supplier_data['iban']
);

//Load the services from the form
$services = [];
if (!empty($_POST['description']) && is_array($_POST['description'])) {
    $count = count($_POST['description']);
    for ($i = 0; $i < $count; $i++) {
        $services[] = new Service(
            $_POST['description'][$i] ?? '',
            $_POST['measure'][$i] ?? '',
            (float)($_POST['quantity'][$i] ?? 0),
            (float)($_POST['unit_price'][$i] ?? 0)
        );
    }
}

$invoice = new Invoice($supplier, $customer, 'INV-' . date('YmdHis'));

foreach ($services as $service) {
    $invoice->addService($service);
}



// Creating the code
$invoicetitle = '<h1>';
$invoicetitle .= __('Invoice', 'bcdl-invoice');
$invoicetitle .= '</h1><p class="invoriginal">';

$invoicesubtitle = __('Original', 'bcdl-invoice');

// Get the HTML code from the template
ob_start();
include __DIR__ . '/bcdl-invoice-template.php';
$invoicebody = ob_get_clean();

// PDF Generation process
require_once __DIR__ . '/vendor/autoload.php';


$mpdf = new \Mpdf\Mpdf([
    'format' => 'A4',
    'orientation' => 'P',
    'margin_left' => 25,
    'margin_right' => 15,
    'margin_top' => 20,
    'margin_bottom' => 20
]);

//Set meta
$mpdf->SetTitle(__('BCDL Invoice', 'bcdl-invoice'));
$mpdf->SetAuthor('Datteq Ltd.');

// Load the CSS
if (file_exists(__DIR__ . '/bcdl-invoice.css')) {
    $stylesheet = file_get_contents(__DIR__ . '/bcdl-invoice.css');
    $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
}

// Write HTML content ORIGINAL
$invoicecode = $invoicetitle . $invoicesubtitle . $invoicebody;
$mpdf->WriteHTML($invoicecode, \Mpdf\HTMLParserMode::HTML_BODY);

// Set Footer
$mpdf->SetHTMLFooter( bcdl_footer_html() );

// Add a new page
$mpdf->AddPage();

// Write HTML content COPY
$invoicecode = $invoicetitle . '&nbsp;' . $invoicebody;
$mpdf->WriteHTML($invoicecode, \Mpdf\HTMLParserMode::HTML_BODY);

// Output PDF
$mpdf->Output('bcdl-invoice.pdf', \Mpdf\Output\Destination::INLINE);
