<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JobSign extends Model
{
    protected $table = 'job_sign';
    public function to_personal(){
        return $this->belongsTo("App\Personal","user_id","user_id");
    }
    public function to_job(){
        return $this->belongsTo("App\Job","job_id","id");
    }
}
