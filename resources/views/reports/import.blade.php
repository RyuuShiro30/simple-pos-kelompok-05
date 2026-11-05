@extends('layouts.app')

@section('title', 'Import Produk')

@section('content')
    <h1 class="text-lg font-semibold mb-4">Import Produk dari CSV</h1>

    @if (session('success'))
        <div class="bg-green-50 text-green-700 p-3 rounded-md mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Import Manual --}}
    <div class="mb-8">
        <h2 class="text-lg font-semibold mb-4">Import Manual</h2>

        <form method="POST" action="{{ route('import.store') }}" enctype="multipart/form-data" class="max-w-md">
            @csrf

            <label class="block mb-3">
                <span class="text-sm font-medium">File CSV</span>
                <input
                    type="file"
                    name="csv"
                    accept=".csv"
                    class="mt-1 block w-full text-sm"
                >

                @error('csv')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </label>

            <p class="text-xs text-slate-500 mb-3">
                Column yang dibutuhkan:
                <code>name</code>,
                <code>category_id</code>,
                <code>price</code>.
            </p>

            <button
                type="submit"
                class="bg-slate-900 text-white px-4 py-2 rounded-md text-sm"
            >
                Import
            </button>
        </form>
    </div>

    {{-- Import Laravel Excel --}}
    <div class="border-t pt-6">
        <h2 class="text-lg font-semibold mb-4">
            Import dengan Laravel Excel
        </h2>

        <form
            method="POST"
            action="{{ route('import.excel') }}"
            enctype="multipart/form-data"
            class="max-w-md"
        >
            @csrf

            <label class="block mb-3">
                <span class="text-sm font-medium">File CSV</span>

                <input
                    type="file"
                    name="csv"
                    accept=".csv"
                    class="mt-1 block w-full text-sm"
                >

                @error('csv')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </label>

            <p class="text-xs text-slate-500 mb-3">
                Menggunakan Laravel Excel dengan
                <code>ToModel</code>,
                <code>WithHeadingRow</code>, dan
                <code>WithChunkReading</code>.
            </p>

            <button
                type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm"
            >
                Import dengan Laravel Excel
            </button>
        </form>
    </div>

    {{-- Riwayat Error Import --}}
    <div class="border-t pt-6 mt-8">
        <h2 class="text-lg font-semibold mb-4">
            Riwayat Baris Import yang Gagal
        </h2>

        @if ($importErrors->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-slate-100 text-left">
                            <th class="border p-3">No.</th>
                            <th class="border p-3">Nama File</th>
                            <th class="border p-3">Baris CSV</th>
                            <th class="border p-3">Pesan Error</th>
                            <th class="border p-3">Waktu</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($importErrors as $index => $error)
                            <tr>
                                <td class="border p-3">
                                    {{ $index + 1 }}
                                </td>

                                <td class="border p-3">
                                    {{ $error->file_name }}
                                </td>

                                <td class="border p-3">
                                    {{ $error->row_number }}
                                </td>

                                <td class="border p-3 text-red-600">
                                    {{ $error->error_message }}
                                </td>

                                <td class="border p-3">
                                    {{ $error->created_at->format('d-m-Y H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-slate-500">
                Belum ada data error import.
            </p>
        @endif
    </div>
@endsection
