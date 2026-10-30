# Pengukuran Memori: get() vs lazyById()

Dataset: sekitar 52.500 row pada tabel `transactions`.

| Cara baca data | Peak memory (MB) |
|---|---|
| `Transaction::lazyById()` | 70,1 |
| `Transaction::get()` | 128,4 |

Kesimpulan kelompok: Transaction::get() memuat seluruh 52.500 row ke memori sekaligus, sehingga peak memory-nya mencapai 128,4 MB. Transaction::lazyById() mengambil data per potongan kecil dan membuang potongan sebelumnya, sehingga peak memory-nya hanya 70,1 MB, sekitar 45% lebih hemat. Semakin besar datanya, semakin lebar selisihnya, dan get() berisiko melewati memory_limit PHP. Karena itu, untuk export data besar kita memakai lazyById() (atau FromQuery di Laravel Excel yang membaca per chunk).