<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;




#[Fillable(['name', 'email', 'password' ,'mobile' , 'image' , 'type' , 'is_active' , 'status' , 'rate'])]
#[Hidden(['password', 'remember_token'])]

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable , HasRoles , HasApiTokens;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * =======================================
     */

    public function casesCreated()
    {
        return $this->hasMany(CaseModel::class, 'created_by');
    }

  
    public function casesAsLawyer()
    {
        return $this->belongsToMany(
            CaseModel::class,
            'case_lawyers',
            'lawyer_id',
            'case_id'
        )->withPivot('side')->withTimestamps();
    }

    public function caseParticipations()
    {
        return $this->hasMany(CaseParty::class);
    }
}
