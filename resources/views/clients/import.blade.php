@extends('layouts.app')

@section('content')

<section class="section">
    <div class="section-header">
        <h3 class="page__heading">Import</h3>
    </div>
    @if(session()->has('success'))
    <div class="alert alert-success alert-dismissible show fade">
        <div class="alert-body">
            <button class="close" data-dismiss="alert">
                <span>×</span>
            </button>
            {{ session()->get('success') }}
        </div>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible show fade">
        <div class="alert-body">
            <button class="close" data-dismiss="alert">
                <span>×</span>
            </button>
            <ul style="margin-bottom: 0;margin-left: -20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
    <div class="section-body">
        <div class="row">
            <div class="col-lg-12">        
                    <div class="card">
                        <form method="POST" action="{{ route('clients.import_store') }}"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">
                                <div class="form-group">
                                    <label>File</label>
                                    <input type="file" name="file" class="form-control">
                                </div>
                            </div>
                            <div class="card-footer text-right">
                                <button class="btn btn-primary mr-1" type="submit">Import</button>
                                <button class="btn btn-secondary" type="reset">Cancel</button>
                            </div>
                        </form>
                    </div>              
            </div>
        </div>
    </div>
</section>
@endsection
