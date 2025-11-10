<?php include('./includes/head.php'); ?>
<?php $page = "Raw Material Purchase"; ?>
<?php include('./includes/sidebar.php'); ?>
<style>
    .items-cell {
        max-width: 350px;
        white-space: normal;
    }

    .item-badge {
        font-size: 11px;
        padding: 6px 8px;
        border-radius: 12px;
        cursor: pointer;
        display: inline-block;
    }
    .popover {
        max-width: 200px;
    }
</style>
<main class="main-content">
    <?php include('./includes/navbar.php'); ?>

    <div class="content-inner mt-5 py-0">
        <div>
            <!-- Add Raw Material Modal -->
            <div class="modal fade" id="addPurchaseModal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                    <form id="addPurchaseForm">
                        <div class="modal-header">
                        <h5 class="modal-title">Add Purchase</h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                            <label>Invoice No *</label>
                            <input type="text" class="form-control" name="invoice_no" required>
                            </div>
                            <div class="col-md-4">
                            <label>Invoice Date *</label>
                            <input type="date" class="form-control" name="invoice_date" required>
                            </div>
                            <div class="col-md-4">
                            <label>Notes</label>
                            <input type="text" class="form-control" name="remarks">
                            </div>
                        </div>

                        <table class="table table-bordered" id="purchaseItemsTable">
                            <thead>
                            <tr>
                                <th>Raw Material</th>
                                <th>Unit</th>
                                <th>Qty *</th>
                                <th>Unit Price *</th>
                                <th>Total</th>
                                <th><button type="button" class="btn btn-sm btn-success" id="addRowBtn">+</button></th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>

                        </div>

                        <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary" type="submit">Save Purchase</button>
                        </div>
                    </form>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="header-title">
                                <h4 class="card-title">Raw Materials Purchases</h4>
                            </div>
                            <button id="openAddModal" class="btn btn-primary btn-sm">+ Add Purchase</button>
                        </div>
                        <div class="card-body px-0">
                            <div class="table-responsive">
                                <table id="user-list-table" class="table table-striped" role="grid"
                                    data-toggle="data-table">
                                    <thead>
                                        <tr class="ligth">
                                            <th>Sr.</th>
                                            <th>Date</th>
                                            <th>Invoice No.</th>
                                            <th>Total Amount</th>
                                            <th>Tax</th>
                                            <th>Created By</th>
                                            <th>Items</th>
                                            <th>Notes</th>
                                            <th>Created At</th>
                                        </tr>
                                    </thead>
                                    <tbody id="purchasesTableBody">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include('./includes/footer.php'); ?>
    <script>
        let units = [];
        let rawMaterials = [];
        async function fetchUnits() {
            try {
                const response = await fetch(`http://localhost:5000/api/units`, {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${sessionStorage.getItem('token')}`,
                        'Content-Type': 'application/json'
                    }
                });
                if (!response.ok) {
                    if (response.status === 401) {
                        Swal.fire('Session expired', 'Please log in again.', 'warning').then(() => {
                            sessionStorage.clear();
                            window.location.href = 'index.php';
                        });
                        return;
                    }
                    throw new Error(`HTTP error: ${response.status}`);
                }
                units = await response.json();
            } catch (error) {
                console.error('Error fetching units:', error);
            }
        }

        async function fetchRawMaterials() {

            try {
                const response = await fetch(`http://localhost:5000/api/raw-materials/${sessionStorage.getItem('franchise')}`, {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${sessionStorage.getItem('token')}`,
                        'Content-Type': 'application/json'
                    }
                });

                if (!response.ok) {
                    if (response.status === 401) {
                        Swal.close();
                        Swal.fire('Session expired', 'Please log in again.', 'warning').then(() => {
                            sessionStorage.clear();
                            window.location.href = 'index.php';
                        });
                        return;
                    }
                    throw new Error(`HTTP error: ${response.status}`);
                }
                rawMaterials = await response.json();
            } catch (error) {
                console.error('Error fetching materials:', error);
                Swal.close();
                Swal.fire('Error', 'Unable to fetch data.', 'error');
            }
        }
        
        async function fetchPurchases() {
            Swal.fire({
                title: 'Fetching data...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const response = await fetch(`http://localhost:5000/api/purchases/${sessionStorage.getItem('franchise')}`, {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${sessionStorage.getItem('token')}`,
                        'Content-Type': 'application/json'
                    }
                });

                if (!response.ok) {
                    if (response.status === 401) {
                        Swal.close();
                        Swal.fire('Session expired', 'Please log in again.', 'warning').then(() => {
                            sessionStorage.clear();
                            window.location.href = 'index.php';
                        });
                        return;
                    }
                    throw new Error(`HTTP error: ${response.status}`);
                }

                const data = await response.json();
                Swal.close();

                $("#purchasesTableBody").empty();

                if (!data || data.length === 0) {
                    $("#purchasesTableBody").html('<tr><td colspan="8" class="text-center">No raw materials purchase found.</td></tr>');
                    return;
                }

                let sr = 1;
                data.forEach(purchase => {
                    let itemsHtml = purchase.items.map(r => {
                        return `
                            <span 
                                class="badge bg-light text-dark border me-1 mb-1 item-badge pop-item"
                                data-bs-toggle="popover"
                                data-bs-html="true"
                                data-bs-content="
                                    <b>${r.raw_material_name}</b><br>
                                    Qty: ${r.quantity}<br>
                                    Unit Price: ${r.unit_price}<br>
                                    Total: ${(r.quantity * r.unit_price).toFixed(2)}
                                "
                            >
                                ${r.raw_material_name}
                                <span class="text-muted small">(${r.quantity})</span>
                            </span>
                        `;
                    }).join("");

                    const row = `
                    <tr data-id="${purchase.id}">
                        <td>${sr++}</td>
                        <td>${purchase.invoice_date}</td>
                        <td>${purchase.invoice_no}</td>
                        <td>${purchase.total_amount}</td>
                        <td>${purchase.tax_amount}</td>
                        <td>${purchase.first_name}</td>
                        <td class="items-cell d-flex flex-wrap">${itemsHtml}</td>
                        <td>${purchase.remarks || 'NA'}</td>
                        <td>${new Date(purchase.created_at).toLocaleDateString()}</td>
                    </tr>`;

                    $("#purchasesTableBody").append(row);
                });

                // Activate popovers after render
                const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
                popovers.forEach(el => {
                    new bootstrap.Popover(el);
                });

            } catch (error) {
                console.error('Error fetching materials:', error);
                Swal.close();
                Swal.fire('Error', 'Unable to fetch data.', 'error');
            }
        }
        
        document.addEventListener('DOMContentLoaded', () => {
            fetchPurchases();
            fetchRawMaterials();
            fetchUnits();

            $("#openAddModal").click(()=>{
                $('#addPurchaseModal').modal('show');
            })
        });

        function addPurchaseRow() {
            const tbody = $("#purchaseItemsTable tbody");
            
            let rawOptions = rawMaterials.map(r => `<option value="${r.id}" data-unit="${r.unit_name}">${r.name}</option>`).join("");

            const tr = `
            <tr>
                <td><select class="form-control raw-select" required>${rawOptions}</select></td>

                <td><input class="form-control unit" type="text" readonly></td>

                <td><input class="form-control qty" type="number" step="0.01" min="0.01" required></td>

                <td><input class="form-control price" type="number" step="0.01" min="0.01" required></td>

                <td><input class="form-control total" type="number" step="0.01" readonly></td>

                <td><button class="btn btn-danger btn-sm removeRowBtn">X</button></td>
            </tr>`;

            tbody.append(tr);

            $(".raw-select").trigger("change");
        }

        $("#addRowBtn").on("click", addPurchaseRow);

        $(document).on("click", ".removeRowBtn", function () {
            $(this).closest("tr").remove();
        });

        $(document).on("change", ".raw-select", function () {
            let unitName = $(this).find(":selected").data("unit");
            $(this).closest("tr").find(".unit").val(unitName);
        });

        $(document).on("input", ".qty, .price", function () {
            let row = $(this).closest("tr");
            let qty = parseFloat(row.find(".qty").val()) || 0;
            let price = parseFloat(row.find(".price").val()) || 0;
            row.find(".total").val((qty * price).toFixed(2));
        });

        $("#addPurchaseForm").on("submit", async function (e) {
            e.preventDefault();

            const invoice_no = $("[name='invoice_no']").val().trim();
            const invoice_date = $("[name='invoice_date']").val();
            const remarks = $("[name='remarks']").val();

            let items = [];
            let valid = true;

            $("#purchaseItemsTable tbody tr").each(function () {
                let raw_material_id = $(this).find(".raw-select").val();
                let qty = $(this).find(".qty").val();
                let price = $(this).find(".price").val();

                if (!raw_material_id || qty <= 0 || price <= 0) valid = false;

                items.push({
                    raw_material_id,
                    quantity: qty,
                    unit_price: price
                });
            });

            if (!valid || items.length === 0) {
                return Swal.fire("Error", "Please fill all fields correctly!", "error");
            }

            const payload = {
                franchise_id: sessionStorage.getItem("franchise"),
                branch_id: sessionStorage.getItem("branch"), // ✅ REQUIRED
                invoice_no,
                invoice_date,
                remarks,
                items
            };

            try {
                const res = await fetch("http://localhost:5000/api/purchases", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Authorization: `Bearer ${sessionStorage.getItem("token")}`
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (!res.ok) throw new Error(data.message);

                Swal.fire("Success", "Purchase added successfully!", "success");
                $("#addPurchaseModal").modal("hide");
                fetchPurchases();

            } catch (err) {
                Swal.fire("Error", err.message, "error");
            }
        });
    </script>