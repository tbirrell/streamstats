<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stream extends Model
{
    use HasFactory;

    protected $fillable = [
        'stream_id',
        'channel_name',
        'stream_title',
        'game',
        'viewer_count',
        'start_time',
        'top_thousand'
    ];

}
