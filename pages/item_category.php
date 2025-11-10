<?php include('./includes/head.php'); ?>
<?php $page = "Item Category Management"; ?>
<?php include('./includes/sidebar.php'); ?>
<main class="main-content">
    <?php include('./includes/navbar.php'); ?>

    <div class="content-inner mt-5 py-0">
        <div>
            <div class="modal fade" id="categoryModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Category</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="categoryId">

                            <div class="mb-3">
                                <label>Name</label>
                                <input type="text" id="categoryName" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label>Parent Category</label>
                                <select id="parentCategory" class="form-control"></select>
                            </div>
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
                                <h4 class="card-title">Categories</h4>
                            </div>
                            <button id="openAddModal" class="btn btn-primary btn-sm">+ Add Category</button>
                        </div>
                        <div class="card-body px-0">
                            <div class="table-responsive">
                                <table id="user-list-table" class="table table-striped" role="grid"
                                    data-toggle="data-table">
                                    <thead>
                                        <tr class="ligth">
                                            <th>Sr</th>
                                            <th>Name</th>
                                            <th>Parent Category</th>
                                            <th>Is Active</th>
                                            <th>Action</th>
                                            <th>Active</th>
                                        </tr>
                                    </thead>
                                    <tbody id="categoryTableBody">
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
        const API_BASE = "http://localhost:5000/api/item-categories"; // CHANGE IF NEEDED

        document.addEventListener('DOMContentLoaded', () => {
            fetchCategories();
            document.getElementById("openAddModal").addEventListener("click", openAddModal);
            document.getElementById("saveCategoryBtn").addEventListener("click", saveCategory);
        });

        async function fetchCategories() {
            try {
                const res = await fetch(API_BASE,{
                    headers: {
                        "Authorization": `Bearer ${sessionStorage.getItem("token")}`
                    }
                });
                const data = await res.json();

                let tbody = document.getElementById("categoryTableBody");
                tbody.innerHTML = "";
                let sr = 1;

                data.forEach(cat => {
                    const row = `
                        <tr>
                            <td>${sr++}</td>
                            <td>${cat.name}</td>
                            <td>${cat.parent_name ?? '-'}</td>
                            <td>${cat.active == 1 ? "✅ Yes" : "❌ No"}</td>
                            <td>
                                <button disabled class="btn btn-sm btn-warning" onclick="editCategory(${cat.id}, '${cat.name}', ${cat.parent_id})">Edit</button>
                            </td>
                            <td>
                                <label class="category-switch">
                                    <input type="checkbox" ${cat.active == 1 ? "checked" : ""} 
                                        onchange="toggleStatus(${cat.id}, this.checked)">
                                    <span class="category-slider"></span>
                                </label>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });

                loadParentDropdown(data);
            } catch (err) {
                console.error("Error fetching categories:", err);
            }
        }

        // ✅ Load parent category dropdown
        function loadParentDropdown(data) {
            let parentSelect = document.getElementById("parentCategory");
            parentSelect.innerHTML = `<option value="">None</option>`;
            data.forEach(c => {
                parentSelect.innerHTML += `<option value="${c.id}">${c.name}</option>`;
            });
        }

        function openAddModal() {
            document.getElementById("categoryId").value = "";
            document.getElementById("categoryName").value = "";
            document.getElementById("parentCategory").value = "";
            $("#categoryModal").modal("show");
        }

        // ✅ Edit mode
        function editCategory(id, name, parent) {
            document.getElementById("categoryId").value = id;
            document.getElementById("categoryName").value = name;
            document.getElementById("parentCategory").value = parent;
            $("#categoryModal").modal("show");
        }

        // ✅ Save / Update Category
        async function saveCategory() {
            const id = document.getElementById("categoryId").value;
            const name = document.getElementById("categoryName").value;
            const parent_id = document.getElementById("parentCategory").value || null;

            const payload = { name, parent_id, "franchise_id": sessionStorage.getItem("franchise") };

            const url = id ? `${API_BASE}/${id}` : API_BASE;
            const method = id ? "PUT" : "POST";

            const res = await fetch(url, {
                method,
                headers: { "Content-Type": "application/json", "Authorization": "Bearer " + sessionStorage.getItem("token") },
                body: JSON.stringify(payload)
            });

            const result = await res.json();
            $("#categoryModal").modal("hide");
            fetchCategories();
        }

        // ✅ Toggle Active Status
        async function toggleStatus(id, active) {
            await fetch(`${API_BASE}/status/${id}`, {
                method: "PUT",
                headers: { "Content-Type": "application/json", "Authorization": "Bearer " + sessionStorage.getItem("token") },
                body: JSON.stringify({ active: active ? 1 : 0 })
            });
        }
    </script>