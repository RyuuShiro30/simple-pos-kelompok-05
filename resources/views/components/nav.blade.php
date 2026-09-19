<nav class="bg-slate-900 text-white px-4 py-3 flex items-center">
    <span class="font-semibold">Simple POS</span>

    <div class="flex gap-4 ml-6">
        <a href="{{ route('pos.create') }}"
           class="hover:underline {{ request()->routeIs('pos.create') ? 'bg-slate-700 px-2 py-1 rounded' : '' }}">
            Kasir
        </a>

        <a href="{{ route('transactions.index') }}"
           class="hover:underline {{ request()->routeIs('transactions.index') ? 'bg-slate-700 px-2 py-1 rounded' : '' }}">
            Transaksi
        </a>
    </div>
</nav>