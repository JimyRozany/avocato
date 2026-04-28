<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Judgment extends Model
{
    
    protected $guarded = [];

    public function case()
    {
        return $this->belongsTo(CaseModel::class);
    }
}
