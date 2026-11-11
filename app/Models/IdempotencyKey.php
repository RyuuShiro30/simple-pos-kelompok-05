<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable; 
use Illuminate\Database\Eloquent\Builder;

class IdempotencyKey extends Model
{
    use Prunable; 

    protected $fillable = ['user_id', 'key', 'request_hash', 'response_status', 'response_body'];

    /**
     * Tentukan data mana yang akan dihapus secara otomatis (berumur > 24 jam).
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subHours(24));
    }
}