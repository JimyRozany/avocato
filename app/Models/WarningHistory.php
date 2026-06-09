<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarningHistory extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function target()
    {
        return $this->belongsTo(User::class, 'lawyer_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
