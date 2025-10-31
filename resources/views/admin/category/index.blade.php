@extends('layouts.app')


@section('main_content')
<div class="container-fluid">
    <div class="alerts my-2"></div>

    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Category</h5>
            <div>
                <button class="btn btn-success" id="btnOpenCreate">Add Category</button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped table-bordered" id="categories-table" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category</th>
                        <th>Created At</th>
                        <th style="width:170px">Actions</th>
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
          <h5 class="modal-title" id="modalCreateLabel">Add Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="create-errors" class="alert alert-danger d-none"></div>

            <div class="mb-3">
                <label for="create_name" class="form-label">Name</label>
                <input type="text" class="form-control" id="create_name" name="name" required>
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
          <h5 class="modal-title" id="modalEditLabel">Edit Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="edit-errors" class="alert alert-danger d-none"></div>

            <div class="mb-3">
                <label for="edit_name" class="form-label">Name</label>
                <input type="text" class="form-control" id="edit_name" name="name" required>
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

<!-- Show Modal (read-only) -->
<div class="modal fade" id="modalShow" tabindex="-1" aria-labelledby="modalShowLabel" aria-hidden="true">
  <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalShowLabel">Category Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <table class="table table-sm table-borderless">
                <tbody>
                    <tr><th>Name</th><td id="show_name"></td></tr>
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



@section('footer_js')
<script>
$(function() {
    // bootstrap modal instances
    var modalCreate = new bootstrap.Modal(document.getElementById('modalCreate'));
    var modalEdit = new bootstrap.Modal(document.getElementById('modalEdit'));
    var modalShow = new bootstrap.Modal(document.getElementById('modalShow'));

    // helper to show bootstrap alert in .alerts container
    function showAlert(type, message) {
        var html = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
            message +
            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
            '</div>';
        $('.alerts').html(html);
        // auto-dismiss after 5s
        setTimeout(function(){ $('.alerts .alert').alert('close'); }, 5000);
    }

    // Initialize DataTable
    var table = $('#categories-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("categories.index") }}',
            dataSrc: 'data'
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
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
            url: '{{ route("categories.store") }}',
            method: 'POST',
            data: formData,
            success: function(res) {
                showAlert('success', res.message || 'Category created.');
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
    $('#categories-table').on('click', '.btn-edit', function(){
        var id = $(this).data('id');
        $('#edit-errors').addClass('d-none').html('');
        $.ajax({
            url: '/categories/' + id + '/edit',
            method: 'GET',
            success: function(res) {
                if (res.category) {
                    $('#edit_id').val(res.category.id);
                    $('#edit_name').val(res.category.name);
                    modalEdit.show();
                } else {
                    showAlert('danger','Could not load category data.');
                }
            },
            error: function() {
                showAlert('danger','Could not load category data.');
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
            url: '/categories/' + id,
            method: 'POST',
            data: formData,
            success: function(res) {
                showAlert('success', res.message || 'Category updated.');
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
    $('#categories-table').on('click', '.btn-show', function(){
        var id = $(this).data('id');
        $.ajax({
            url: '/categories/' + id,
            method: 'GET',
            success: function(res){
                if (res.category) {
                    $('#show_name').text(res.category.name || '-');
                    $('#show_created_at').text(res.category.created_at || '-');
                    modalShow.show();
                } else {
                    showAlert('danger', 'Could not fetch category details.');
                }
            },
            error: function() {
                showAlert('danger', 'Could not fetch category details.');
            }
        });
    });

    // Delete (with confirm)
    $('#categories-table').on('click', '.btn-delete', function(){
        var id = $(this).data('id');
        if (!confirm('Delete this category?')) return;

        $.ajax({
            url: '/categories/' + id,
            method: 'POST',
            data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
            success: function(res) {
                showAlert('success', res.message || 'Category deleted.');
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
