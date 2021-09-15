<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

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

    public function users()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }
}
