@extends('bounds')

@section('content')
    <h1 class="text-center">Product list</h1>

    <div class="row" id="products">
        @foreach($grouped_products as $group => $products)
            <div class="col-6">
                <h2 class="text-primary text-center">Payment {{ $group }}</h2>

                <div class="list">
                    @foreach($products as $product)
                        <div class="border border-dark p-4 m-4">
                            <h3>{{ $product->title }}</h3>
                            <p>{{ $product->description  }}</p>
                            <div>
                                <span>${{ $product->price  }}</span> / <b>{{ $product->payment->name }}</b>
                            </div>
                            <div class="d-flex justify-content-center">
                                <a class="btn btn-primary" href="{{ route('http.buy', $product->id) }}" target="_blank">Buy!</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@endsection
