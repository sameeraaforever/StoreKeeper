@extends('layouts.app')

@section('header_css')
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('main_content')
<div class="container-fluid">
    <div class="alerts my-2"></div>

    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Locations</h5>
            <div>
                <button class="btn btn-success" id="btnOpenCreate">Add Location</button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped table-bordered" id="locations-table" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Company</th>
                        <th>Address</th>
                        <th>City</th>
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
          <h5 class="modal-title" id="modalCreateLabel">Add Location</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="create-errors" class="alert alert-danger d-none"></div>

            <div class="mb-3">
                <label for="create_company_id" class="form-label">Company</label>
                <select id="create_company_id" name="company_id" class="form-select" required>
                    <option value="">-- choose company --</option>
                    @foreach($companies as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="create_address_line" class="form-label">Address</label>
                <input type="text" class="form-control" id="create_address_line" name="address_line">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="create_city" class="form-label">City</label>
                    <input type="text" class="form-control" id="create_city" name="city">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="create_state" class="form-label">State</label>
                    <input type="text" class="form-control" id="create_state" name="state">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="create_zip_code" class="form-label">Zip Code</label>
                    <input type="text" class="form-control" id="create_zip_code" name="zip_code">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="create_country" class="form-label">Country</label>
                    <input type="text" class="form-control" id="create_country" name="country" value="Sri Lanka">
                </div>
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
  <div class="modal-dialog">
    <form id="formEdit" autocomplete="off">
      @csrf
      @method('PUT')
      <input type="hidden" id="edit_id" name="id">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalEditLabel">Edit Location</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="edit-errors" class="alert alert-danger d-none"></div>

            <div class="mb-3">
                <label for="edit_company_id" class="form-label">Company</label>
                <select id="edit_company_id" name="company_id" class="form-select" required>
                    <option value="">-- choose company --</option>
                    @foreach($companies as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="edit_address_line" class="form-label">Address</label>
                <input type="text" class="form-control" id="edit_address_line" name="address_line">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="edit_city" class="form-label">City</label>
                    <input type="text" class="form-control" id="edit_city" name="city">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="edit_state" class="form-label">State</label>
                    <input type="text" class="form-control" id="edit_state" name="state">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="edit_zip_code" class="form-label">Zip Code</label>
                    <input type="text" class="form-control" id="edit_zip_code" name="zip_code">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="edit_country" class="form-label">Country</label>
                    <input type="text" class="form-control" id="edit_country" name="country">
                </div>
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
  <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalShowLabel">Location Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <table class="table table-sm table-borderless">
                <tbody>
                    <tr><th>Company</th><td id="show_company"></td></tr>
                    <tr><th>Address</th><td id="show_address_line"></td></tr>
                    <tr><th>City</th><td id="show_city"></td></tr>
                    <tr><th>State</th><td id="show_state"></td></tr>
                    <tr><th>Zip</th><td id="show_zip_code"></td></tr>
                    <tr><th>Country</th><td id="show_country"></td></tr>
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

    var table = $('#locations-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("locations.index") }}',
            dataSrc: 'data'
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'company_name', name: 'company_name' },
            { data: 'address_line', name: 'address_line' },
            { data: 'city', name: 'city' },
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
            url: '{{ route("locations.store") }}',
            method: 'POST',
            data: formData,
            success: function(res) {
                showAlert('success', res.message || 'Location created.');
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
    $('#locations-table').on('click', '.btn-edit', function(){
        var id = $(this).data('id');
        $('#edit-errors').addClass('d-none').html('');
        $.ajax({
            url: '/locations/' + id + '/edit',
            method: 'GET',
            success: function(res) {
                if (res.location) {
                    $('#edit_id').val(res.location.id);
                    $('#edit_company_id').val(res.location.company_id);
                    $('#edit_address_line').val(res.location.address_line);
                    $('#edit_city').val(res.location.city);
                    $('#edit_state').val(res.location.state);
                    $('#edit_zip_code').val(res.location.zip_code);
                    $('#edit_country').val(res.location.country);
                    modalEdit.show();
                } else {
                    showAlert('danger','Could not load location data.');
                }
            },
            error: function() {
                showAlert('danger','Could not load location data.');
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
            url: '/locations/' + id,
            method: 'POST',
            data: formData,
            success: function(res) {
                showAlert('success', res.message || 'Location updated.');
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
    $('#locations-table').on('click', '.btn-show', function(){
        var id = $(this).data('id');
        $.ajax({
            url: '/locations/' + id,
            method: 'GET',
            success: function(res){
                if (res.location) {
                    $('#show_company').text(res.location.company ? res.location.company.name : '-');
                    $('#show_address_line').text(res.location.address_line || '-');
                    $('#show_city').text(res.location.city || '-');
                    $('#show_state').text(res.location.state || '-');
                    $('#show_zip_code').text(res.location.zip_code || '-');
                    $('#show_country').text(res.location.country || '-');
                    $('#show_created_at').text(res.location.created_at_formatted || '-');
                    modalShow.show();
                } else {
                    showAlert('danger', 'Could not fetch location details.');
                }
            },
            error: function() {
                showAlert('danger', 'Could not fetch location details.');
            }
        });
    });

    // Delete (with confirm)
    $('#locations-table').on('click', '.btn-delete', function(){
        var id = $(this).data('id');
        if (!confirm('Delete this location?')) return;

        $.ajax({
            url: '/locations/' + id,
            method: 'POST',
            data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
            success: function(res) {
                showAlert('success', res.message || 'Location deleted.');
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
