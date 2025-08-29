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



<p>&nbsp;</p>
<table width="100%" class="mainpara">
    <thead>
        <tr>
            <th><?php _e('Description of good or service', 'bcdl-invoice'); ?></th>
            <th class="numeric"><?php _e('Measure', 'bcdl-invoice'); ?></th>
            <th class="numeric"><?php _e('Quantity', 'bcdl-invoice'); ?></th>
            <th class="numeric"><?php _e('Unit Price', 'bcdl-invoice'); ?></th>
            <th class="numeric"><?php _e('Discount', 'bcdl-invoice'); ?></th>
            <th class="numeric"><?php _e('Tax Rate', 'bcdl-invoice'); ?></th>
            <th class="numeric"><?php _e('Total', 'bcdl-invoice'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($invoice->services as $service): ?>
            <?php 
                $lineTotal = ($service->quantity * $service->unitPrice) * (1 - $service->discount/100);
                $lineTotal += $lineTotal * ($service->taxRate/100);
            ?>
            <tr>
                <td><?php echo $service->description; ?></td>
                <td class="numeric"><?php echo $service->measure; ?></td>
                <td class="numeric"><?php echo $service->quantity; ?></td>
                <td class="numeric"><?php echo number_format($service->unitPrice, 2); ?></td>
                <td class="numeric"><?php echo number_format($service->discount, 2); ?>%</td>
                <td class="numeric"><?php echo number_format($service->taxRate, 2); ?>%</td>
                <td class="numeric"><?php echo number_format($lineTotal, 2); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
