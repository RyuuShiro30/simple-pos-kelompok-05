<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportError extends Model
{
    protected $fillable = [
        'file_name',
        'row_number',
        'error_message',
    ];
}
