<?php
use Illuminate\Support\Facades\Schedule;
use App\Models\IdempotencyKey;

Schedule::command('model:prune', [
    '--model' => [IdempotencyKey::class],
])->daily();