<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Board extends Model
{
    use HasFactory;

    protected $table = 'boards';

    protected $casts = [
		'id' => 'integer',
        'state' => 'string',
	];

    protected $fillable = [
        'state',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function addBoards()
    {
        $this->attributes['state'] = 'livre';
        $this->save();
    }

}
