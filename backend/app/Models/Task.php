<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str; 

class Task extends Model {

    use HasFactory; 

    protected $table = 'tasks'; 
    protected $keyType = 'string'; 
    public $incrementing = false; 

    
    protected $fillable = [
        'title', 
        'description', 
        'required_skills', 
        'estimated_duration',  // in hours
        'deadline', 
        'status', 
        'assigned_worker_id', 
        'created_by' //company user_id
    ]; 
    
    // casts for automatic data conversion to the respective types
    protected $casts = [
        'required_skills' => 'array', 
        'deadline' => 'datetime', 
    ];
     
    protected static function booted() {
        static::creating(function($model) {
            if(empty($model->id)){
                $model->id = (string) Str::uuid(); 
            }
        }); 
    }

    // Worker belongs to the task 
    public function assignedWorker(){
        return $this->belongsTo(User::class, 'assigned_worker_id'); 
    }

    // company who created the task  
    public function createdByCompany(){
        return $this->belongsTo(User::class, 'created_by'); 
    }

    
    public function feedback(){
        return $this->hasOne(TaskFeedback::class);
    }

}
