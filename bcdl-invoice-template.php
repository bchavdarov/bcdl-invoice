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
<div class="mainpara">
    <p>
        <?php echo( __('Number', 'bcdl-invoice') . ': '); ?>
        <strong><?php echo $invoice->number; ?></strong>
        <br/>
        <?php echo( __('Issue date', 'bcdl-invoice') . ': '); ?>
        <strong><?php echo bcdl_format_date($issueDate); ?></strong>
        <br/>
        <?php echo( __('Event date', 'bcdl-invoice') . ': '); ?>
        <strong><?php echo bcdl_format_date($eventDate); ?></strong>
    </p>
</div>

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



<p>&nbsp;</p>
<table width="100%" class="mainpara">
    <thead>
        <tr>
            <th>#</th>
            <th style="text-align: left;"><?php _e('Description of good or service', 'bcdl-invoice'); ?></th>
            <th class="numeric"><?php _e('Measure', 'bcdl-invoice'); ?></th>
            <th class="numeric"><?php _e('Quantity', 'bcdl-invoice'); ?></th>
            <th class="numeric"><?php echo(__('Unit Price', 'bcdl-invoice').', <br />'.$meta_form['currency']); ?></th>
            <!--
            <th class="numeric"><?php _e('Discount', 'bcdl-invoice'); ?>, %</th>
            <th class="numeric"><?php _e('Tax Rate', 'bcdl-invoice'); ?>, %</th>
            -->
            <th class="numeric"><?php echo(__('Total', 'bcdl-invoice').', <br />'.$currency); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php $linenum=1; ?>
        <?php foreach ($invoice->services as $service): ?>
            <?php 
                $lineTotal = ($service->quantity * $service->unitPrice) * (1 - $service->discount/100);
                $lineTotal += $lineTotal * ($service->taxRate/100);
            ?>
            <tr>
                <td><?php echo $linenum; ?></td>
                <td><?php echo $service->description; ?></td>
                <td class="numeric"><?php echo $service->measure; ?></td>
                <td class="numeric"><?php echo $service->quantity; ?></td>
                <td class="numeric"><?php echo number_format($service->unitPrice, 2); ?></td>
                <!--
                <td class="numeric"><?php echo number_format($service->discount, 2); ?></td>
                <td class="numeric"><?php echo number_format($service->taxRate, 2); ?></td>
                -->
                <td class="numeric"><?php echo number_format($lineTotal, 2); ?></td>
            </tr>
            <?php $linenum++; ?>
        <?php endforeach; ?>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td  class="numeric">
                    <?php echo __('Grand Total:', 'bcdl-invoice') ?><br />
                    <?php echo __('Tax base:', 'bcdl-invoice') ?><br />
                    <?php echo __('Tax:', 'bcdl-invoice') ?><br />
                    <?php echo __('Payable sum:', 'bcdl-invoice') ?>
                </td>
                <td class="numeric">
                    <?php echo number_format($invoice->getGrandTotal(), 2, '.', ''); ?><br />
                    <?php echo number_format($invoice->getGrandTotal(), 2, '.', ''); ?><br />
                    <?php echo $meta_form['taxrate']; ?><br />
                    <?php echo number_format($invoice->getGrandTotal(), 2, '.', ''); ?><br />
                </td>
            </tr>
    </tbody>
</table>

<?php 
if ($meta_form['taxrate'] === '0') {
    echo '<p class="mainpara">'. __('Base for not applying VAT: ', 'bcdl-invoice') . $meta_form['vatbase'] .'</p>';
};
?>
<p class="mainpara">
    <?php echo __('Payment method: ', 'bcdl-invoice') .  __($meta_form['paymentmethod'], 'bcdl-invoice'); ?><br />
    <?php echo 'IBAN: ' .  $supplier->iban; ?><br />
    <?php echo __('Payment term: ', 'bcdl-invoice') .  bcdl_format_date($dueDate); ?><br />
</p>

<p>&nbsp;</p>
<p class="mainpara">
    <?php echo __('Issuer', 'bcdl-invoice') . ': ' . $meta_form['issuer']; ?>
</p>

<p class="mainpara">
    <?php echo __('Receiver', 'bcdl-invoice') . ': ' . $meta_form['receiver']; ?>
</p>
