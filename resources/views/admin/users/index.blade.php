@extends('layouts.app')

@section('header_css')
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('main_content')
<div class="container-fluid">
    <div class="alerts my-2"></div>

    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Users</h5>
            <button class="btn btn-primary btn-add">Add User</button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table id="usersTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Profile</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title"></h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form id="userForm" enctype="multipart/form-data">
              @csrf
              <div class="modal-body">
                  <input type="hidden" id="user_id">
                  <div class="mb-3">
                      <label>Name</label>
                      <input type="text" name="name" class="form-control" required>
                  </div>
                  <div class="mb-3">
                      <label>Email</label>
                      <input type="email" name="email" class="form-control" required>
                  </div>
                  <div class="mb-3">
                      <label>Password</label>
                      <input type="password" name="password" class="form-control">
                  </div>
                  <div class="mb-3">
                      <label>Confirm Password</label>
                      <input type="password" name="password_confirmation" class="form-control">
                  </div>
                  <div class="mb-3">
                      <label>Role</label>
                      <select name="role" class="form-select" required>
                          <option value="user">User</option>
                          <option value="admin">Admin</option>
                      </select>
                  </div>
                  <div class="mb-3">
                      <label>Profile Picture</label>
                      <input type="file" name="profile_picture" class="form-control">
                      <div class="mt-2" id="preview"></div>
                  </div>
              </div>
              <div class="modal-footer">
                  <button type="submit" class="btn btn-primary btn-save">Save</button>
              </div>
          </form>
      </div>
  </div>
</div>
@endsection

@section('footer_js')

<script>

$(document).ready(function () {

    let table = $('#usersTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: "{{ route('admin.users.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'profile', name: 'profile', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'role', name: 'role' },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ]
    });

    // Add User
    $('.btn-add').click(function () {
        $('#userForm')[0].reset();
        $('#user_id').val('');
        $('#userModal .modal-title').text('Add User');
        $('#userModal').modal('show');
    });

    // Edit User
    $('#usersTable').on('click', '.btn-edit', function () {
        let id = $(this).data('id');
        let url = "{{ url('admin/users') }}/" + id + "/edit";

        $.get(url, function (data) {
            $('#user_id').val(data.id);
            $('[name=name]').val(data.name);
            $('[name=email]').val(data.email);
            $('[name=role]').val(data.role);
            $('#preview').html(data.profile_picture ? `<img src="/storage/${data.profile_picture}" width="80">` : '');
            $('#userModal .modal-title').text('Edit User');
            $('#userModal').modal('show');
        });
    });

    $('#userForm').on('submit', function (e) {
        e.preventDefault();

        let id = $('#user_id').val();
        let formData = new FormData(this);
        let url = id ? "{{ url('admin/users') }}/" + id : "{{ route('admin.users.store') }}";

        $.ajax({
            url: url,
            type: 'POST', // always POST (Laravel detects PUT via _method)
            data: formData,
            contentType: false,
            processData: false,
            success: function () {
                $('#userModal').modal('hide');
                $('#usersTable').DataTable().ajax.reload();
            }
        });
    });


    $('#usersTable').on('click', '.btn-delete', function () {
        if (!confirm('Delete this user?')) return;

        let id = $(this).data('id');
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: "{{ url('admin/users') }}/" + id,
            type: 'DELETE', // Laravel expects DELETE for resource route
            success: function () {
                $('#usersTable').DataTable().ajax.reload();
            },
            error: function (xhr) {
                alert('Error deleting user: ' + xhr.responseText);
            }
        });
    });


});


</script>
@endsection
