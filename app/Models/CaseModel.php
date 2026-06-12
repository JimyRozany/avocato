<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaseModel extends Model
{
    use SoftDeletes;

    const STATUS_PENDING   = 'pending';
    const STATUS_ACTIVE    = 'active';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_FLAGGED   = 'flagged';
    const STATUS_CLOSED    = 'closed';

    protected $table = 'cases';

    protected $guarded = [];

    public function client()
    {
        return $this->hasOneThrough(User::class, CaseParty::class, 'case_id', 'id', 'id', 'user_id')
            ->whereHas('roles', fn ($q) => $q->where('name', 'client'));
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parties()
    {
       return $this->hasMany(CaseParty::class, 'case_id');
    }

    public function lawyers()
    {
        return $this->belongsToMany(User::class, 'case_lawyers', 'case_id', 'lawyer_id')
                    ->withPivot('side')
                    ->withTimestamps();
    }

    public function sessions()
    {
        return $this->hasMany(CaseSession::class , 'case_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class , 'case_id');
    }

    public function judgments()
    {
        return $this->hasMany(Judgment::class , 'case_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

}
