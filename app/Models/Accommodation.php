<?php

// app/Models/Accommodation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accommodation extends Model
{
    protected $fillable = [
        'step_id',
        'title',
        'description',
        'location',
        'external_link',
        'start_date',
        'end_date',
    ];

    public function step()
    {
        return $this->belongsTo(Step::class);
    }
}


