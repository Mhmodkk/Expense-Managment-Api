<?php

namespace App\Models;

use App\Traits\Encryptable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Encryptable;

    protected array $encryptable = [
        'name',
        'email',
    ];

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'currency_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
