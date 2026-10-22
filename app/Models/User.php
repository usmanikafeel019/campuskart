<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $attributes = ['role' => 'student', 'is_active' => true];

    protected $fillable = ['name', 'email', 'password', 'phone'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'email_verified_at' => 'datetime', 'is_active' => 'boolean'];
    }

    public function materials()
    {
        return $this->hasMany(Material::class);
    }

    public function enquiries()
    {
        return $this->hasMany(Enquiry::class, 'buyer_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }
}
