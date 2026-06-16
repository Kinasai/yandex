<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParseLog extends Model
{
    use HasFactory;

    protected $fillable = ['parse_task_id', 'log_level', 'message'];

    public function parseTask()
    {
        return $this->belongsTo(ParseTask::class);
    }
}
