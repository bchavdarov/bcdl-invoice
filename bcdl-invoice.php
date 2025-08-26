<?php
/**
 * Plugin Name:       BCDL Invoice
 * Plugin URI:        https://github.com/bchavdarov/bcdl-invoice
 * Description:       A small WordPress plugin that will create your invoices as 'pdf' files. Shortcode [bcdlinvoice].
 * Version:           3.2.2
 * Requires at least: 5.3
 * Requires PHP:      7.3
 * Author:            Boncho Chavdarov / DATTEQ Ltd.
 * Author URI:        https://datteq.com/
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://github.com/bchavdarov/bcdl-invoice
 * Text Domain:       bcdl-invoice
 * Domain Path:       /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/bcdl-invfunctions.php';

function bcdl_invoice() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bcdl_invoice_companies';

    // Fetch all fields except the first company (ID = 1)
    $companies = $wpdb->get_results("SELECT * FROM $table_name WHERE company_id > 1 ORDER BY company_id ASC");
    $resulthtml = '<h2 class="text-center">';
    $resulthtml .= __('INVOICE Form', 'bcdl-invoice');
    $resulthtml .= '</h2>
    <div class="container">

        <form class="mb-3">
          <select id="companyselector" class="form-select mb-3" aria-label="Default select example">
            <option selected>'.__('Select company from the list', 'bcdl-invoice').'</option>';
          if ($companies) {
              foreach ($companies as $c) {
                  $resulthtml .= '<option value="' . esc_attr($c->company_id) . '"'
                      . ' data-name="' . esc_attr($c->company_name) . '"'
                      . ' data-address="' . esc_attr($c->address) . '"'
                      . ' data-crn="' . esc_attr($c->crn) . '"'
                      . ' data-vat="' . esc_attr($c->vat) . '"'
                      . ' data-mrp="' . esc_attr($c->mrp) . '"'
                      . ' data-email="' . esc_attr($c->email) . '"'
                      . ' data-phone="' . esc_attr($c->phone) . '"'
                      . '>'
                      . esc_html($c->company_name)
                      . '</option>';
              }
          } else {
              $resulthtml .= '<option value="">' . __('No customers found', 'bcdl-invoice') . '</option>';
          }
          
    $resulthtml .= '</select>
          <button id="loadCompanyBtn" type="submit" class="btn btn-primary">' . __('Select company', 'bcdl-invoice') . '</button>
        </form>

      <form action="';
    $resulthtml .= plugin_dir_url(__FILE__) . 'bcdl-invoicegen.php';
    $resulthtml .= '" method="post" target="_blank">
        <!-- Customer -->
        <div class="input-group mb-3">
          <span class="input-group-text" id="bcdlcustidspan">';
          $resulthtml .= __('Customer ID', 'bcdl-invoice');
          $resulthtml .= '</span>
          <input type="number" name="custid" id="bcdlcustid" class="form-control" placeholder="' . __('Customer ID', 'bcdl-invoice'). '" aria-describedby="bcdlcustidspan" readonly>
          <span class="input-group-text" id="bcdlcustomerspan">' . __('Customer Name', 'bcdl-invoice'). '</span>
          <input type="text" name="customer" id="bcdlcustomer" class="form-control" placeholder="' . __('Customer Name', 'bcdl-invoice'). '" required aria-describedby="bcdlcustomerspan">
        </div>

        <div class="input-group mb-3">
          <span class="input-group-text" id="bcdladdressspan">' . __('Customer Address', 'bcdl-invoice'). '</span>
          <input type="text" name="address" id="bcdladdress" class="form-control" placeholder="' . __('Customer Address', 'bcdl-invoice'). '" required aria-describedby="bcdladdressspan">

          <span class="input-group-text" id="bcdlcrnspan">' . __('CRN', 'bcdl-invoice'). '</span>
          <input type="text" name="crn" id="bcdlcrn" class="form-control" placeholder="' . __('Company Registration Number', 'bcdl-invoice'). '" required aria-describedby="bcdlcrnspan">
        </div>

        <div class="input-group mb-3">
            <span class="input-group-text" id="bcdlvatspan">' . __('VAT', 'bcdl-invoice'). '</span>
            <input type="text" name="vat" id="bcdlvat" class="form-control" placeholder="' . __('VAT Number', 'bcdl-invoice'). '" aria-describedby="bcdlvatspan">

            <span class="input-group-text" id="bcdlmrpspan">' . __('MRP', 'bcdl-invoice'). '</span>
            <input type="text" name="mrp" id="bcdlmrp" class="form-control" placeholder="' . __('Materially Responsible Person', 'bcdl-invoice'). '" aria-describedby="bcdlmrpspan">
        </div>

        <div class="input-group mb-3">
            <span class="input-group-text" id="bcdlemailspan">' . __('Email', 'bcdl-invoice'). '</span>
            <input type="email" name="email" id="bcdlemail" class="form-control" placeholder="' . __('Email', 'bcdl-invoice'). '" aria-describedby="bcdlemailspan">

            <span class="input-group-text" id="bcdlphonespan">' . __('Phone', 'bcdl-invoice'). '</span>
            <input type="text" name="phone" id="bcdlphone" class="form-control" placeholder="' . __('Phone Number', 'bcdl-invoice'). '" aria-describedby="bcdlphonespan">
        </div>
        
        <!-- Services Table -->
        <table class="table table-bordered" id="servicesTable">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>' . __('Description of the product or service', 'bcdl-invoice'). '</th>
              <th>' . __('Measure', 'bcdl-invoice'). '</th>
              <th>' . __('Quantity', 'bcdl-invoice'). '</th>
              <th>' . __('Unit Price', 'bcdl-invoice'). '</th>
              <th>' . __('Total', 'bcdl-invoice'). '</th>
              <th>' . __('Action', 'bcdl-invoice'). '</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>1</td>
              <td><input type="text" name="description[]" class="form-control" required></td>
              <td><input type="text" name="measure[]" class="form-control" required></td>
              <td><input type="number" name="quantity[]" class="form-control qty" value="1" required placeholder="' . __('Quantity', 'bcdl-invoice'). '" min="0" step="any"></td>
              <td><input type="number" name="unit_price[]" class="form-control price" value="0" required placeholder="' . __('Unit Price', 'bcdl-invoice'). '" min="0" step="any"></td>
              <td class="total">0.00</td>
              <td><button type="button" class="btn btn-danger btn-sm removeRow">' . __('Remove', 'bcdl-invoice'). '</button></td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="5" class="text-end fw-bold">' . __('Grand Total', 'bcdl-invoice'). '</td>
              <td id="grandTotal" class="fw-bold">0.00</td>
              <td></td>
            </tr>
          </tfoot>
        </table>

        <button type="button" class="btn btn-primary btn-sm" id="addRow">' . __('Add Service', 'bcdl-invoice'). '</button>
        <br><br>
        <input id="bcdlpdfsubmitchk" type="hidden" name="bcdlpdfsubmitted" value="0">
        <button type="submit" class="btn btn-success">';
    $resulthtml .= __('Generate invoice', 'bcdl-invoice');
    $resulthtml .= '</button>
      </form>
    </div>
    
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const tableBody = document.querySelector("#servicesTable tbody");
        const addRowBtn = document.getElementById("addRow");
        const grandTotalEl = document.getElementById("grandTotal");

        function updateTotals(row) {
            const qty = parseFloat(row.querySelector(".qty").value) || 0;
            const price = parseFloat(row.querySelector(".price").value) || 0;
            row.querySelector(".total").textContent = (qty * price).toFixed(2);
            updateGrandTotal();
        }

        function updateGrandTotal() {
            let totalSum = 0;
            tableBody.querySelectorAll("tr").forEach(r => {
                totalSum += parseFloat(r.querySelector(".total").textContent) || 0;
            });
            grandTotalEl.textContent = totalSum.toFixed(2);
        }

        function attachEvents(row) {
            row.querySelectorAll(".qty, .price").forEach(input => {
                input.addEventListener("input", () => updateTotals(row));
            });
            row.querySelector(".removeRow").addEventListener("click", () => {
                row.remove();
                Array.from(tableBody.rows).forEach((r, i) => r.cells[0].textContent = i + 1);
                updateGrandTotal();
            });
        }

        addRowBtn.addEventListener("click", () => {
            const rowCount = tableBody.rows.length + 1;
            const newRow = document.createElement("tr");
            newRow.innerHTML = `
              <td>${rowCount}</td>
              <td><input type="text" name="description[]" class="form-control" required></td>
              <td><input type="text" name="measure[]" class="form-control" required></td>
              <td><input type="number" name="quantity[]" class="form-control qty" value="1" required placeholder="Quantity" min="0" step="any"></td>
              <td><input type="number" name="unit_price[]" class="form-control price" value="0" required placeholder="Unit Price" min="0" step="any"></td>
              <td class="total">0.00</td>
              <td><button type="button" class="btn btn-danger btn-sm removeRow">' . __('Remove', 'bcdl-invoice'). '</button></td>
            `;
            tableBody.appendChild(newRow);
            attachEvents(newRow);
        });

        Array.from(tableBody.rows).forEach(attachEvents);
        updateGrandTotal(); // initial calculation
    });
    </script>';
    $resulthtml .= '
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const selector = document.getElementById("companyselector");
        const loadBtn = document.getElementById("loadCompanyBtn");

        loadBtn.addEventListener("click", function(e) {
            e.preventDefault();
            const selected = selector.options[selector.selectedIndex];
            if (!selected.value) return;

            document.getElementById("bcdlcustid").value = selected.value;
            document.getElementById("bcdlcustomer").value = selected.dataset.name || "";
            document.getElementById("bcdladdress").value = selected.dataset.address || "";
            document.getElementById("bcdlcrn").value = selected.dataset.crn || "";
            document.getElementById("bcdlvat").value = selected.dataset.vat || "";
            document.getElementById("bcdlmrp").value = selected.dataset.mrp || "";
            document.getElementById("bcdlemail").value = selected.dataset.email || "";
            document.getElementById("bcdlphone").value = selected.dataset.phone || "";
        });
    });
    </script>';


    return $resulthtml;
}

add_shortcode('bcdlinvoice', 'bcdl_invoice');
