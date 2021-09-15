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
                            <h3 class="text-center">Credit Cards Content</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
