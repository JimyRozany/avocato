<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseSession extends Model
{
 protected $guarded = [];

    public function case()
    {
        return $this->belongsTo(CaseModel::class);
    }
}
