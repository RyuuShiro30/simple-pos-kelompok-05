<?php

namespace App\Jobs;

use App\Models\ImportError;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProcessProductImport implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [10, 60];
    public int $timeout = 60;

    public function __construct(private string $csvPath) {}

    public function handle(): void
    {
        $filePath = Storage::path($this->csvPath);
        $fileName = basename($this->csvPath);

        $lines = file(
            $filePath,
            FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
        );

        $rows = array_map('str_getcsv', $lines);
        $header = array_shift($rows);

        // Menghapus BOM UTF-8 pada header pertama CSV.
        if (isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        }

        foreach ($rows as $index => $row) {
            // Nomor baris dimulai dari 2 karena baris pertama adalah header.
            $rowNumber = $index + 2;

            // Memeriksa jumlah kolom.
            if (count($row) !== count($header)) {
                ImportError::create([
                    'file_name' => $fileName,
                    'row_number' => $rowNumber,
                    'error_message' => 'Jumlah kolom tidak sesuai dengan header CSV.',
                ]);

                continue;
            }

            $validator = Validator::make(
                array_combine($header, $row),
                [
                    'name' => ['required', 'string'],
                    'category_id' => ['required', 'exists:categories,id'],
                    'price' => ['required', 'integer', 'min:0'],
                ]
            );

            // Menyimpan baris yang gagal beserta pesan error.
            if ($validator->fails()) {
                ImportError::create([
                    'file_name' => $fileName,
                    'row_number' => $rowNumber,
                    'error_message' => implode(
                        ' ',
                        $validator->errors()->all()
                    ),
                ]);

                continue;
            }

            // Menyimpan atau memperbarui produk yang valid.
            $data = $validator->validated();

            Product::updateOrCreate(
                [
                    'name' => $data['name'],
                    'category_id' => $data['category_id'],
                ],
                [
                    'price' => $data['price'],
                ]
            );
        }

        // Menghapus file CSV setelah proses import selesai.
        Storage::delete($this->csvPath);
    }
}
