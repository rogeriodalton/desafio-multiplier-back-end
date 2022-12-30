<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'order_items';

    protected $fillable = [
        'board_log_id',
        'menu_id',
        'price',
        'amount',
        'amount_to_pay',
        'state',
    ];

    protected $casts = [
		'id' => 'integer',
        'board_log_id' =>'integer',
        'menu_id' =>'integer',
        'price' => 'float',
        'amount' => 'integer',
        'amount_to_pay' => 'float',
        'state' => 'string',
	];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function setOrderItems(Request $request, array &$dataFields = [])
    {
        $amount = 1;

        if ($request->has('order_id'))
            $this->attributes['board_log_id'] = $request->order_id;

        if ($request->has('board_log_id'))
            $this->attributes['board_log_id'] = $request->board_log_id;

        if ($request->has('menu_id'))
            $this->attributes['menu_id'] = $request->menu_id;

        if ($request->has('amount')) {
            $amount = $request->amount;
            $this->attributes['amount'] = $request->amount;
        }

        if (array_key_exists('price', $dataFields)) {
            $this->attributes['price'] = $dataFields['price'];
            $this->attributes['amount_to_pay'] = ($amount * $dataFields['price']);
        }

        if ($request->has('state'))
            $this->attributes['state'] = $request->state;

        elseif (array_key_exists('state', $dataFields))
            $this->attributes['state'] = $dataFields['state'];

        elseif (array_key_exists('amount', $dataFields))
            $this->attributes['amount'] = $dataFields['amount'];



        $this->save();
        return $this;
    }

}
