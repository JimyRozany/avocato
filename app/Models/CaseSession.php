<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseSession extends Model
{
    protected $guarded = [];
    protected $fillable = [
        'case_id',
        'session_date',
        'decision',
        'notes',
        'next_session_date',
        'created_by',
    ];

    public function case()
    {
        return $this->belongsTo(CaseModel::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'case_session_id');
    }
}
