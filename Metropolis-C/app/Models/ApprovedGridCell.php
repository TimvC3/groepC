<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovedGridCell extends Model
{
    protected $fillable = [
        'cell_index',
        'item_type',
        'item_id',
        'item_name',
        'approved_by',
    ];
}
