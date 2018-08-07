<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackingLogin extends Model
{
    protected $table = "tracking_login";
    protected $fillable   = ["id","user_id","token","day_working","status","created_at","updated_at"];							
}
