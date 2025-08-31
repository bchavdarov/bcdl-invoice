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
            $_POST['description'][$i],
            $_POST['measure'][$i],
            (float) $_POST['quantity'][$i],
            (float) $_POST['unit_price'][$i],
            (float) ($_POST['tax_rate'][$i] ?? 0),
            (float) ($_POST['discount'][$i] ?? 0)
        );
    }
}

// Collect meta 
$meta_form = [
    'taxrate'     => $_POST['taxrate'] ?? '',
    'currency' => $_POST['currency'] ?? 'EUR',
    'vatbase'  => $_POST['vatbase'] ?? '',
    'paymentmethod'  => $_POST['paymentmethod'] ?? '',
    'issuer'     => $_POST['issuer'] ?? '',
    'receiver'     => $_POST['receiver'] ?? '',
];

$meta = json_encode($meta_form, JSON_UNESCAPED_UNICODE);

// Prepare dates
$issueDate = !empty($_POST['issuedate']) 
    ? new DateTime($_POST['issuedate']) 
    : new DateTime('today'); // fallback to today

$eventDate = !empty($_POST['eventdate']) 
    ? new DateTime($_POST['eventdate']) 
    : new DateTime('today'); // fallback to today

$dueDate = !empty($_POST['duedate']) 
    ? new DateTime($_POST['duedate']) 
    : (clone $eventDate)->modify('+30 days');

$currency = $_POST['currency'];

// Function calls:
// Save the new company to the database if not saved already
$customer = bcdl_company_save($customer);

// Create the two tables - invoices and services if they don't exist
bcdl_create_invoices_table();
bcdl_create_invoice_services_table();

// Save invoice
$invoice = bcdl_save_invoice_to_database($customer, $supplier, $services, $issueDate, $eventDate, $dueDate, $meta);

// Creating the code
$invoicetitle = '<h1 style="border-bottom: 1px solid black;">';
$invoicetitle .= __('Invoice', 'bcdl-invoice');
$invoicetitle .= '</h1><p class="invoriginal">';
$invoicesubtitle = __('Original', 'bcdl-invoice');

// Get the HTML code from the template ====================
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

// Set Header
$mpdf->SetHTMLHeader( bcdl_header_html() );

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
