<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sections extends Model
{
    use HasFactory;
    protected $fillable = [
        'section_name',
        'description',
        'created_by',
    ];



    public function products()
    {
        return $this->hasMany(Products::class);
    }
    public function invoice()
    {
        return $this->hasMany(invoices::class);
    }
}
