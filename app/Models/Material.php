<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'category_id', 'subject_id', 'title', 'description', 'semester', 'price', 'image_path', 'condition', 'status'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function enquiries()
    {
        return $this->hasMany(Enquiry::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }
}
