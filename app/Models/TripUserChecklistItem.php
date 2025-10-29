<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripUserChecklistItem extends Model
{
    use HasFactory;

    protected $table = 'trip_user_checklist_items';

    protected $fillable = [
        'trip_user_id',
        'checklist_item_id',
        'is_checked',
        'checked_at',
    ];

    protected $casts = [
        'is_checked' => 'boolean',
        'checked_at' => 'datetime',
    ];


    public function tripUser()
    {
        return $this->belongsTo(TripUser::class);
    }


    public function checklistItem()
    {
        return $this->belongsTo(ChecklistItem::class);
    }
}
