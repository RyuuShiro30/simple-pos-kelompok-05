@extends('layouts.app')

@section('title', 'Kasir')

@section('content')
<div class="container my-4">
    <h1 class="mb-4">Transaksi Kasir</h1>

    {{-- Pesan Sukses --}}
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Pesan Error Items --}}
    @error('items')
        <div class="alert alert-danger">
            {{ $message }}
        </div>
    @error

    {{-- Form Transaksi --}}
    <form action="" method="POST">
        @csrf

        {{-- Daftar Produk --}}
        <div class="row mb-4">
            @foreach ($products as $product)
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">{{ $product->name }}</h5>
                            <p class="card-text">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Ringkasan & Pembayaran --}}
        <div class="card p-3">
            <h4 class="mb-3">Subtotal: Rp <span id="subtotal">0</span></h4>
            <button type="submit" class="btn btn-primary">Bayar</button>
        </div>
    </form>
</div>
@endsection