<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Artists extends Model
{
    protected $table = "artists";

    public function musics()
    {
        return $this->hasMany('App\Models\Musics','artist_id');
    }
}
