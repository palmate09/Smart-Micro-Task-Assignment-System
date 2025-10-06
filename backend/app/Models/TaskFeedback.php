<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class TaskFeedback extends Model {
    use HasFactory;

    protected $table = 'task_feedback';
    protected $keyType = 'string';
    public $incrementing = false;

    protected static function booted() {
        static::creating(function($model){
            if(empty($model->id)){
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'task_id',
        'worker_id',
        'rating',
        'review',
    ];

    protected $casts = [
        'rating' => 'float',
    ];

    public function task(){
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function worker(){
        return $this->belongsTo(User::class, 'worker_id');
    }
}


