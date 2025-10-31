@extends('layouts.app')

@section('header_css')
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Buttons CSS -->
    <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('main_content')
<div class="container-fluid">
    <div class="alerts my-2"></div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Supply Records</h5>
                <div>
                    <button class="btn btn-success" id="btnOpenCreate">Add Supply</button>
                </div>
            </div>

            <div class="row mt-3 g-2">
                <div class="col-md-2">
                    <select id="filter_company" class="form-select">
                        <option value="">All Companies</option>
                        @foreach($companies as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select id="filter_product" class="form-select">
                        <option value="">All Products</option>
                        @foreach($products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select id="filter_month" class="form-select">
                        <option value="">All Months</option>
                        @for($m=1;$m<=12;$m++)
                        <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-2">
                    <select id="filter_year" class="form-select">
                        <option value="">All Years</option>
                        @php
                            $currentYear = date('Y');
                            $startYear = $currentYear - 5;
                        @endphp
                        @for($y=$startYear; $y <= $currentYear; $y++)
                        <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-4 text-end">
                    <button id="btnFilter" class="btn btn-primary">Filter</button>
                    <button id="btnClear" class="btn btn-secondary">Clear</button>
                    <a id="exportExcel" class="btn btn-outline-success">Export</a>
                </div>
            </div>

        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped table-bordered" id="supply-table" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Company</th>
                        <th>Location</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                        <th>Supply Date</th>
                        <th>Added By</th>
                        <th style="width:160px">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form id="formCreate">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Supply</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div id="create-errors" class="alert alert-danger d-none"></div>

            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label">Company</label>
                    <select id="create_company_id" name="company_id" class="form-select" required>
                        <option value="">-- select company --</option>
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Location</label>
                    <select id="create_location_id" name="location_id" class="form-select">
                        <option value="">-- select location --</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Product</label>
                    <select id="create_product_id" name="product_id" class="form-select" required>
                        <option value="">-- select product --</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 mt-2">
                    <label class="form-label">Quantity</label>
                    <input type="number" step="0.001" name="quantity" id="create_quantity" class="form-control" required value="0">
                </div>

                <div class="col-md-3 mt-2">
                    <label class="form-label">Unit Price</label>
                    <input type="number" step="0.0001" name="unit_price" id="create_unit_price" class="form-control" required value="0">
                </div>

                <div class="col-md-3 mt-2">
                    <label class="form-label">Total</label>
                    <input type="text" id="create_total_amount" class="form-control" readonly>
                </div>

                <div class="col-md-3 mt-2">
                    <label class="form-label">Supply Date</label>
                    <input type="date" name="supply_date" id="create_supply_date" class="form-control">
                </div>
            </div>

            <div class="mt-2 small text-muted">
                Unit price auto-filled from price history for selected product and date (can be edited).
            </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button id="btnCreateSubmit" type="submit" class="btn btn-primary">Save</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form id="formEdit">
      @csrf
      @method('PUT')
      <input type="hidden" id="edit_id" name="id">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Supply</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div id="edit-errors" class="alert alert-danger d-none"></div>

            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label">Company</label>
                    <select id="edit_company_id" name="company_id" class="form-select" required>
                        <option value="">-- select company --</option>
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Location</label>
                    <select id="edit_location_id" name="location_id" class="form-select">
                        <option value="">-- select location --</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Product</label>
                    <select id="edit_product_id" name="product_id" class="form-select" required>
                        <option value="">-- select product --</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 mt-2">
                    <label class="form-label">Quantity</label>
                    <input type="number" step="0.001" name="quantity" id="edit_quantity" class="form-control" required>
                </div>

                <div class="col-md-3 mt-2">
                    <label class="form-label">Unit Price</label>
                    <input type="number" step="0.0001" name="unit_price" id="edit_unit_price" class="form-control" required>
                </div>

                <div class="col-md-3 mt-2">
                    <label class="form-label">Total</label>
                    <input type="text" id="edit_total_amount" class="form-control" readonly>
                </div>

                <div class="col-md-3 mt-2">
                    <label class="form-label">Supply Date</label>
                    <input type="date" name="supply_date" id="edit_supply_date" class="form-control">
                </div>
            </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button id="btnEditSubmit" type="submit" class="btn btn-primary">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Show Modal -->
<div class="modal fade" id="modalShow" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Supply Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <table class="table table-sm table-borderless">
                <tbody>
                    <tr><th>Company</th><td id="show_company"></td></tr>
                    <tr><th>Location</th><td id="show_location"></td></tr>
                    <tr><th>Product</th><td id="show_product"></td></tr>
                    <tr><th>Quantity</th><td id="show_quantity"></td></tr>
                    <tr><th>Unit Price</th><td id="show_unit_price"></td></tr>
                    <tr><th>Total</th><td id="show_total"></td></tr>
                    <tr><th>Supply Date</th><td id="show_supply_date"></td></tr>
                    <tr><th>Added By</th><td id="show_added_by"></td></tr>
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
    <!-- Buttons -->
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
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

    var table = $('#supply-table').DataTable({
        processing: true,
        serverSide: true,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                title: 'supply-records-' + new Date().toISOString().slice(0,10),
                exportOptions: { columns: [0,1,2,3,4,5,6,7,8] }
            }
        ],
        ajax: {
            url: '{{ route("supply-records.index") }}',
            data: function(d) {
                d.company_id = $('#filter_company').val();
                d.product_id = $('#filter_product').val();
                d.month = $('#filter_month').val();
                d.year = $('#filter_year').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'company_name', name: 'company_name' },
            { data: 'location_name', name: 'location_name' },
            { data: 'product_name', name: 'product_name' },
            { data: 'quantity', name: 'quantity' },
            { data: 'unit_price', name: 'unit_price' },
            { data: 'total_amount_format', name: 'total_amount' },
            { data: 'supply_date', name: 'supply_date' },
            { data: 'added_by', name: 'added_by' },
            { data: 'actions', name: 'actions', orderable:false, searchable:false }
        ],
        order: [[0,'desc']],
        responsive: true
    });

    // Filter buttons
    $('#btnFilter').on('click', function(){ table.ajax.reload(); });
    $('#btnClear').on('click', function(){
        $('#filter_company').val('');
        $('#filter_product').val('');
        $('#filter_month').val('');
        $('#filter_year').val('');
        table.ajax.reload();
    });

    // Export button (bind)
    $('#exportExcel').on('click', function(){
        table.button('.buttons-excel').trigger();
    });

    // Open create modal
    $('#btnOpenCreate').on('click', function(){
        $('#formCreate')[0].reset();
        $('#create-errors').addClass('d-none').html('');
        $('#create_location_id').html('<option value="">-- select location --</option>');
        $('#create_total_amount').val('');
        modalCreate.show();
    });

    // when company changes -> load locations
    $('#create_company_id').on('change', function(){
        var companyId = $(this).val();
        $('#create_location_id').html('<option value="">-- select location --</option>');
        if (!companyId) return;
        $.get('/supply-records/locations/' + companyId, function(res){
            if (res.locations) {
                res.locations.forEach(function(loc){
                    $('#create_location_id').append('<option value="' + loc.id + '">' + (loc.address_line || loc.city || loc.id) + '</option>');
                });
            }
        });
    });

    // when product or date changes -> fetch price
    function fetchCreatePrice() {
        var pid = $('#create_product_id').val();
        var date = $('#create_supply_date').val();
        if (!pid) return;

        $.get('/product-prices/by-product/' + pid, { date: date }, function(res){
            if (res.price !== null) {
                $('#create_unit_price').val(res.price);
            }
            recalcCreateTotal();
        });
    }


    $('#create_product_id, #create_supply_date').on('change', fetchCreatePrice);

    // live calc totals
    function recalcCreateTotal() {
        var q = parseFloat($('#create_quantity').val() || 0);
        var p = parseFloat($('#create_unit_price').val() || 0);
        $('#create_total_amount').val((q * p).toFixed(4));
    }
    $('#create_quantity, #create_unit_price').on('input', recalcCreateTotal);

    // Submit Create
    $('#formCreate').on('submit', function(e){
        e.preventDefault();
        $('#btnCreateSubmit').prop('disabled', true);
        $('#create-errors').addClass('d-none').html('');
        var formData = $(this).serialize();
        $.post('{{ route("supply-records.store") }}', formData)
            .done(function(res){
                showAlert('success', res.message || 'Supply record created.');
                modalCreate.hide();
                table.ajax.reload(null, false);
            })
            .fail(function(xhr){
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    var html = '<ul class="mb-0">';
                    $.each(errors, function(k,v){ html += '<li>' + v[0] + '</li>'; });
                    html += '</ul>';
                    $('#create-errors').removeClass('d-none').html(html);
                } else {
                    showAlert('danger', 'Server error.');
                }
            })
            .always(function(){ $('#btnCreateSubmit').prop('disabled', false); });
    });

    // Edit: open and populate
    $('#supply-table').on('click', '.btn-edit', function(){
        var id = $(this).data('id');
        $('#edit-errors').addClass('d-none').html('');
        $.get('/supply-records/' + id + '/edit', function(res){
            if (res.record) {
                var r = res.record;
                $('#edit_id').val(r.id);
                $('#edit_company_id').val(r.company_id);
                // load locations for this company then set value
                $.get('/supply-records/locations/' + r.company_id, function(locRes){
                    $('#edit_location_id').html('<option value="">-- select location --</option>');
                    if (locRes.locations) {
                        locRes.locations.forEach(function(loc){
                            var sel = (loc.id == r.location_id) ? 'selected' : '';
                            $('#edit_location_id').append('<option value="' + loc.id + '" '+sel+'>' + (loc.address_line || loc.city || loc.id) + '</option>');
                        });
                    }
                });
                $('#edit_product_id').val(r.product_id);
                $('#edit_quantity').val(r.quantity);
                $('#edit_unit_price').val(r.unit_price);
                $('#edit_total_amount').val((parseFloat(r.total_amount)||0).toFixed(4));
                $('#edit_supply_date').val(r.supply_date ? r.supply_date.substr(0,10) : '');
                modalEdit.show();
            } else {
                showAlert('danger','Could not load record.');
            }
        }).fail(function(){ showAlert('danger','Could not load record.'); });
    });

    // edit product/date change -> fetch price (but do not overwrite if user already changed unit_price)
    function fetchEditPrice() {
        var pid = $('#edit_product_id').val();
        var date = $('#edit_supply_date').val();
        if (!pid) return;

        $.get('/product-prices/by-product/' + pid, { date: date }, function(res){
            if (res.price !== null) {
                $('#edit_unit_price').val(res.price);
                recalcEditTotal();
            }
        });
    }

    $('#edit_product_id, #edit_supply_date').on('change', fetchEditPrice);

    function recalcEditTotal() {
        var q = parseFloat($('#edit_quantity').val() || 0);
        var p = parseFloat($('#edit_unit_price').val() || 0);
        $('#edit_total_amount').val((q * p).toFixed(4));
    }
    $('#edit_quantity, #edit_unit_price').on('input', recalcEditTotal);

    // Submit Edit
    $('#formEdit').on('submit', function(e){
        e.preventDefault();
        $('#btnEditSubmit').prop('disabled', true);
        $('#edit-errors').addClass('d-none').html('');
        var id = $('#edit_id').val();
        var formData = $(this).serialize();
        $.post('/supply-records/' + id, formData)
            .done(function(res){
                showAlert('success', res.message || 'Supply record updated.');
                modalEdit.hide();
                table.ajax.reload(null, false);
            })
            .fail(function(xhr){
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    var html = '<ul class="mb-0">';
                    $.each(errors, function(k,v){ html += '<li>' + v[0] + '</li>'; });
                    html += '</ul>';
                    $('#edit-errors').removeClass('d-none').html(html);
                } else {
                    showAlert('danger','Server error.');
                }
            })
            .always(function(){ $('#btnEditSubmit').prop('disabled', false); });
    });

    // Show
    $('#supply-table').on('click', '.btn-show', function(){
        var id = $(this).data('id');
        $.get('/supply-records/' + id, function(res){
            if (res.record) {
                var r = res.record;
                $('#show_company').text(r.company ? r.company.name : '-');
                $('#show_location').text(r.location ? (r.location.address_line || r.location.city) : '-');
                $('#show_product').text(r.product ? r.product.name : '-');
                $('#show_quantity').text(r.quantity);
                $('#show_unit_price').text(r.unit_price);
                $('#show_total').text(r.total_amount);
                $('#show_supply_date').text(r.supply_date ? r.supply_date.substr(0,10) : '-');
                $('#show_added_by').text(r.creator ? r.creator.name : '-');
                modalShow.show();
            } else {
                showAlert('danger','Could not fetch details.');
            }
        }).fail(function(){ showAlert('danger','Could not fetch details.'); });
    });

    // Delete
    $('#supply-table').on('click', '.btn-delete', function(){
        var id = $(this).data('id');
        if (!confirm('Delete this supply record?')) return;
        $.post('/supply-records/' + id, { _method: 'DELETE', _token: '{{ csrf_token() }}' })
            .done(function(res){
                showAlert('success', res.message || 'Deleted.');
                table.ajax.reload(null, false);
            })
            .fail(function(){ showAlert('danger','Delete failed.'); });
    });

});
</script>
@endsection
