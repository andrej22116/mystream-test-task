@extends('bounds')

@section('content')
    <div class="d-flex flex-column justify-content-center align-items-center">
        <div class="container">
            <form method="POST" action="{{ route('http.reg') }}">
                @csrf

                <div class="form-group mt-3">
                    <label for="name">Name:</label>
                    <input type="text" class="form-control" id="name" name="name">
                </div>

                <div class="form-group mt-3">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" id="email" name="email">
                </div>

                <div class="form-group mt-3">
                    <label for="password">Password:</label>
                    <input type="password" class="form-control" id="password" name="password">
                </div>

                <div class="form-group mt-3">
                    <button style="cursor:pointer" type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>

        <div class="container mt-5">
            <a class="btn btn-secondary" href="{{ route('http.home') }}">Go home!</a>
        </div>
    </div>
@endsection
