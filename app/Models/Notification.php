<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    public $table = 'notifications';

    protected $fillable = [];

    protected $guarded = [];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'action_route' => 'array',
    ];
}
