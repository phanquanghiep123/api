<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Downloads extends Model
{
    protected $table = "downloads";
    public $artists_table = "artists";
    public $musics_table = "musics";
    protected function file($today,$track_id,$artist_id,$public_key){
        return $this->select($this->musics_table.".*",$this->table.".id AS dowload_id")
        ->join($this->artists_table,$this->table.".artist_id","=",$this->artists_table.".id")
        ->join($this->musics_table,$this->artists_table.".id","=",$this->musics_table.".artist_id")
        ->where([
            [$this->table.".start","<=",$today],
            [$this->table.".end",">=",$today],
            [$this->table.".public_key","=",$public_key],
            [$this->musics_table.".id","=",$track_id],
            [$this->artists_table.".id","=",$artist_id]
        ])->first();
    }
}
