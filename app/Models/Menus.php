<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menus extends Model
{
    protected $table = "menus";
    protected $fillable   = ["id","name","slug","class_name","id_name","type","status","created_at","updated_at"];							
}
