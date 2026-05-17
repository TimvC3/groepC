<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZoningDesignation extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'category',
        'icon',
    ];
}