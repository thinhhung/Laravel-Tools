@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Tools</div>

                <div class="card-body">
                    <a href="{{ route('export-data-doc.index') }}" class="btn btn-primary btn-block">Export Database Document</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
