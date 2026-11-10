
@extends('layouts.app')

@section('title', 'Kategori Cepat')

@section('content')
<div class="max-w-3xl mx-auto p-6" x-data="quickCategories()">
    <h1 class="text-2xl font-bold mb-6">Kelola Kategori Cepat</h1>

    <form @submit.prevent="addCategory" class="mb-6 flex gap-3">
        <input
            type="text"
            x-model="name"
            placeholder="Masukkan nama kategori"
            class="flex-1 border rounded-lg px-4 py-2"
            required
        >

        <button
            type="submit"
            :disabled="loading"
            class="bg-blue-600 text-white px-5 py-2 rounded-lg disabled:opacity-50"
        >
            <span x-text="loading ? 'Menyimpan...' : 'Tambah'"></span>
        </button>
    </form>

    <template x-if="message">
        <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-700" x-text="message"></div>
    </template>

    <template x-if="error">
        <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-700" x-text="error"></div>
    </template>

    <h2 class="text-lg font-semibold mb-3">Daftar Kategori</h2>

    <div class="border rounded-lg divide-y">
        <template x-for="category in categories" :key="category.id">
            <div class="p-4" x-text="category.name"></div>
        </template>

        <template x-if="categories.length === 0">
            <div class="p-4 text-gray-500">
                Belum ada kategori.
            </div>
        </template>
    </div>
</div>

<script>
function quickCategories() {
    return {
        name: '',
        categories: @json($categories),
        loading: false,
        message: '',
        error: '',

        async addCategory() {
            this.message = '';
            this.error = '';

            const categoryName = this.name.trim();

            if (!categoryName) {
                this.error = 'Nama kategori wajib diisi.';
                return;
            }

            this.loading = true;

            try {
                const response = await fetch(
                    @json(route('quick-categories.store')),
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document
                                .querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                        },
                        body: JSON.stringify({
                            name: categoryName
                        })
                    }
                );

                const result = await response.json();

                if (!response.ok) {
                    if (response.status === 422) {
                        this.error = Object.values(result.errors || {})
                            .flat()
                            .join(' ');
                    } else {
                        this.error = result.message || 'Gagal menambahkan kategori.';
                    }

                    return;
                }

                this.categories.unshift(result.data);
                this.name = '';
                this.message = result.message;
            } catch (error) {
                this.error = 'Terjadi kesalahan koneksi.';
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
@endsection
