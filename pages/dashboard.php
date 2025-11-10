<?php include('./includes/head.php'); ?>
<?php $page = "Raw Material Management"; ?>
<?php include('./includes/sidebar.php'); ?>

<main class="main-content">
    <?php include('./includes/navbar.php'); ?>

    <div class="content-inner mt-5 py-0">
        <div>
            <!-- Add Raw Material Modal -->
            <div class="modal fade" id="addRawMaterialModal" tabindex="-1" aria-labelledby="addRawMaterialLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form id="addRawMaterialForm">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addRawMaterialLabel">Add Raw Material</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group mb-3">
                                    <label>Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="add_name" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label>SKU <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="add_sku">
                                </div>
                                <div class="form-group mb-3">
                                    <label>Unit ID <span class="text-danger">*</span></label>
                                    <select required class="form-control" id="add_unit_id">
                                        <option value="">-Select Unit-</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Minimum Quantity <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="add_min_qty">
                                </div>
                                <div class="form-group mb-3">
                                    <label>Cost Price <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="add_cost_price">
                                </div>
                                <div class="form-group mb-3">
                                    <label>Notes</label>
                                    <textarea class="form-control" id="add_notes"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Add</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Raw Material Modal -->
            <div class="modal fade" id="editRawMaterialModal" tabindex="-1" aria-labelledby="editRawMaterialLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form id="editRawMaterialForm">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editRawMaterialLabel">Edit Raw Material</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" id="edit_id">
                                <div class="form-group mb-3">
                                    <label>Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_name" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Minimum Quantity</label>
                                    <input type="number" step="0.01" class="form-control" id="edit_min_qty">
                                </div>
                                <div class="form-group mb-3">
                                    <label>Cost Price</label>
                                    <input type="number" step="0.01" class="form-control" id="edit_cost_price">
                                </div>
                                <div class="form-group mb-3">
                                    <label>Notes</label>
                                    <textarea class="form-control" id="edit_notes"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-warning">Update</button>
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
                                <h4 class="card-title">Raw Materials</h4>
                            </div>
                            <button id="openAddModal" class="btn btn-primary btn-sm">+ Add Raw Material</button>
                        </div>
                        <div class="card-body px-0">
                            <div class="table-responsive">
                                <table id="user-list-table" class="table table-striped" role="grid"
                                    data-toggle="data-table">
                                    <thead>
                                        <tr class="ligth">
                                            <th>Name</th>
                                            <th>SKU</th>
                                            <th>Unit</th>
                                            <th>Minimum Quantity</th>
                                            <th>Cost Price</th>
                                            <th>Active</th>
                                            <th>Notes</th>
                                            <th>Created At</th>
                                            <th style="min-width: 100px">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rawMaterialTableBody">
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
                units.forEach(unit => {
                    $('#add_unit_id').append(new Option(unit.name, unit.id));
                });
            } catch (error) {
                console.error('Error fetching units:', error);
            }
        }
        
        async function fetchRawMaterials() {
            Swal.fire({
                title: 'Fetching data...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

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

                const data = await response.json();
                Swal.close();

                $("#rawMaterialTableBody").empty();

                if (!data || data.length === 0) {
                    $("#rawMaterialTableBody").html('<tr><td colspan="8" class="text-center">No raw materials found.</td></tr>');
                    return;
                }

                data.forEach(material => {
                    const row = `
                    <tr data-id="${material.id}">
                        <td>${material.name}</td>
                        <td>${material.sku}</td>
                        <td>${material.unit_name}</td>
                        <td>${material.minimum_quantity}</td>
                        <td>${material.cost_price}</td>
                        <td><span class="badge bg-${material.active ? 'success' : 'secondary'}">${material.active ? 'Active' : 'Inactive'}</span></td>
                        <td>${material.notes || 'NA'}</td>
                        <td>${new Date(material.created_at).toLocaleDateString()}</td>
                        <td>
                            <div class="flex align-items-center list-user-action">
                                <a class="btn btn-sm btn-icon btn-edit btn-warning" data-toggle="tooltip"
                                    data-placement="top" title="" data-original-title="Edit"
                                    href="#">
                                    <span class="btn-inner">
                                        <svg width="20" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M11.4925 2.78906H7.75349C4.67849 2.78906 2.75049 4.96606 2.75049 8.04806V16.3621C2.75049 19.4441 4.66949 21.6211 7.75349 21.6211H16.5775C19.6625 21.6211 21.5815 19.4441 21.5815 16.3621V12.3341"
                                                stroke="currentColor" stroke-width="1.5"
                                                stroke-linecap="round" stroke-linejoin="round">
                                            </path>
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M8.82812 10.921L16.3011 3.44799C17.2321 2.51799 18.7411 2.51799 19.6721 3.44799L20.8891 4.66499C21.8201 5.59599 21.8201 7.10599 20.8891 8.03599L13.3801 15.545C12.9731 15.952 12.4211 16.181 11.8451 16.181H8.09912L8.19312 12.401C8.20712 11.845 8.43412 11.315 8.82812 10.921Z"
                                                stroke="currentColor" stroke-width="1.5"
                                                stroke-linecap="round" stroke-linejoin="round">
                                            </path>
                                            <path d="M15.1655 4.60254L19.7315 9.16854"
                                                stroke="currentColor" stroke-width="1.5"
                                                stroke-linecap="round" stroke-linejoin="round">
                                            </path>
                                        </svg>
                                    </span>
                                </a>
                                <a data-id="${material.id}" class="btn btn-sm btn-icon btn-danger btn-delete" data-toggle="tooltip"
                                    data-placement="top" title="" data-original-title="Delete"
                                    href="#">
                                    <span class="btn-inner">
                                        <svg width="20" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                            stroke="currentColor">
                                            <path
                                                d="M19.3248 9.46826C19.3248 9.46826 18.7818 16.2033 18.4668 19.0403C18.3168 20.3953 17.4798 21.1893 16.1088 21.2143C13.4998 21.2613 10.8878 21.2643 8.27979 21.2093C6.96079 21.1823 6.13779 20.3783 5.99079 19.0473C5.67379 16.1853 5.13379 9.46826 5.13379 9.46826"
                                                stroke="currentColor" stroke-width="1.5"
                                                stroke-linecap="round" stroke-linejoin="round">
                                            </path>
                                            <path d="M20.708 6.23975H3.75" stroke="currentColor"
                                                stroke-width="1.5" stroke-linecap="round"
                                                stroke-linejoin="round"></path>
                                            <path
                                                d="M17.4406 6.23973C16.6556 6.23973 15.9796 5.68473 15.8256 4.91573L15.5826 3.69973C15.4326 3.13873 14.9246 2.75073 14.3456 2.75073H10.1126C9.53358 2.75073 9.02558 3.13873 8.87558 3.69973L8.63258 4.91573C8.47858 5.68473 7.80258 6.23973 7.01758 6.23973"
                                                stroke="currentColor" stroke-width="1.5"
                                                stroke-linecap="round" stroke-linejoin="round">
                                            </path>
                                        </svg>
                                    </span>
                                </a>
                            </div>
                        </td>
                    </tr>`;
                    $("#rawMaterialTableBody").append(row);
                });

            } catch (error) {
                console.error('Error fetching materials:', error);
                Swal.close();
                Swal.fire('Error', 'Unable to fetch data.', 'error');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            fetchRawMaterials();
            
            fetchUnits();

            $(document).on('click', '#openAddModal', function() {
                $('#addRawMaterialForm')[0].reset();
                $('#addRawMaterialModal').modal('show');
            });

            $(document).on('click', '.btn-edit', function() {
                const row = $(this).closest('tr');
                $('#edit_id').val(row.data('id'));
                $('#edit_name').val(row.find('td:eq(0)').text());
                $('#edit_min_qty').val(row.find('td:eq(3)').text());
                $('#edit_cost_price').val(row.find('td:eq(4)').text());
                $('#edit_notes').val(row.find('td:eq(6)').text());
                $('#editRawMaterialModal').modal('show');
            });

            $(document).on('click', '.btn-delete', function() {
                const row = $(this).closest('tr');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This action cannot be undone.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            const response = await fetch(`http://localhost:5000/api/raw-materials/${row.data('id')}`, {
                                method: 'DELETE',
                                headers: {
                                    'Authorization': `Bearer ${sessionStorage.getItem('token')}`,
                                    'Content-Type': 'application/json'
                                }
                            });

                            const res = await response.json();

                            if (!response.ok) throw new Error(res.message || 'Failed to delete');

                            Swal.fire('Deleted!', 'Raw material has been deleted.', 'success');
                            fetchRawMaterials();

                        } catch (err) {
                            Swal.fire('Error', err.message, 'error');
                        }
                    }
                });
            });

            $('#addRawMaterialForm').on('submit', async function(e) {
                e.preventDefault();

                const name = $('#add_name').val().trim();
                const unit_id = $('#add_unit_id').val();
                const sku = $('#add_sku').val();
                const minimum_quantity = $('#add_min_qty').val();
                const cost_price = $('#add_cost_price').val();

                if (!name || !unit_id || !sku || !minimum_quantity || !cost_price) {
                    Swal.fire('Error', 'Please fill all required fields', 'error');
                    return;
                }

                const data = {
                    franchise_id: sessionStorage.getItem('franchise'),
                    branch_id: sessionStorage.getItem('branch'),
                    name,
                    sku,
                    default_unit_id: unit_id,
                    minimum_quantity,
                    cost_price,
                    notes: $('#add_notes').val()
                };

                try {
                    const response = await fetch('http://localhost:5000/api/raw-materials', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${sessionStorage.getItem('token')}`
                        },
                        body: JSON.stringify(data)
                    });

                    const res = await response.json();

                    if (!response.ok) throw new Error(res.message || 'Failed to add');

                    Swal.fire('Success', 'Raw material added successfully', 'success');
                    $('#addRawMaterialModal').modal('hide');
                    fetchRawMaterials();

                } catch (err) {
                    Swal.fire('Error', err.message, 'error');
                }
            });
            
            $('#editRawMaterialForm').on('submit', async function(e) {
                e.preventDefault();
                
                const id = $('#edit_id').val();
                const name = $('#edit_name').val().trim();

                if (!id || !name) {
                    Swal.fire('Error', 'Please fill all required fields', 'error');
                    return;
                }

                const data = {
                    name,
                    minimum_quantity: $('#edit_min_qty').val(),
                    cost_price: $('#edit_cost_price').val(),
                    notes: $('#edit_notes').val()
                };

                try {
                    const response = await fetch(`http://localhost:5000/api/raw-materials/${id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${sessionStorage.getItem('token')}`
                        },
                        body: JSON.stringify(data)
                    });

                    const res = await response.json();

                    if (!response.ok) throw new Error(res.message || 'Failed to update');

                    Swal.fire('Success', 'Raw material updated successfully', 'success');
                    $('#editRawMaterialModal').modal('hide');
                    fetchRawMaterials();

                } catch (err) {
                    Swal.fire('Error', err.message, 'error');
                }
            });
        });
    </script>