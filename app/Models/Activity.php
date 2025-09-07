<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'step_id', 'title', 'description',
        'start_at', 'end_at',
        'external_link',
        'cost', 'currency', 'category',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
        'cost'     => 'decimal:2',
    ];

    public function step()
    {
        return $this->belongsTo(Step::class);
    }

    public function trip()
    {
        return $this->step?->trip();
    }
}
