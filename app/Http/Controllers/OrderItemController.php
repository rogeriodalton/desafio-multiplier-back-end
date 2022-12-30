<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\OrderItem;
use App\Models\BoardLog;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderItemController extends Controller
{
    private BoardLog $Order;
    private OrderItem $OrderItem;
    private Menu $Menu;
    private Request $Request;
    private $loggedIn;
    private int $userId = 0;
    private bool $isAdmin  = false;
    private bool $isClient = false;
    private bool $isCooker = false;
    private bool $isWaiter = false;
    private array $fields = [
        'board_logs.id as orderId',
        'order_items.id as orderItemId',
        'board_id as boardId',
        'board_logs.state as orderState',
        'menus.id as menuItemId',
        'menus.name as menuName',
        'order_items.price as priceUnit',
        'order_items.amount',
        'order_items.state as orderItemState',
        'client.name as client',
        'waiter.name as waiter',
    ];

    public function __construct(OrderItem $orderItem, BoardLog $order, Menu $menu, Request $request)
    {
        $this->Order = $order;
        $this->OrderItem = $orderItem;
        $this->Menu = $menu;
        $this->Request = $request;
        $this->loggedIn = auth('sanctum')->user();
        $this->userId = auth('sanctum')->user()->id;

        if ($this->loggedIn) {
            $this->isAdmin  = $this->getPermission($this->loggedIn, 1); //administrador
            $this->isClient = $this->getPermission($this->loggedIn, 2); //cliente
            $this->isCooker = $this->getPermission($this->loggedIn, 3); //cozinheiro
            $this->isWaiter = $this->getPermission($this->loggedIn, 4); //garçom
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!$this->isAdmin)
            return $this->msgNotAuthorized();

        return response()->json(
            $this->OrderItem->join('menus', 'menus.id','order_items.menu_id')
                            ->join('board_logs', 'board_logs.id', 'order_items.board_log_id')
                            ->join('users as waiter', 'waiter.id', 'board_logs.waiter_id')
                            ->leftjoin('users as client', 'client.id', 'board_logs.client_id')
                            ->select($this->fields)
                            ->orderBy('board_logs.id', 'desc')
                            ->paginate(50));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        if ((!$this->isWaiter) && (!$this->isAdmin))
            return $this->msgNotAuthorized();

        $rules = [
            'menu_id' => 'required|integer|exists:App\Models\Menu,id',
            'order_id' => 'required|integer|exists:App\Models\BoardLog,id',
        ];

        $validator = Validator::make($this->Request->all(), $rules);
        if ($validator->fails())
            return $this->msgMissingValidator($validator);

        $menu = $this->Menu->find($this->Request->menu_id);

        $dataFields = [
            'price' => $menu->price,
            'amount' => 1,
            'state' => 'aguardando',
        ];

        $orderItem = new OrderItem;
        $orderItem->setOrderItems($this->Request, $dataFields);

        $orderItems = $this->OrderItem->where('board_log_id', $this->Request->order_id)
                                      ->select('id', 'amount', 'price')
                                      ->get()
                                      ->toArray();

        $order = $this->Order->find($orderItem->board_log_id);
        $order->setBoardLogTotalOrder($orderItems);

        return $this->msgInclude($orderItem);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(string $id = null)
    {
        if ($id == 'help')
            return $this->help();

        if (!$this->Request->has('order_id'))
            return $this->msgNotHasField('order_id');

        switch ($id) {
            case 'admin':   if ($this->isAdmin)
                                return response()->json(
                                    $this->OrderItem->join('menus', 'menus.id','order_items.menu_id')
                                                    ->join('board_logs', 'board_logs.id', 'order_items.board_log_id')
                                                    ->join('users as waiter', 'waiter.id', 'board_logs.waiter_id')
                                                    ->leftjoin('users as client', 'client.id', 'board_logs.client_id')
                                                    ->select($this->fields)
                                                    ->where('board_logs.id', $this->Request->order_id)
                                                    ->orderBy('board_logs.id', 'desc')
                                                    ->get());
                            else
                                return $this->msgNotAuthorized();

            case 'waiter':  if ($this->isWaiter)
                                return response()->json(
                                    $this->OrderItem->join('menus', 'menus.id','order_items.menu_id')
                                                    ->join('board_logs', 'board_logs.id', 'order_items.board_log_id')
                                                    ->join('users as waiter', 'waiter.id', 'board_logs.waiter_id')
                                                    ->leftjoin('users as client', 'client.id', 'board_logs.client_id')
                                                    ->where('board_logs.waiter_id', $this->userId)
                                                    ->where('board_logs.id', $this->Request->order_id)
                                                    ->select($this->fields)
                                                    ->orderBy('board_logs.id', 'desc')
                                                    ->get());
                            else
                                return $this->msgNotAuthorized();

            case 'cooker':  if (($this->isCooker) || ($this->isWaiter) || ($this->isAdmin))
                                return response()->json(
                                    $this->OrderItem->join('menus', 'menus.id','order_items.menu_id')
                                                    ->join('board_logs', 'board_logs.id', 'order_items.board_log_id')
                                                    ->join('users as waiter', 'waiter.id', 'board_logs.waiter_id')
                                                    ->leftjoin('users as client', 'client.id', 'board_logs.client_id')
                                                    ->where('order_items.state', 'aguardando')
                                                    ->orWhere('order_items.state', 'preparando')
                                                    ->select($this->fields)
                                                    ->orderBy('board_logs.id', 'desc')
                                                    ->get());
                            else
                                return $this->msgNotAuthorized();
            default:
                return $this->msgResourceNotExists();
         };
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update()
    {
        if ((!$this->isWaiter) && (!$this->isAdmin))
            return $this->msgNotAuthorized();

        $rules = [
            'menu_id' => 'required|integer|exists:App\Models\Menu,id',
            'order_id' => 'required|integer|exists:App\Models\BoardLog,id',
            'order_item_id' => 'required|integer|exists:App\Models\OrderItem,id',
        ];

        $validator = Validator::make($this->Request->all(), $rules);
        if ($validator->fails())
            return $this->msgMissingValidator($validator);

        $menu = $this->Menu->find($this->Request->menu_id);

        $dataFields = [
            'price' => $menu->price,
            'amount' => 1,
            'state' => 'aguardando',
        ];

        $orderItem = $this->OrderItem->where('id', $this->Request->order_item_id)
                                     ->where('board_log_id', $this->Request->order_id)
                                     ->first();
        if (!$orderItem)
            return $this->msgRecordNotFound();

        $orderItem->setOrderItems($this->Request, $dataFields);

        $orderItems = $this->OrderItem->where('board_log_id', $this->Request->order_id)
                                      ->select('id', 'amount', 'price')
                                      ->get()
                                      ->toArray();

        $order = $this->Order->find($orderItem->board_log_id);
        $order->setBoardLogTotalOrder($orderItems);


        return $this->msgUpdated($orderItem);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        if (!$this->isAdmin)
            return $this->msgNotAuthorized();

        $rules = [
            'order_id' => 'required|integer|exists:App\Models\BoardLog,id',
            'order_item_id' => 'required|integer|exists:App\Models\OrderItem,id',
        ];

        $validator = Validator::make($this->Request->all(), $rules);
        if ($validator->fails())
            return $this->msgMissingValidator($validator);

        $orderItem = $this->OrderItem->where('id', $this->Request->order_item_id)
                                     ->where('board_log_id', $this->Request->order_id)
                                     ->first();

        if (!$orderItem)
            return $this->msgRecordNotFound();

        try {
            $orderItem->delete();
            return $this->msgDeleted();
        } catch (Exception $e) {
            return $this->msgNotDeleted($e);
        }
    }

    private function help()
    {
        return response()->json([
            'help' => [
                '[ GET ]    /orderItems/help'   => 'Informações sobre o point solicitado.',
                '[ GET ]    /orderItems'        => 'Apresenta todos pedidos do sistema para usuário grupo "administrador", usuário Grupo "garçom" somente seus pedidos e usuário grupo "cozinheiro" pedidos em "preparo" ou "aguardando". ',
                '[ POST ]   /orderItems -> novos itens do pedido' => [
                    '{order_id}' => 'id do pedido',
                    '{menu_id}' => 'id do cardápio selecionado',
                    'state'  => 'Estado do pedido, caso não informado o state será "aguardando", mas pode ser informado como ["aguardando", "preparando", "pronto", "entregue"] ',
                ],
                '[ PUT ]   /orderItems -> Alterar usuários do sistema' => [
                    '{order_id}' => 'id do pedido',
                    'menu_id' => 'id do cardápio selecionado ',
                    'state'  => 'Estado do pedido a ser definido ["aguardando", "preparando", "pronto", "entregue"] ',

                ],
                '[ DELETE ]   /orderItems -> Excluir ítem do pedido' => [
                    '{order_id}' => 'id do pedido',
                    '{order_item_id}' => 'item do pedido'
                ],

            ],
        ]);
    }

}
