<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function case()
    {
        return $this->belongsTo(CaseModel::class);
    }

    public function caseSession()
    {
        return $this->belongsTo(CaseSession::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    protected function filePath(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? url('storage/' . $value) : null,
        );
    }
}
