<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Personal extends Model
{
    protected $table = 'personal_user';
    public function to_user(){
        return $this->hasOne("App\User","id","user_id");
    }
    public function to_leader(){
        return $this->hasOne("App\Leader","user_id","user_id");
    }
    public function to_jobsign(){
        return $this->hasOne("App\JobSign","user_id","user_id");
    }
}
