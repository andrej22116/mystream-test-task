@extends('bounds')

@section('content')
    <div>
        <h2 class="alert alert-info">Canceled! :|</h2>

        <a class="btn btn-primary" href="{{ route('http.home') }}">Go home!</a>
    </div>
@endsection
