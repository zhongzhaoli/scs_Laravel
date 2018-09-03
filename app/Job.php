<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $table = 'job';
    public function to_user(){
        return $this->belongsTo("App\User","user_id","id");
    }
    public function to_job_sign(){
        return $this->hasOne("App\JobSign","job_id","id");
    }
}
