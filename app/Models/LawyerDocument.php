<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LawyerDocument extends Model
{
    protected $guarded = [];

    public function lawyer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
