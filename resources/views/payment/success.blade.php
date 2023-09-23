@extends('bounds')

@section('content')
    <div>
        <h2 class="alert alert-success">Success! c:</h2>

        <a class="btn btn-primary" href="{{ route('http.home') }}">Go home!</a>
    </div>
@endsection
