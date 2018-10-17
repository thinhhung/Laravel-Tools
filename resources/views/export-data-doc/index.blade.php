@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Export Database Document</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('export-data-doc.store') }}">
                        @csrf
                        <div class="form-group">
                            <label for="host">DB Host</label>
                            <input type="text" class="form-control" id="host" name="host" value={{ old('host', '127.0.0.1') }}>
                        </div>
                        <div class="form-group">
                            <label for="port">DB Port</label>
                            <input type="text" class="form-control" id="port" name="port" value={{ old('port', '3306') }}>
                        </div>
                        <div class="form-group">
                            <label for="database">Database</label>
                            <input type="text" class="form-control" id="database" name="database" value={{ old('database') }}>
                        </div>
                        <div class="form-group">
                            <label for="username">DB Username</label>
                            <input type="text" class="form-control" id="username" name="username" value={{ old('username') }}>
                        </div>
                        <div class="form-group">
                            <label for="password">DB Password</label>
                            <input type="password" class="form-control" id="password" name="password" value={{ old('password') }}>
                        </div>
                        <div class="text-right">
                            <button class="btn btn-primary">Export</button>
                            <a href="{{ url('/') }}" class="btn btn-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
