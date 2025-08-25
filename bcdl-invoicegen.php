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

// Initialize the variables
require_once __DIR__ . '/bcdl-invclasses.php';

$customer = new Party(
    (int) $_POST['custid'],
    stripslashes(sanitize_text_field($_POST['customer'])),
    stripslashes(sanitize_textarea_field($_POST['address'])), // better for multi-line
    stripslashes(sanitize_text_field($_POST['crn'])),
    stripslashes(sanitize_text_field($_POST['vat'])),
    stripslashes(sanitize_text_field($_POST['mrp'])),
    sanitize_email($_POST['email']),
    stripslashes(sanitize_text_field($_POST['phone']))
);

// Creating the code
$invoicetitle = '<h1>';
$invoicetitle .= __('Invoice', 'bcdl-invoice');
$invoicetitle .= '</h1><p class="invoriginal">';

$invoicesubtitle = __('Original', 'bcdl-invoice');

$invoicebody = '<p><strong>Customer Details</strong></p>
    <table class="mainpara" border="0">
        <tr><th>ID</th><td>' . $customer->custid . '</td></tr>
        <tr><th>Name</th><td>' . $customer->name . '</td></tr>
        <tr><th>Address</th><td>' . nl2br($customer->address) . '</td></tr>
        <tr><th>CRN</th><td>' . $customer->crn . '</td></tr>
        <tr><th>VAT</th><td>' . $customer->vat . '</td></tr>
        <tr><th>MRP</th><td>' . $customer->mrp . '</td></tr>
        <tr><th>Email</th><td>' . $customer->email . '</td></tr>
        <tr><th>Phone</th><td>' . $customer->phone . '</td></tr>
    </table>';

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
$footerHTML = '<div class="invfooter"><p class="mainpara"><strong>'; 
$footerHTML .= __('Thank you for trusting DATTEQ Ltd.!', 'bcdl-invoice');
$footerHTML .= '</stong></p><p class="invfooterpara">';
$footerHTML .= __('This invoice was created by <strong>BCDL Invoice</strong> by <strong>DATTEQ</strong>. For more information visit ', 'bcdl-invoice');
$footerHTML .= '<a href="https://datteq.com/" target="_blank">https://datteq.com/</a></p></div>';
$mpdf->SetHTMLFooter($footerHTML);

// Add a new page
$mpdf->AddPage();

// Write HTML content COPY
$invoicecode = $invoicetitle . '&nbsp;' . $invoicebody;
$mpdf->WriteHTML($invoicecode, \Mpdf\HTMLParserMode::HTML_BODY);

// Output PDF
$mpdf->Output('bcdl-invoice.pdf', \Mpdf\Output\Destination::INLINE);
