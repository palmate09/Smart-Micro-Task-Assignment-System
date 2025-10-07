<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;


    public $incrementing = false; 
    protected $keyType = 'string';

    protected static function booted() {
        static::creating(function($user){
            $user->id = (string) Str::uuid(); 
        }); 
    }



    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', 
        'rating', 
        'availability_status'
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


    // skills that belongs to the user
    public function skills() {
        return $this->belongsToMany(
            Skills::class, 
            'user_skills', 
            'user_id', 
            'skill_id'
        )
        ->withPivot('proficiency')
        ->withTimestamps();    
    }

    public function tasks() {
        return $this->hasMany(Task::class, 'assigned_worker_id');
    }

    public function feedbacks() {
        return $this->hasMany(TaskFeedback::class, 'worker_id');
    }

    public function userSkill() {
        return $this->hasMany(UserSkill::class);
    }
}
