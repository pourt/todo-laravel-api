<?php

namespace PERP\Task\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    public $afterCommit = true;

    protected $guarded = ['id'];

    protected $fillable = [
        'id',
        'title',
        'description',
        'due_date',
        'status',
    ];

    protected $hidden = [];

    protected $cast = [
        'due_date' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'deleted_at' => 'datetime:Y-m-d H:i:s',
    ];
}
