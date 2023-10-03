@extends('bounds')

@push('scripts')
    <script>
        const stripClientPublicKey = '{{ env('STRIPE_API_PUBLIC_KEY') }}';
        const stripItemId = {{ $product->id }};
    </script>

    @vite('resources/js/payment.js')
@endpush

@section('content')
    <h1 class="text-center">Payment</h1>

    <h2 class="text-center">Details</h2>

    <div class="container">
        <div class="row">
            <div class="col-sm">
                <h3>Card for successful payment</h3>
                <p>4242 4242 4242 4242</p>
            </div>

            <div class="col-sm">
                <h3>Card with 3D secure</h3>
                <p>4000 0000 0000 3220</p>
            </div>

            <div class="col-sm">
                <h3>Card for failed payment</h3>
                <p>4000 0000 0000 9995</p>
            </div>
        </div>

        <div class="row">
            <div class="col-sm border border-dark p-4 m-4">
                <h3>{{ $product->title }}</h3>
                <p>{{ $product->description  }}</p>
                <div>
                    <span>${{ $product->price  }}</span> / <b>{{ $product->payment->name }}</b>
                </div>
            </div>

            <div class="col-sm m-4">
                <form id="payment-form">
                    @csrf

                    <div id="link-authentication-element"></div>

                    <div id="link-element"></div>

                    <div id="payment-element"></div>

                    <div class="d-flex justify-content-center">
                        <button id="submit" class="btn btn-success mt-4">
                            <div class="spinner hidden" id="spinner"></div>

                            <span id="button-text">
                                {{ $product->payment === \App\Models\Product\PaymentType::Once ? 'Pay Now' : 'Subscribe' }}
                            </span>
                        </button>
                    </div>

                    <div id="payment-message" class="hidden"></div>
                </form>
            </div>
        </div>
    </div>

    <div class="mt-5">
        <a class="btn btn-primary" href="{{ route('http.home') }}">Go Home</a>
    </div>

@endsection
