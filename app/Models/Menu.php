<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menus';

    protected $fillable = [
        'id',
        'name',
        'description',
        'price',
    ];

    protected $casts = [
		'id' => 'integer',
        'name' =>'string',
        'description' =>'string',
        'price' => 'float',
	];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function setMenu(Request $request)
    {
        $this->attributes['name'] = $request->name;
        $this->attributes['description'] = $request->description;
        $this->attributes['price'] = $request->price;
        $this->save();
        return $this;
    }


}
