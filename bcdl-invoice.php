<?php
/**
 * Plugin Name:       BCDL Invoice
 * Plugin URI:        https://github.com/bchavdarov/bcdl-invoice
 * Description:       A small WordPress plugin that will create your invoices as 'pdf' files. Shortcode [bcdlinvoice].
 * Version:           3.0.2
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


function bcdl_invoice() {

    $resulthtml = '<h2 class="text-center">';
    $resulthtml .= __('INVOICE Form', 'bcdl-invoice');
    $resulthtml .= '</h2>
    <div class="container">
      <form action="';
    $resulthtml .= plugin_dir_url(__FILE__) . 'invoicegen.php';
    $resulthtml .= '" method="post" target="_blank">
        <!-- Customer -->
        <div class="input-group mb-3">
          <span class="input-group-text" id="bcdlcustomerspan">Customer</span>
          <input type="text" name="customer" id="bcdlcustomer" class="form-control" placeholder="Customer" aria-label="Customer" aria-describedby="bcdlcustomerspan">
          
          <span class="input-group-text" id="bcdlcrnspan">CRN</span>
          <input type="text" name="companyregnumcust" id="bcdlcompanyregnumcust" class="form-control" placeholder="CRN" aria-label="Company Registration Number" aria-describedby="bcdlcrnspan">
        </div>
        
        <!-- Services Table -->
        <table class="table table-bordered" id="servicesTable">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Description</th>
              <th>Measure</th>
              <th>Quantity</th>
              <th>Unit Price</th>
              <th>Total</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>1</td>
              <td><input type="text" name="description[]" class="form-control" required></td>
              <td><input type="text" name="measure[]" class="form-control" required></td>
              <td><input type="number" name="quantity[]" class="form-control qty" value="1" required placeholder="Quantity" min="0" step="any"></td>
              <td><input type="number" name="unit_price[]" class="form-control price" value="0" required placeholder="Unit Price" min="0" step="any"></td>
              <td class="total">0.00</td>
              <td><button type="button" class="btn btn-danger btn-sm removeRow">Remove</button></td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="5" class="text-end fw-bold">Grand Total</td>
              <td id="grandTotal" class="fw-bold">0.00</td>
              <td></td>
            </tr>
          </tfoot>
        </table>

        <button type="button" class="btn btn-primary btn-sm" id="addRow">Add Service</button>
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
              <td><button type="button" class="btn btn-danger btn-sm removeRow">Remove</button></td>
            `;
            tableBody.appendChild(newRow);
            attachEvents(newRow);
        });

        Array.from(tableBody.rows).forEach(attachEvents);
        updateGrandTotal(); // initial calculation
    });
    </script>';

    return $resulthtml;
}

add_shortcode('bcdlinvoice', 'bcdl_invoice');
