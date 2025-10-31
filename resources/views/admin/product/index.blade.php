@extends('layouts.app')

@section('header_css')
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('main_content')
<div class="container-fluid">
    <div class="alerts my-2"></div>

    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Products</h5>
            <div>
                <button class="btn btn-success" id="btnOpenCreate">Add Product</button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped table-bordered" id="products-table" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th>Description</th>
                        <th>Created At</th>
                        <th style="width:160px">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="modalCreate" tabindex="-1" aria-labelledby="modalCreateLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form id="formCreate" autocomplete="off">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalCreateLabel">Add Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="create-errors" class="alert alert-danger d-none"></div>

            <div class="mb-3">
                <label for="create_name" class="form-label">Name</label>
                <input type="text" class="form-control" id="create_name" name="name" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="create_category_id" class="form-label">Category</label>
                    <select id="create_category_id" name="category_id" class="form-select">
                        <option value="">-- select category --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="create_unit" class="form-label">Unit</label>
                    <select id="create_unit" name="unit" class="form-select">
                        <option value="">-- select unit --</option>
                        @foreach($units as $u)
                            <option value="{{ $u }}">{{ $u }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="create_description" class="form-label">Description</label>
                <textarea class="form-control" id="create_description" name="description" rows="3"></textarea>
            </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary" id="btnCreateSubmit">Save</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form id="formEdit" autocomplete="off">
      @csrf
      @method('PUT')
      <input type="hidden" id="edit_id" name="id">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalEditLabel">Edit Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="edit-errors" class="alert alert-danger d-none"></div>

            <div class="mb-3">
                <label for="edit_name" class="form-label">Name</label>
                <input type="text" class="form-control" id="edit_name" name="name" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="edit_category_id" class="form-label">Category</label>
                    <select id="edit_category_id" name="category_id" class="form-select">
                        <option value="">-- select category --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="edit_unit" class="form-label">Unit</label>
                    <select id="edit_unit" name="unit" class="form-select">
                        <option value="">-- select unit --</option>
                        @foreach($units as $u)
                            <option value="{{ $u }}">{{ $u }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="edit_description" class="form-label">Description</label>
                <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
            </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary" id="btnEditSubmit">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Show Modal -->
<div class="modal fade" id="modalShow" tabindex="-1" aria-labelledby="modalShowLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalShowLabel">Product Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <table class="table table-sm table-borderless">
                <tbody>
                    <tr><th>Name</th><td id="show_name"></td></tr>
                    <tr><th>Category</th><td id="show_category"></td></tr>
                    <tr><th>Unit</th><td id="show_unit"></td></tr>
                    <tr><th>Description</th><td id="show_description"></td></tr>
                    <tr><th>Created</th><td id="show_created_at"></td></tr>
                </tbody>
            </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
  </div>
</div>

@endsection

@section('footer_js_links')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
@endsection

@section('footer_js')
<script>
$(function() {
    var modalCreate = new bootstrap.Modal(document.getElementById('modalCreate'));
    var modalEdit = new bootstrap.Modal(document.getElementById('modalEdit'));
    var modalShow = new bootstrap.Modal(document.getElementById('modalShow'));

    function showAlert(type, message) {
        var html = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
            message +
            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
            '</div>';
        $('.alerts').html(html);
        setTimeout(function(){ $('.alerts .alert').alert('close'); }, 5000);
    }

    var table = $('#products-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("products.index") }}',
            dataSrc: 'data'
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'category_name', name: 'category_name' },
            { data: 'unit', name: 'unit' },
            { data: 'description', name: 'description' },
            { data: 'created_at', name: 'created_at' },
            { data: 'actions', name: 'actions', orderable:false, searchable:false }
        ],
        order: [[0,'desc']],
        responsive: true
    });

    // Open Create modal
    $('#btnOpenCreate').on('click', function(){
        $('#formCreate')[0].reset();
        $('#create-errors').addClass('d-none').html('');
        modalCreate.show();
    });

    // Submit Create (AJAX)
    $('#formCreate').on('submit', function(e){
        e.preventDefault();
        $('#btnCreateSubmit').prop('disabled', true);
        $('#create-errors').addClass('d-none').html('');

        var formData = $(this).serialize();

        $.ajax({
            url: '{{ route("products.store") }}',
            method: 'POST',
            data: formData,
            success: function(res) {
                showAlert('success', res.message || 'Product created.');
                modalCreate.hide();
                table.ajax.reload(null, false);
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    var html = '<ul class="mb-0">';
                    $.each(errors, function(k,v){
                        html += '<li>' + v[0] + '</li>';
                    });
                    html += '</ul>';
                    $('#create-errors').removeClass('d-none').html(html);
                } else {
                    showAlert('danger', 'Server error. Try again.');
                }
            },
            complete: function() {
                $('#btnCreateSubmit').prop('disabled', false);
            }
        });
    });

    // Click Edit button: fetch data and open edit modal
    $('#products-table').on('click', '.btn-edit', function(){
        var id = $(this).data('id');
        $('#edit-errors').addClass('d-none').html('');
        $.ajax({
            url: '/products/' + id + '/edit',
            method: 'GET',
            success: function(res) {
                if (res.product) {
                    $('#edit_id').val(res.product.id);
                    $('#edit_name').val(res.product.name);
                    $('#edit_category_id').val(res.product.category_id);
                    $('#edit_unit').val(res.product.unit);
                    $('#edit_description').val(res.product.description);
                    modalEdit.show();
                } else {
                    showAlert('danger','Could not load product data.');
                }
            },
            error: function() {
                showAlert('danger','Could not load product data.');
            }
        });
    });

    // Submit Edit (AJAX)
    $('#formEdit').on('submit', function(e){
        e.preventDefault();
        $('#btnEditSubmit').prop('disabled', true);
        $('#edit-errors').addClass('d-none').html('');

        var id = $('#edit_id').val();
        var formData = $(this).serialize();

        $.ajax({
            url: '/products/' + id,
            method: 'POST',
            data: formData,
            success: function(res) {
                showAlert('success', res.message || 'Product updated.');
                modalEdit.hide();
                table.ajax.reload(null, false);
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    var html = '<ul class="mb-0">';
                    $.each(errors, function(k,v){
                        html += '<li>' + v[0] + '</li>';
                    });
                    html += '</ul>';
                    $('#edit-errors').removeClass('d-none').html(html);
                } else {
                    showAlert('danger', 'Server error. Try again.');
                }
            },
            complete: function() {
                $('#btnEditSubmit').prop('disabled', false);
            }
        });
    });

    // Click Show button
    $('#products-table').on('click', '.btn-show', function(){
        var id = $(this).data('id');
        $.ajax({
            url: '/products/' + id,
            method: 'GET',
            success: function(res){
                if (res.product) {
                    $('#show_name').text(res.product.name || '-');
                    $('#show_category').text(res.product.category ? res.product.category.name : '-');
                    $('#show_unit').text(res.product.unit || '-');
                    $('#show_description').text(res.product.description || '-');
                    $('#show_created_at').text(res.product.created_at_formatted || '-');
                    modalShow.show();
                } else {
                    showAlert('danger', 'Could not fetch product details.');
                }
            },
            error: function() {
                showAlert('danger', 'Could not fetch product details.');
            }
        });
    });

    // Delete (with confirm)
    $('#products-table').on('click', '.btn-delete', function(){
        var id = $(this).data('id');
        if (!confirm('Delete this product?')) return;

        $.ajax({
            url: '/products/' + id,
            method: 'POST',
            data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
            success: function(res) {
                showAlert('success', res.message || 'Product deleted.');
                table.ajax.reload(null, false);
            },
            error: function() {
                showAlert('danger', 'Delete failed.');
            }
        });
    });

});
</script>
@endsection
