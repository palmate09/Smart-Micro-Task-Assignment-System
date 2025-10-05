<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Skills extends Model {
    
    use HasFactory; 

    protected $table = 'skills'; 
    protected $keyType = 'string'; // UUID  
    public $incrementing  = false; // UUID is not auto-incrementing

    protected static function booted() {
        static::creating(function($skill){
            $skill->id = (string) Str::uuid(); 
        }); 
    }


    protected $fillable = [
        'name'
    ]; 

    // users that have this skill (many to many) 
    public function users() {
        return $this->belongsToMany(
            User::class, 
            'user_skills', 
            'skill_id', 
            'user_id'
        )
        ->withPivot('proficiency')
        ->withTimestamps(); 
    }

}
