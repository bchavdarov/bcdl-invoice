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

<table class="mainpara" border="0">
    <tr><th>ID</th><td><?php echo($customer->id); ?></td></tr>
    <tr><th>Name</th><td><?php echo($customer->name); ?></td></tr>
    <tr><th>Address</th><td><?php echo( nl2br($customer->address) ); ?></td></tr>
    <tr><th>CRN</th><td><?php echo($customer->crn); ?></td></tr>
    <tr><th>VAT</th><td><?php echo($customer->vat); ?></td></tr>
    <tr><th>MRP</th><td><?php echo($customer->mrp); ?></td></tr>
    <tr><th>Email</th><td><?php echo($customer->email); ?></td></tr>
    <tr><th>Phone</th><td><?php echo($customer->phone); ?></td></tr>
    <tr><th>Phone</th><td><?php echo($customer->iban); ?></td></tr>
</table>

<table class="mainpara" border="0">
    <tr><th>ID</th><td><?php echo($supplier->id); ?></td></tr>
    <tr><th>Name</th><td><?php echo($supplier->name); ?></td></tr>
    <tr><th>Address</th><td><?php echo( nl2br($supplier->address) ); ?></td></tr>
    <tr><th>CRN</th><td><?php echo($supplier->crn); ?></td></tr>
    <tr><th>VAT</th><td><?php echo($supplier->vat); ?></td></tr>
    <tr><th>MRP</th><td><?php echo($supplier->mrp); ?></td></tr>
    <tr><th>Email</th><td><?php echo($supplier->email); ?></td></tr>
    <tr><th>Phone</th><td><?php echo($supplier->phone); ?></td></tr>
    <tr><th>Phone</th><td><?php echo($supplier->iban); ?></td></tr>
</table>
