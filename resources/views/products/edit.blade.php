@extends('layouts.app')

@section('content')
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Edit Produk</h1>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('products.update', $product->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="name" class="block mb-2">Nama Produk</label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    value="{{ old('name', $product->name) }}"
                    class="w-full border rounded px-3 py-2"
                >
            </div>

            <div class="mb-4">
                <label for="category_id" class="block mb-2">Kategori</label>
                <select
                    name="category_id"
                    id="category_id"
                    class="w-full border rounded px-3 py-2"
                >
                    @foreach ($categories as $category)
                        <option
                            value="{{ $category->id }}"
                            @selected(old('category_id', $product->category_id) == $category->id)
                        >
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label for="price" class="block mb-2">Harga</label>
                <input
                    type="number"
                    name="price"
                    id="price"
                    value="{{ old('price', $product->price) }}"
                    min="0"
                    class="w-full border rounded px-3 py-2"
                >
            </div>

            <div class="mb-4">
                <label for="stock" class="block mb-2">Stok</label>
                <input
                    type="number"
                    name="stock"
                    id="stock"
                    value="{{ old('stock', $product->stock) }}"
                    min="0"
                    class="w-full border rounded px-3 py-2"
                >
            </div>

            <div class="flex gap-2">
                <button
                    type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded"
                >
                    Simpan Perubahan
                </button>

                <a
                    href="{{ route('products.index') }}"
                    class="px-4 py-2 border rounded"
                >
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection