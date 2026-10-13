# Hasil Tes curl pada POST /pos

## Persiapan

Pertama, saya menjalankan server dengan composer run dev. Setelah itu, saya mengambil token CSRF dari halaman /pos memakai curl, lalu menyimpannya di variabel $token. Token ini perlu karena Laravel menolak request POST yang tidak membawa token (hasilnya error 419).

Perintah yang saya pakai:
curl.exe -c cookies.txt http://127.0.0.1:8000/pos -o halaman.html
$token = (Select-String -Path halaman.html -Pattern 'name="_token" value="([^"]+)"').Matches[0].Groups[1].Value

## Kasus A: qty bernilai 0

Pada kasus ini, saya mengirim product_id 1 (produk yang ada) tetapi qty 0.

Perintah:
curl.exe -i -b cookies.txt -c cookies.txt -H "Accept: application/json" -X POST http://127.0.0.1:8000/pos -d "_token=$token" -d "items[0][product_id]=1" -d "items[0][qty]=0"

Hasil:
- Status: 422 Unprocessable Content
- Isi respons: {"message":"The items.0.qty field must be at least 1.","errors":{"items.0.qty":["The items.0.qty field must be at least 1."]}}

Artinya, product_id-nya benar, tetapi qty ditolak karena aturan qty minimal 1.

## Kasus B: product_id tidak ada

Pada kasus ini, saya mengirim product_id 99999 (produk yang tidak ada di database) dengan qty 1 yang benar.

Perintah:
curl.exe -i -b cookies.txt -c cookies.txt -H "Accept: application/json" -X POST http://127.0.0.1:8000/pos -d "_token=$token" -d "items[0][product_id]=99999" -d "items[0][qty]=1"

Hasil:
- Status: 422 Unprocessable Content
- Isi respons: {"message":"The selected items.0.product_id is invalid.","errors":{"items.0.product_id":["The selected items.0.product_id is invalid."]}}

Artinya, qty-nya benar, tetapi product_id ditolak karena aturan exists:products,id tidak menemukan produk dengan id 99999.

## Kasus C (tambahan): dua kesalahan sekaligus

Pada kasus ini, saya sengaja mengirim product_id 99999 dan qty 0 secara bersamaan.

Perintah:
curl.exe -i -b cookies.txt -c cookies.txt -H "Accept: application/json" -X POST http://127.0.0.1:8000/pos -d "_token=$token" -d "items[0][product_id]=99999" -d "items[0][qty]=0"

Hasil:
- Status: 422 Unprocessable Content
- Isi respons: {"message":"The selected items.0.product_id is invalid. (and 1 more error)","errors":{"items.0.product_id":["The selected items.0.product_id is invalid."],"items.0.qty":["The items.0.qty field must be at least 1."]}}

Artinya, Laravel melaporkan kedua kesalahan sekaligus. Pesan di bagian atas hanya menulis error pertama, tetapi bagian errors berisi dua entri.