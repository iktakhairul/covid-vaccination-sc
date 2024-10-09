<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'vaccine_center_id',
        'full_name',
        'nid',
        'email',
        'phone_number',
        'status',
        'scheduled_vaccination_date',
        'vaccinated_at',
        'created_at',
        'updated_at',
    ];

    // Define the relationship to the VaccineCenter model
    public function vaccineCenter()
    {
        return $this->belongsTo(VaccineCenter::class, 'vaccine_center_id');
    }
}
