<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class TaskLog extends Model {
    use HasFactory;

    protected $table = 'task_logs';
    protected $keyType = 'string';
    public $incrementing = false;

    protected static function booted(){
        static::creating(function($model){
            if(empty($model->id)){
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'task_id',
        'worker_id',
        'status',
        'start_time',
        'end_time',
        'comments',
    ];
}


