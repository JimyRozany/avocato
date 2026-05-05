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
    'next_session_date'
 ];

    public function case()
    {
        return $this->belongsTo(CaseModel::class);
    }
}
