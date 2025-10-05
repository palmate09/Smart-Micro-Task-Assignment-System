<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str; 

class UserSkill extends Model {
    
    use HasFactory; 

    protected $table = 'user_skills'; 
    protected $keyType = 'string'; //UUID 
    public $incrementing = false; //UUID is not auto-incrementing

    protected static function booted() {
        static::creating(function($model){
            if(empty($model->id)){
                $model->id = (string) Str::uuid();
            } 
        }); 
    }

    protected $fillable = [
        'user_id', 
        'skill_id',
        'proficiency'
    ]; 

    
    // user skills belongs to a user
    public function user() {
        return $this->belongsTo(User::class); 
    }

    // A userSkills belogns to a skill 
    public function skills() {
        return $this->belongsTo(Skills::class); 
    }
}
