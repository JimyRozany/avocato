<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Legal extends Model
{
    protected $guarded = [];

    protected $hidden = ['embedding'];

    protected $table = 'legals';
}
