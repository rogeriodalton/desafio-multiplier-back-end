<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    use HasFactory;

    protected $table = 'user_groups';

    protected $fillable = [
        'user_id',
        'group_id',
    ];

    protected $casts = [
		'id' => 'integer',
        'user_id' => 'integer',
        'group_id' =>'int',
	];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function setUserGroups(array &$dataField = [])
    {
        $this->attributes['user_id'] = $dataField['user_id'];
        $this->attributes['group_id'] = $dataField['group_id'];
        $this->save();
        return $this;
    }

}
