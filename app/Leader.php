<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Leader extends Model
{

    protected $fillable = [
        'user_id',"create_time"
    ];

    protected $table = 'leader_user';
    public function to_personal(){
        return $this->belongsTo("App\Personal","user_id","user_id");
    }
}
