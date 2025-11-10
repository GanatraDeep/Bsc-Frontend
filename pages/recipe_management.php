<?php include('./includes/head.php'); ?>
<?php $page = "Recipe Management"; ?>
<?php include('./includes/sidebar.php'); ?>
<main class="main-content">
    <?php include('./includes/navbar.php'); ?>

    <div class="content-inner mt-5 py-0">

        <!-- ✅ Recipe Modal -->
        <div class="modal fade" id="recipeModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Recipe</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body" id="recipeBody"></div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button id="saveRecipe" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ✅ Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4>Recipes</h4>
                <button class="btn btn-primary btn-sm" id="newRecipe">+ Add Recipe</button>
            </div>
            <div class="card-body px-0">
                <div class="table-responsive">
                    <table class="table table-striped" id="recipeTable" data-toggle="data-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Yield Qty</th>
                                <th>Raw Materials</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="recipeBodyTable"></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <?php include('./includes/footer.php'); ?>

    <script>
        const API = "http://localhost:5000/api/recipes";
        const ITEM_API = "http://localhost:5000/api/item-categories/item";
        const RAW_API = "http://localhost:5000/api/raw-materials";
        const UNIT_API = "http://localhost:5000/api/units";

        const token = sessionStorage.getItem("token");
        const fid = sessionStorage.getItem("franchise");
        const bid = sessionStorage.getItem("branch");

        let recipeModal = new bootstrap.Modal(document.getElementById("recipeModal"));
        let itemsList = [];
        let rawMaterials = [];
        let units = [];

        async function loadUnits() {
            let res = await fetch(UNIT_API, {
                headers: { Authorization: `Bearer ${token}` }
            });
            units = await res.json();
        }

        async function loadRawMaterials() {
            let res = await fetch(`${RAW_API}/${fid}`, {
                headers: { Authorization: `Bearer ${token}` }
            });
            rawMaterials = await res.json();
        }

        // ✅ Load Items for dropdown
        async function loadItems() {
            let res = await fetch(`${ITEM_API}/${fid}/${bid}`, {
                headers: { Authorization: `Bearer ${token}` }
            });
            itemsList = await res.json();
        }

        // ✅ Load Recipes Table
        async function loadRecipes() {
            try {
                let res = await fetch(`${API}?franchise_id=${fid}&branch_id=${bid}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });
                let data = await res.json();
                let html = "";

                data.forEach(r => {
                    let rm = r.items?.map(i => `${i.raw_material_name} (${i.quantity})`).join("<br>") || "";

                    html += `
                <tr>
                    <td>${r.item_name}</td>
                    <td>${r.yield_qty}</td>
                    <td>${rm}</td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick='editRecipe(${JSON.stringify(r)})'>Edit</button>
                    </td>
                </tr>`;
                });

                document.getElementById("recipeBodyTable").innerHTML = html;

            } catch (e) {
                console.log(e);
                alert("Error loading recipes");
            }
        }

        // ✅ Open Create / Edit Modal
        function openRecipeForm(data = {}) {

            let rowsHTML = "";
            (data.items || [{ raw_material_id: "", quantity: "", unit_id: "", optional: 0, comment: "" }]).forEach((i, idx) => {
                rowsHTML += ingredientRowHTML(idx, i);
            });

            document.getElementById("recipeBody").innerHTML = `
        <label>Final Item</label>
        <select id="item_id" class="form-control mb-2">
            <option value="">Select Item</option>
            ${itemsList.map(it => `<option value="${it.id}" ${data.item_id == it.id ? "selected" : ""}>${it.name}</option>`)}
        </select>

        <label>Yield Quantity</label>
        <input type="number" id="yield_qty" value="${data.yield_qty || 1}" class="form-control mb-2">

        <label>Notes</label>
        <textarea id="notes" class="form-control mb-2">${data.notes || ""}</textarea>

        <h6>Ingredients</h6>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Item</th><th>Qty</th><th>Unit</th><th>Optional</th><th>Comment</th><th></th>
                </tr>
            </thead>
            <tbody id="ingredientRows">${rowsHTML}</tbody>
        </table>

        <button class="btn btn-secondary btn-sm" onclick="addIngredientRow()">+ Add Ingredient</button>
    `;

            recipeModal.show();
            document.getElementById("saveRecipe").onclick = () => saveRecipe(data.id);
        }

        // ✅ Ingredient Row Template
        function ingredientRowHTML(idx, i) {
            return `
    <tr data-index="${idx}">
        <td>
            <select class="form-control rm_id" onchange="setUnit(this)">
                <option value="">Select</option>
                ${rawMaterials.map(rm => `<option value="${rm.id}" ${i.raw_material_id == rm.id ? "selected" : ""}>${rm.name}</option>`)}
            </select>
        </td>

        <td><input type="number" class="form-control qty" value="${i.quantity || ""}"></td>

        <td>
            <select class="form-control unit" readonly>
                <option value="">Unit</option>
                ${units.map(rm => `<option value="${rm.id}" ${i.unit_id == rm.id ? "selected" : ""}>${rm.name}</option>`)}
            </select>
        </td>

        <td class="text-center">
            <input type="checkbox" class="opt" ${i.optional ? "checked" : ""}>
        </td>

        <td><input class="form-control comment" value="${i.comment || ""}"></td>
        <td><button class="btn btn-danger btn-sm" onclick="removeRow(this)">X</button></td>
    </tr>`;
        }

        function setUnit(selectElement) {
            const row = selectElement.closest("tr");
            const unitSelect = row.querySelector(".unit");
            const selectedRM = rawMaterials.find(r => r.id == selectElement.value);

            if (selectedRM) {
                unitSelect.value = selectedRM.default_unit_id; // raw material's default unit
            }
        }

        // ✅ Add Ingredient Row
        function addIngredientRow() {
            const idx = document.querySelectorAll("#ingredientRows tr").length;
            document.getElementById("ingredientRows").insertAdjacentHTML("beforeend",
                ingredientRowHTML(idx, { raw_material_id: "", quantity: "", unit_id: "", optional: 0, comment: "" })
            );
        }

        // ✅ Remove Row
        function removeRow(btn) {
            btn.closest("tr").remove();
        }

        // ✅ Save Recipe
        async function saveRecipe(id) {
            let rows = [...document.querySelectorAll("#ingredientRows tr")].map(r => ({
                raw_material_id: r.querySelector(".rm_id").value,
                quantity: r.querySelector(".qty").value,
                unit_id: r.querySelector(".unit").value,
                optional: r.querySelector(".opt").checked ? 1 : 0,
                comment: r.querySelector(".comment").value
            }));

            let body = {
                item_id: document.getElementById("item_id").value,
                yield_qty: document.getElementById("yield_qty").value,
                notes: document.getElementById("notes").value,
                items: rows
            };

            let res = await fetch(id ? `${API}/${id}` : API, {
                method: id ? "PUT" : "POST",
                headers: { "Content-Type": "application/json", Authorization: `Bearer ${token}` },
                body: JSON.stringify(body)
            });

            if (res.ok) {
                alert("Recipe saved");
                recipeModal.hide();
                loadRecipes();
            } else {
                alert("Error saving recipe");
            }
        }

        // ✅ Edit Action
        function editRecipe(r) {
            openRecipeForm(r);
        }

        // ✅ Init
        (async () => {
            await loadItems();
            await loadRawMaterials();
            await loadUnits();
            loadRecipes();
            loadRecipes();
            document.getElementById("newRecipe").addEventListener("click", () => openRecipeForm());
        })();
    </script>