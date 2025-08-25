<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load WordPress so __() works
require_once dirname(__FILE__, 4) . '/wp-load.php';

class Party {
    public $name;
    public $address;
    public $vat;
    public $email;

    public function __construct($name, $address, $vat, $email) {
        $this->name = $name;
        $this->address = $address;
        $this->vat = $vat;
        $this->email = $email;
    }
}

// Creating the code
$invoicetitle = '<h1>';
$invoicetitle .= __('Invoice', 'bcdl-invoice');
$invoicetitle .= '</h1><p class="invoriginal">';
$invoicesubtitle = __('Original', 'bcdl-invoice');
$invoicebody = '</p><p class="mainpara">This is a <strong>sample</strong> text in English.</p>';

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
