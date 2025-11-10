<?php include('./includes/head.php'); ?>
<?php $page = "Stock Level"; ?>
<?php include('./includes/sidebar.php'); ?>
<main class="main-content">
    <?php include('./includes/navbar.php'); ?>

    <div class="content-inner mt-5 py-0">
        <div>
            <!-- Stock Transaction History Modal -->
            <div class="modal fade" id="stockHistoryModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Stock Transactions</h5>
                            <button class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">

                            <div class="table-responsive" style="max-height:400px; overflow-y:auto;">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-light" style="position: sticky; top: 0; z-index: 5;">
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Qty</th>
                                            <th>Unit</th>
                                            <th>Note</th>
                                            <th>By</th>
                                        </tr>
                                    </thead>
                                    <tbody id="historyBody"></tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <!-- Stock Adjustment Modal -->
            <div class="modal fade" id="addPurchaseModal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <form id="addPurchaseForm">
                            <div class="modal-header">
                                <h5 class="modal-title">Stock Adjustment</h5> <button class="btn-close"
                                    data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="modal-body">
                                    <div class="alert alert-info">
                                        <strong>Note:</strong> Enter negative quantity to reduce stock.
                                        <br> Example: <code>-5</code> to reduce 5 units.
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">Raw Material</label>
                                            <select class="form-control" id="raw_material_id" required>
                                                <option value="">Select Material</option>
                                            </select>
                                        </div>

                                        <div class="col-md-3 mb-2">
                                            <label class="form-label">Quantity</label>
                                            <input type="number" class="form-control" id="quantity" step="0.01"
                                                placeholder="e.g., 10 or -5" required>
                                        </div>

                                        <div class="col-md-12 mb-2">
                                            <label class="form-label">Note (Optional)</label>
                                            <input type="text" class="form-control" id="note"
                                                placeholder="Reason for adjustment">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer"> <button class="btn btn-secondary"
                                    data-bs-dismiss="modal">Cancel</button> <button class="btn btn-primary"
                                    type="submit">Save Adjustment</button> </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="header-title">
                                <h4 class="card-title">Stock Levels</h4>
                            </div>
                            <button id="openAddModal" class="btn btn-primary btn-sm">+ Stock Adjustment</button>
                        </div>
                        <div class="card-body px-0">
                            <div class="table-responsive">
                                <table id="user-list-table" class="table table-striped" role="grid"
                                    data-toggle="data-table">
                                    <thead>
                                        <tr class="ligth">
                                            <th>Sr.</th>
                                            <th>Item</th>
                                            <th>Current Qty</th>
                                            <th>Unit</th>
                                        </tr>
                                    </thead>
                                    <tbody id="stockTableBody">
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

                loadRawMaterialDropdown();
            } catch (error) {
                console.error('Error fetching materials:', error);
                Swal.close();
                Swal.fire('Error', 'Unable to fetch data.', 'error');
            }
        }
        async function fetchStockLevels() {
            Swal.fire({
                title: 'Fetching data...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const response = await fetch(`http://localhost:5000/api/stock/${sessionStorage.getItem('franchise')}/${sessionStorage.getItem('branch')}`, {
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

                $("#stockTableBody").empty();

                if (!data || data.length === 0) {
                    $("#stockTableBody").html('<tr><td colspan="4" class="text-center">No raw materials purchase found.</td></tr>');
                    return;
                }

                let sr = 1;
                data.forEach(stock => {
                    const row = `
                    <tr class="stock-row" data-material="${stock.raw_material_id}">
                        <td>${sr++}</td>
                        <td>${stock.raw_material_name}</td>
                        <td>${parseFloat(stock.quantity).toFixed(2)}</td>
                        <td>${stock.unit_name}</td>
                    </tr>`;

                    $("#stockTableBody").append(row);
                });

            } catch (error) {
                console.error('Error fetching materials:', error);
                Swal.close();
                Swal.fire('Error', 'Unable to fetch data.', 'error');
            }
        }
        function loadRawMaterialDropdown() {
            const select = document.getElementById("raw_material_id");
            select.innerHTML = `<option value="">Select Material</option>`;

            rawMaterials.forEach(rm => {
                select.innerHTML += `<option value="${rm.id}">${rm.name}</option>`;
            });
        }
        document.addEventListener('DOMContentLoaded', () => {
            fetchStockLevels();

            fetchRawMaterials();
            fetchUnits();

            $("#openAddModal").click(() => {
                $('#addPurchaseModal').modal('show');
            })
        });

        $("#addPurchaseForm").submit(async function (e) {
            e.preventDefault();

            const branch_id = sessionStorage.getItem("branch");
            const raw_material_id = $("#raw_material_id").val();
            const quantity = $("#quantity").val();
            const note = $("#note").val();

            if (!raw_material_id || !quantity) {
                return Swal.fire("Error", "Please select material & enter quantity.", "warning");
            }

            Swal.fire({
                title: 'Saving...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const res = await fetch(`http://localhost:5000/api/stock/adjust`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Authorization": `Bearer ${sessionStorage.getItem("token")}`
                    },
                    body: JSON.stringify({
                        branch_id,
                        raw_material_id,
                        quantity,
                        note
                    })
                });

                const data = await res.json();
                Swal.close();

                if (!res.ok) {
                    return Swal.fire("Error", data.message || "Stock update failed", "error");
                }

                Swal.fire("Success", "Stock Adjustment Applied!", "success");

                $("#addPurchaseModal").modal("hide");
                $("#addPurchaseForm")[0].reset();

                await fetchStockLevels(); // refresh table

            } catch (err) {
                Swal.close();
                Swal.fire("Error", "Something went wrong", "error");
                console.log(err);
            }
        });
        $(document).on("click", ".stock-row", function () {
            const raw_material_id = $(this).data("material");
            loadStockHistory(raw_material_id);
        });
        async function loadStockHistory(raw_material_id) {
            $("#historyBody").html(`<tr><td colspan="6" class="text-center">Loading...</td></tr>`);
            $("#stockHistoryModal").modal("show");

            const franchise_id = sessionStorage.getItem("franchise");
            const branch_id = sessionStorage.getItem("branch");

            try {
                const res = await fetch(`http://localhost:5000/api/stock/transactions/${franchise_id}/${branch_id}?raw_material_id=${raw_material_id}`, {
                    headers: {
                        "Authorization": `Bearer ${sessionStorage.getItem("token")}`
                    }
                });

                const data = await res.json();

                if (!res.ok) {
                    $("#historyBody").html(`<tr><td colspan="6" class="text-center">${data.message}</td></tr>`);
                    return;
                }

                if (!data.length) {
                    $("#historyBody").html(`<tr><td colspan="6" class="text-center">No transactions found</td></tr>`);
                    return;
                }

                $("#historyBody").empty();
                data.forEach(t => {
                    const sign = t.quantity > 0 ? "+" : "";
                    const typeBadge = t.txn_type === "ADJUSTMENT"
                        ? `<span class="badge bg-warning">Adj</span>`
                        : `<span class="badge bg-info">${t.txn_type}</span>`;

                    $("#historyBody").append(`
                        <tr>
                            <td>${new Date(t.created_at).toLocaleString()}</td>
                            <td>${typeBadge}</td>
                            <td>${sign}${parseFloat(t.quantity).toFixed(2)}</td>
                            <td>${t.unit_name}</td>
                            <td>${t.note || "-"}</td>
                            <td>${t.done_by || "System"}</td>
                        </tr>
                    `);
                });

            } catch (err) {
                $("#historyBody").html(`<tr><td colspan="6" class="text-center text-danger">Failed to load history</td></tr>`);
                console.error(err);
            }
        }
    </script>