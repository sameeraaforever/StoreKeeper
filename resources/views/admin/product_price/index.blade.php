@extends('layouts.app')

@section('header_css')
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('main_content')
<div class="container-fluid">
    <div class="alerts my-2"></div>

    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Product Prices</h5>
            <div>
                <button class="btn btn-success" id="btnOpenCreate">Add Price</button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped table-bordered" id="prices-table" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Start Date</th>
                        <th>End Date</th>
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
  <div class="modal-dialog">
    <form id="formCreate" autocomplete="off">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalCreateLabel">Add Product Price</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="create-errors" class="alert alert-danger d-none"></div>

            <div class="mb-3">
                <label for="create_product_id" class="form-label">Product</label>
                <select id="create_product_id" name="product_id" class="form-select" required>
                    <option value="">-- select product --</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="create_price" class="form-label">Price</label>
                <input type="number" step="0.0001" class="form-control" id="create_price" name="price" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="create_start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="create_start_date" name="start_date" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="create_end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="create_end_date" name="end_date" placeholder="Optional">
                </div>
            </div>

            <p class="small text-muted">Note: Adding a new price auto-sets the previous active price's end date to <code>Start Date - 1</code>.</p>

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
  <div class="modal-dialog">
    <form id="formEdit" autocomplete="off">
      @csrf
      @method('PUT')
      <input type="hidden" id="edit_id" name="id">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalEditLabel">Edit Product Price</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="edit-errors" class="alert alert-danger d-none"></div>

            <div class="mb-3">
                <label for="edit_product_id" class="form-label">Product</label>
                <select id="edit_product_id" name="product_id" class="form-select" required>
                    <option value="">-- select product --</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="edit_price" class="form-label">Price</label>
                <input type="number" step="0.0001" class="form-control" id="edit_price" name="price" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="edit_start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="edit_end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="edit_end_date" name="end_date" placeholder="Optional">
                </div>
            </div>

            <p class="small text-muted">Note: Editing dates may auto-adjust neighboring records to avoid overlaps.</p>

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
  <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalShowLabel">Price Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <table class="table table-sm table-borderless">
                <tbody>
                    <tr><th>Product</th><td id="show_product"></td></tr>
                    <tr><th>Price</th><td id="show_price"></td></tr>
                    <tr><th>Start Date</th><td id="show_start_date"></td></tr>
                    <tr><th>End Date</th><td id="show_end_date"></td></tr>
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
        setTimeout(function(){ $('.alerts .alert').alert('close'); }, 7000);
    }

    var table = $('#prices-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("product-prices.index") }}',
            dataSrc: 'data'
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'product_name', name: 'product_name' },
            { data: 'price', name: 'price' },
            { data: 'start_date', name: 'start_date' },
            { data: 'end_date', name: 'end_date' },
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
            url: '{{ route("product-prices.store") }}',
            method: 'POST',
            data: formData,
            success: function(res) {
                showAlert('success', res.message || 'Price created.');
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
    $('#prices-table').on('click', '.btn-edit', function(){
        var id = $(this).data('id');
        $('#edit-errors').addClass('d-none').html('');
        $.ajax({
            url: '/product-prices/' + id + '/edit',
            method: 'GET',
            success: function(res) {
                if (res.price) {
                    $('#edit_id').val(res.price.id);
                    $('#edit_product_id').val(res.price.product_id);
                    $('#edit_price').val(res.price.price);
                    $('#edit_start_date').val(res.price.start_date);
                    $('#edit_end_date').val(res.price.end_date);
                    modalEdit.show();
                } else {
                    showAlert('danger','Could not load price data.');
                }
            },
            error: function() {
                showAlert('danger','Could not load price data.');
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
            url: '/product-prices/' + id,
            method: 'POST',
            data: formData,
            success: function(res) {
                showAlert('success', res.message || 'Price updated.');
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
    $('#prices-table').on('click', '.btn-show', function(){
        var id = $(this).data('id');
        $.ajax({
            url: '/product-prices/' + id,
            method: 'GET',
            success: function(res){
                if (res.price) {
                    $('#show_product').text(res.price.product ? res.price.product.name : '-');
                    $('#show_price').text(res.price.price || '-');
                    $('#show_start_date').text(res.price.start_date || '-');
                    $('#show_end_date').text(res.price.end_date || '-');
                    $('#show_created_at').text(res.price.created_at_formatted || '-');
                    modalShow.show();
                } else {
                    showAlert('danger', 'Could not fetch price details.');
                }
            },
            error: function() {
                showAlert('danger', 'Could not fetch price details.');
            }
        });
    });

    // Delete (with confirm)
    $('#prices-table').on('click', '.btn-delete', function(){
        var id = $(this).data('id');
        if (!confirm('Delete this price?')) return;

        $.ajax({
            url: '/product-prices/' + id,
            method: 'POST',
            data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
            success: function(res) {
                showAlert('success', res.message || 'Price deleted.');
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
