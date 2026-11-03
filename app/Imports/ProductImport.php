<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductImport implements ToModel, WithHeadingRow, WithChunkReading
{
    public function model(array $row)
    {
        if (!Category::where('id', $row['category_id'])->exists()) {
            return null;
        }

        $product = Product::firstOrNew([
            'name' => $row['name'],
            'category_id' => $row['category_id'],
        ]);

        $product->price = $row['price'];

        return $product;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}