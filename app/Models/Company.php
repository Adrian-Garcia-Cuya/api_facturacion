<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'social_reason',
        'ruc',
        'address',
        'logo_path',
        'sol_user',
        'sol_pass',
        'certificate_path',
        'client_id',
        'client_secret',
        'production',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
