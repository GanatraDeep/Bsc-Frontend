<?php include('./includes/head.php'); ?>
<?php $page = "Item Management"; ?>
<?php include('./includes/sidebar.php'); ?>
<main class="main-content">
    <?php include('./includes/navbar.php'); ?>

    <div class="content-inner mt-5 py-0">
        <div>
            <div class="modal fade" id="itemModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Item</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button id="saveCategoryBtn" class="btn btn-primary">Save</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="header-title">
                                <h4 class="card-title">Items</h4>
                            </div>
                            <button id="openAddModal" class="btn btn-primary btn-sm">+ Add Item</button>
                        </div>
                        <div class="card-body px-0">
                            <div class="table-responsive">
                                <table id="user-list-table" class="table table-striped" role="grid"
                                    data-toggle="data-table">
                                    <thead>
                                        <tr class="ligth">
                                            <th>Sr</th>
                                            <th>Name</th>
                                            <th>Sku</th>
                                            <th>Description</th>
                                            <th>Sale Price</th>
                                            <th>Tax %</th>
                                            <th>Category</th>
                                            <th>Is Combo</th>
                                            <th>Active</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemTableBody">
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
        const API_ITEMS = "http://localhost:5000/api/item-categories/item";
        const API_CATEGORIES = "http://localhost:5000/api/item-categories";

        // Elements
        const itemTableBody = document.getElementById("itemTableBody");
        const modalBody = document.querySelector("#itemModal .modal-body");
        const openAddModalBtn = document.getElementById("openAddModal");
        const saveBtn = document.getElementById("saveCategoryBtn");

        let itemModal = new bootstrap.Modal(document.getElementById("itemModal"));

        // Load Categories for Select dropdown
        async function loadCategories() {
            const token = sessionStorage.getItem("token");
            let res = await fetch(`${API_CATEGORIES}`, {
                headers: { Authorization: `Bearer ${token}` }
            });
            return await res.json();
        }

        // Open Modal
        openAddModalBtn.addEventListener("click", async () => {
            let categories = await loadCategories();

            modalBody.innerHTML = `
        <div class="mb-3">
            <label>Item Name</label>
            <input type="text" id="itemName" class="form-control">
        </div>

        <div class="mb-3">
            <label>Category</label>
            <select id="categoryId" class="form-control">
                <option value="">Select Category</option>
                ${categories.map(c => `<option value="${c.id}">${c.name}</option>`).join("")}
            </select>
        </div>

        <div class="mb-3">
            <label>SKU</label>
            <input type="text" id="sku" class="form-control">
        </div>

        <div class="mb-3">
            <label>Description</label>
            <textarea id="description" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label>Sale Price</label>
            <input type="number" id="salePrice" class="form-control">
        </div>

        <div class="mb-3">
            <label>Tax %</label>
            <input type="number" id="tax" class="form-control" value="0">
        </div>

        <div class="mb-3">
            <label>Is Combo?</label>
            <input type="checkbox" id="isCombo">
        </div>
    `;

            itemModal.show();
        });

        // Save Item
        saveBtn.addEventListener("click", async () => {
            const token = sessionStorage.getItem("token");

            let body = {
                franchise_id: 1, // dynamic later
                branch_id: 1, // dynamic later
                category_id: document.getElementById("categoryId").value,
                name: document.getElementById("itemName").value,
                sku: document.getElementById("sku").value,
                description: document.getElementById("description").value,
                sale_price: document.getElementById("salePrice").value,
                tax_percentage: document.getElementById("tax").value,
                is_combo: document.getElementById("isCombo").checked ? 1 : 0
            };

            let res = await fetch(API_ITEMS, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${token}`
                },
                body: JSON.stringify(body)
            });

            if (res.ok) {
                alert("Item Added Successfully");
                itemModal.hide();
                loadItems();
            } else {
                alert("Error saving item");
            }
        });

        // Fetch & Display Items
        async function loadItems() {
            const token = sessionStorage.getItem("token");

            let res = await fetch(`${API_ITEMS}/${sessionStorage.getItem('franchise')}/${sessionStorage.getItem('branch')}`, {
                headers: { Authorization: `Bearer ${token}` }
            });

            let data = await res.json();
            itemTableBody.innerHTML = "";

            data.forEach((item, index) => {
                let row = `
            <tr>
                <td>${index + 1}</td>
                <td>${item.name}</td>
                <td>${item.sku}</td>
                <td>${item.description ? item.description.substring(0,50)+'...' : ''}</td>
                <td>${item.sale_price}</td>
                <td>${item.tax_percentage}</td>
                <td>${item.category_name}</td>
                <td>${(item.is_combo)?"Yes":"No"}</td>
                <td>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" 
                            ${item.active ? "checked" : ""} 
                            onchange="toggleItemStatus(${item.id}, this.checked)">
                    </div>
                </td>
            </tr>
        `;
                itemTableBody.innerHTML += row;
            });
        }

        // Toggle Status
        async function toggleItemStatus(id, active) {
            const token = sessionStorage.getItem("token");

            await fetch(`${API_ITEMS}/status/${id}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${token}`
                },
                body: JSON.stringify({ active: active ? 1 : 0 })
            });
        }

        // Load Items on page load
        loadItems();
    </script>