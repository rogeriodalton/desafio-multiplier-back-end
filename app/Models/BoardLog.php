<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BoardLog extends Model
{
    use HasFactory;

    protected $table = 'board_logs';

    protected $casts = [
        'id' => 'integer',
        'board_id' => 'integer',
        'waiter_id' => 'integer',
        'client_id' => 'integer',
	];

    protected $fillable = [
        'board_id',
        'waiter_id',
        'client_id',
        'state',
        'total_order',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function setBoardLog(Request $request, array &$dataFields = [])
    {
        if ($request->has('board_id'))
            $this->attributes['board_id'] = $request->board_id;

        if ($request->has('client_id'))
            $this->attributes['client_id'] = $request->clientId;
        elseif (array_key_exists('client_id', $dataFields))
            $this->attributes['client_id'] = $dataFields['client_id'];

        if ($request->has('waiter_id'))
            $this->attributes['waiter_id'] = $request->waiter_id;
        elseif (array_key_exists('waiter_id', $dataFields))
            $this->attributes['waiter_id'] = $dataFields['waiter_id'];

        if ($request->has('state'))
            $this->attributes['state'] = $request->state;
        elseif (array_key_exists('state', $dataFields))
            $this->attributes['state'] = $dataFields['state'];

        $this->save();
        return $this;
    }

    public function setBoardLogTotalOrder(array &$orderItems = [])
    {
        $total = 0;

        if (!$orderItems)
            return 'informe order items';

        foreach ($orderItems as $key => $value) {
            $total = $total + ($value['price'] * $value['amount']);
        }

        $this->attributes['total_order'] = $total;
        $this->save();
        return $this;
    }
}
