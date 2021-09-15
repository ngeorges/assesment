@extends('layouts.app')

@section('page_css')
<!-- CSS Libraries -->
<link rel="stylesheet" href="{{ asset('assets/css/datatables/datatables.min.css') }}">
<link rel="stylesheet"
    href="{{ asset('assets/css/datatables/dataTables.bootstrap4.min.css') }}">
@endsection

@section('title', 'Credit Cards')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Credit Cards</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped dataTable" id="table-clients">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Account</th>
                                        <th>Type</th>
                                        <th>Number</th>
                                        <th>Expiration Date</th>                        
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('page_js')
<!-- JS Libraies -->
<script src="{{ asset('assets/js/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('assets/js/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/js/datatables/Select-1.2.4/js/dataTables.select.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery-ui/jquery-ui.min.js') }}"></script>
<script type="text/javascript">
    $(function () {
        var table = $('.dataTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('creditcards.list') }}",
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'account',
                    name: 'account'
                },
                {
                    data: 'type',
                    name: 'type'
                },
                {
                    data: 'number',
                    name: 'number'
                },
                {
                    data: 'expirationDate',
                    name: 'expirationDate'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });

    });
</script>
@stop