<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseParty extends Model
{

    protected $guarded = [];

    public function case()
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
