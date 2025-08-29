<?php
/**
 * Invoice Template
 *
 * Variables passed:
 * $supplier (Party)
 * $customer (Party)
 * $invoice  (Invoice)
 */
?>

<table class="mainpara" border="0">>
    <tr style="width: 100%">
        <td style="width: 50%; padding-right: 3rem;">
            <span style="text-transform: uppercase;"><strong><?php _e('Customer:', 'bcdl-invoice') ?></strong></span><br />
            <span><?php echo($customer->name); ?></span><br />
            <span><?php echo(__('CRN', 'bcdl-invoice') . ': ' . $customer->crn); ?></span><br />
            <span><?php echo(__('VAT ID', 'bcdl-invoice') . ': ' . $customer->vat); ?></span><br />
            <span><?php echo(__('Address', 'bcdl-invoice') . ': ' . nl2br($customer->address) ); ?></span>
        </td>
        
        <td style="width: 50%; padding-left: 3rem;">
            <span style="text-transform: uppercase;"><strong><?php _e('Supplier:', 'bcdl-invoice') ?></strong></span><br />
            <span><?php echo($supplier->name); ?></span><br />
            <span><?php echo(__('CRN', 'bcdl-invoice') . ': ' . $supplier->crn); ?></span><br />
            <span><?php echo(__('VAT ID', 'bcdl-invoice') . ': ' . $supplier->vat); ?></span><br />
            <span><?php echo(__('Address', 'bcdl-invoice') . ': ' . nl2br($supplier->address) ); ?></span>
        </td>
    </tr>
</table>

