<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripUserChecklistItem extends Model
{
    use HasFactory;

    protected $table = 'trip_user_checklist_items'; // ✅ nom exact de ta table

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

    /**
     * 🔗 Lien vers le pivot trip_user (lien entre user et voyage)
     */
    public function tripUser()
    {
        return $this->belongsTo(TripUser::class);
    }

    /**
     * 🔗 Lien vers l’élément commun de checklist
     */
    public function checklistItem()
    {
        return $this->belongsTo(ChecklistItem::class);
    }
}
