<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Musics extends Model
{
    protected $table = "musics";
    public function post()
    {
        return $this->belongsTo('App\Artists');
    }
}
