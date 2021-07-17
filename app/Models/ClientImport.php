<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'read_count',
        'import_count',
        'import_attempts',
        'import_file',
        'user_id',
    ];
}
