<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\OrderItem;
use App\Models\BoardLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class OrderReportController extends Controller
{
    private BoardLog $BoardLog;
    private OrderItem $OrderItem;
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
        'board_logs.state as orderState',
        'board_logs.total_order',
        'board_id as boardId',
        'menus.id as menuItemId',
        'menus.name as menuName',
        'order_items.price as priceUnit',
        'order_items.amount',
        'order_items.amount_to_pay as total',
        'order_items.state as orderItemState',
        'client.name as client',
        'waiter.name as waiter',
    ];

    private $fieldsBoardLog = [
        'board_logs.id as id',
        'board_logs.board_id',
        'board_logs.total_order',
        'board_logs.state',
        'client.name as client',
        'waiter.name as waiter',
        'board_logs.created_at as boardLog_created_at',
        'board_logs.updated_at as boardLog_updated_at',
    ];

    public function __construct(BoardLog $boardLog, OrderItem $orderItem, Request $request)
    {
        $this->BoardLog = $boardLog;
        $this->OrderItem = $orderItem;
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
        return $this->help();
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(string $id = null, string $iid = null)
    {
        $msgRoute = '/api/orderReport/?   (help  or  client  or  admin  or  waiter  or  cooker)';

        switch ($id) {
            case 'help'  : return $this->help();
            case 'client': return $this->rptClient($iid);
            case 'admin' : return $this->rptAdmin($iid);
            case 'waiter': return $this->rptWaiter($iid);
            case 'cooker': return $this->rptCooker($iid);
        default:
            return $this->msgGeneric($msgRoute);
        };

    }

    private function rptAdmin(string $iid = null)
    {
        $msgRoute = '/api/orderReport/admin/?   (day-week-year  or  month-year  or  year  or  board  or  client)';

        $msgRoute = response()->json([
            'help' => [
                '[ POST ]   /api/orderReport/admin/day-week-year'  => 'Pedidos de um dia mês e ano',
                '[ POST ]   /api/orderReport/admin/month-year'  => 'Pedidos de um mes e ano',
                '[ POST ]   /api/orderReport/admin/year'  => 'Pedidos de um ano',
                '[ POST ]   /api/orderReport/admin/board'  => 'Pedidos gerados de uma mesa',
                '[ POST ]   /api/orderReport/admin/client'  => 'Pedidos gerados de um cliente de valor maior para o menor.',
            ],
        ]);

        if (!$this->isAdmin)
            return $this->msgNotAuthorized();

        if (!$this->Request->has('year'))
            return $this->msgNotHasField('year');

            switch ($iid) {
                case 'day-week-year': {
                     if (!$this->Request->has('month'))
                         return $this->msgNotHasField('month');

                     if (!$this->Request->has('day'))
                         return $this->msgNotHasField('month');

                     return response()->json(
                         $this->BoardLog->join('users as waiter', 'waiter.id', 'board_logs.waiter_id')
                                        ->leftjoin('users as client', 'client.id', 'board_logs.client_id')
                                        ->whereYear('board_logs.created_at', $this->Request->get('year'))
                                        ->whereMonth('board_logs.created_at', $this->Request->get('month'))
                                        ->whereDay('board_logs.created_at', $this->Request->get('day'))
                                        ->select($this->fieldsBoardLog)
                                        ->orderBy('board_logs.id', 'desc')
                                        ->paginate(100));
                }

                case 'month-year': {
                     if (!$this->Request->has('month'))
                        return $this->msgNotHasField('month');

                     return response()->json(
                                $this->BoardLog->join('users as waiter', 'waiter.id', 'board_logs.waiter_id')
                                               ->leftjoin('users as client', 'client.id', 'board_logs.client_id')
                                               ->whereYear('board_logs.created_at', $this->Request->get('year'))
                                               ->whereMonth('board_logs.created_at', $this->Request->get('month'))
                                               ->select($this->fieldsBoardLog)
                                               ->orderBy('board_logs.id', 'desc')
                                               ->paginate(100));
                }

                case 'year': return response()->json(
                                        $this->BoardLog->join('users as waiter', 'waiter.id', 'board_logs.waiter_id')
                                                       ->leftjoin('users as client', 'client.id', 'board_logs.client_id')
                                                       ->whereYear('board_logs.created_at', $this->Request->get('year'))
                                                       ->select($this->fieldsBoardLog)
                                                       ->orderBy('board_logs.id', 'desc')
                                                       ->paginate(100));

                case 'board': {
                    if (!$this->Request->has('board'))
                        return $this->msgNotHasField('board');

                    return response()->json(
                                        $this->BoardLog->join('users as waiter', 'waiter.id', 'board_logs.waiter_id')
                                                       ->leftjoin('users as client', 'client.id', 'board_logs.client_id')
                                                       ->where('board_logs.board_id', $this->Request->get('board'))
                                                       ->select($this->fieldsBoardLog)
                                                       ->orderBy('board_logs.id', 'desc')
                                                       ->paginate(100));
                }

                case 'client': {

                    if (!$this->Request->has('board'))
                        return $this->msgNotHasField('board');

                    return response()->json(
                                        $this->BoardLog->join('users as waiter', 'waiter.id', 'board_logs.waiter_id')
                                                       ->leftjoin('users as client', 'client.id', 'board_logs.client_id')
                                                       ->where('board_logs.client_id', $this->Request->get('client_id'))
                                                       ->select($this->fieldsBoardLog)
                                                       ->orderBy('board_logs.id', 'desc')
                                                       ->paginate(100));
                }

            default:
                return $msgRoute;
            };

            if (!$iid)
                return response()->json(
                    $this->OrderItem->join('menus', 'menus.id','order_items.menu_id')
                                    ->join('board_logs', 'board_logs.id', 'order_items.board_log_id')
                                    ->join('users as waiter', 'waiter.id', 'board_logs.waiter_id')
                                    ->leftjoin('users as client', 'client.id', 'board_logs.client_id')
                                    ->select($this->fields)
                                    ->orderBy('board_logs.id', 'desc')
                                    ->paginate(50));
    }

    private function rptWaiter(string $iid = null)
    {
        if ((!$this->isWaiter) && (!$this->isAdmin))
            return $this->msgNotAuthorized();

        $msgRoute = response()->json([
            'help' => [
                '[ GET ]   /api/orderReport/waiter/all'  => 'Todos pedidos, quando logado Garçom, pedidos do garçom, caso Administrador pedidos de todos os garçons',
                '[ GET ]   /api/orderReport/waiter/progress'  => 'Pedidos em andamento do garçom logado ou caso de login Administrador, todos os pedidos em andamento de todos os garçons',
                '[ GET ]   /api/orderReport/waiter/closed'  => 'Pedidos encerrados do garçom logado ou caso de login Administrador, todos os pedidos encerrados de todos os garçons',
            ],
        ]);


        switch ($iid) {

            case 'help': return $msgRoute;

            case 'all': if ($this->isWaiter)
                            return response()->json(
                                        $this->BoardLog->join('users as waiter', 'waiter.id', 'board_logs.waiter_id')
                                                    ->leftjoin('users as client', 'client.id', 'board_logs.client_id')
                                                    ->where('waiter.id', $this->userId)
                                                    ->select($this->fieldsBoardLog)
                                                    ->orderBy('board_logs.id', 'desc')
                                                    ->paginate(100));
                        else
                            return response()->json(
                                        $this->BoardLog->join('users as waiter', 'waiter.id', 'board_logs.waiter_id')
                                                    ->leftjoin('users as client', 'client.id', 'board_logs.client_id')
                                                    ->select($this->fieldsBoardLog)
                                                    ->orderBy('board_logs.id', 'desc')
                                                    ->paginate(100));

            case 'progress': if ($this->isWaiter)
                                return response()->json(
                                        $this->BoardLog->join('users as waiter', 'waiter.id', 'board_logs.waiter_id')
                                                       ->leftjoin('users as client', 'client.id', 'board_logs.client_id')
                                                       ->where('waiter.id', $this->userId)
                                                       ->where('state', 'aberto')
                                                       ->select($this->fieldsBoardLog)
                                                       ->orderBy('board_logs.id', 'desc')
                                                       ->paginate(100));
                            else
                                return response()->json(
                                        $this->BoardLog->join('users as waiter', 'waiter.id', 'board_logs.waiter_id')
                                                       ->leftjoin('users as client', 'client.id', 'board_logs.client_id')
                                                       ->where('state', 'aberto')
                                                       ->select($this->fieldsBoardLog)
                                                       ->orderBy('board_logs.id', 'desc')
                                                       ->paginate(100));

            case 'closed': if ($this->isWaiter)
                                return response()->json(
                                        $this->BoardLog->join('users as waiter', 'waiter.id', 'board_logs.waiter_id')
                                                        ->leftjoin('users as client', 'client.id', 'board_logs.client_id')
                                                        ->where('waiter.id', $this->userId)
                                                        ->where('state', 'fechado')
                                                        ->select($this->fieldsBoardLog)
                                                        ->orderBy('board_logs.id', 'desc')
                                                        ->paginate(100));
                            else
                                return response()->json(
                                        $this->BoardLog->join('users as waiter', 'waiter.id', 'board_logs.waiter_id')
                                                        ->leftjoin('users as client', 'client.id', 'board_logs.client_id')
                                                        ->where('state', 'fechado')
                                                        ->select($this->fieldsBoardLog)
                                                        ->orderBy('board_logs.id', 'desc')
                                                        ->paginate(100));


        default:
            return $msgRoute;
        };
    }

    private function rptCooker(string $iid = null)
    {
        $msgRoute = response()->json([
            'help' => [
                '[ GET ]   /api/orderReport/cooker/waiting'  => 'Todos os pedidos "aguardando" e "preparando"',
                '[ POST ]   /api/orderReport/cooker/waiting-client'  => 'Todos os pedidos, "aguardando" e "preparando" pelo client_id',
                '[ GET ]   /api/orderReport/cooker/ready'  => 'Todos os pedidos "pronto" e "entregue"',
                '[ POST ]   /api/orderReport/cooker/ready-client'  => 'Todos os pedidos, "pronto" e "entregue" pelo client_id',
            ],
        ]);

        if ((!$this->isCooker) && (!$this->isWaiter) && (!$this->isAdmin))
            return $this->msgNotAuthorized();

        if ((($iid == 'waiting-client') || ($iid == 'ready-client')) && (!$this->Request->has('client_id')))
            return $this->msgNotHasField('client_id');


        switch ($iid) {

            case 'help': return $msgRoute;

            case 'waiting': return response()->json($this->OrderItem->join('menus', 'menus.id','order_items.menu_id')
                                                                     ->join('board_logs', 'board_logs.id', 'order_items.board_log_id')
                                                                     ->join('users as waiter', 'waiter.id', 'board_logs.waiter_id')
                                                                     ->leftjoin('users as client', 'client.id', 'board_logs.client_id')
                                                                     ->whereIn('order_items.state', ['aguardando', 'preparando'])
                                                                     ->select($this->fields)
                                                                     ->orderBy('board_logs.id')
                                                                     ->paginate(100));

            case 'waiting-client': return response()->json($this->OrderItem->join('menus', 'menus.id','order_items.menu_id')
                                                                            ->join('board_logs', 'board_logs.id', 'order_items.board_log_id')
                                                                            ->join('users as waiter', 'waiter.id', 'board_logs.waiter_id')
                                                                            ->leftjoin('users as client', 'client.id', 'board_logs.client_id')
                                                                            ->where('client.id', $this->Request->get('client_id'))
                                                                            ->whereIn('order_items.state', ['aguardando', 'preparando'])
                                                                            ->select($this->fields)
                                                                            ->orderBy('board_logs.id', 'desc')
                                                                            ->paginate(100));


            case 'ready': return response()->json($this->OrderItem->join('menus', 'menus.id','order_items.menu_id')
                                                                  ->join('board_logs', 'board_logs.id', 'order_items.board_log_id')
                                                                  ->join('users as waiter', 'waiter.id', 'board_logs.waiter_id')
                                                                  ->leftjoin('users as client', 'client.id', 'board_logs.client_id')
                                                                  ->where('client.id', $this->Request->get('client.id'))
                                                                  ->whereIn('order_items.state', ['pronto', 'entregue'])
                                                                  ->select($this->fields)
                                                                  ->orderBy('board_logs.id')
                                                                  ->paginate(100));

            case 'ready-client': return response()->json($this->OrderItem->join('menus', 'menus.id','order_items.menu_id')
                                                                         ->join('board_logs', 'board_logs.id', 'order_items.board_log_id')
                                                                         ->join('users as waiter', 'waiter.id', 'board_logs.waiter_id')
                                                                         ->leftjoin('users as client', 'client.id', 'board_logs.client_id')
                                                                         ->where('client.id', $this->Request->get('client_id'))
                                                                         ->whereIn('order_items.state', ['pronto', 'entregue'])
                                                                         ->select($this->fields)
                                                                         ->orderBy('board_logs.id', 'desc')
                                                                         ->paginate(100));
        default:
            return $msgRoute;
        };
    }

    private function rptClient(string $iid = null)
    {
        $msgRoute = response()->json([
            'help' => [
                '[ GET ]   /api/orderReport/client/largeOrder'  => 'Apresenta todos pedidos de cliente ordenados do maior para o menor valor de pedido.',
                '[ GET ]   /api/orderReport/client/firstOrder'  => 'Apresenta todos pedidos ordenados do primeiro até o último',
                '[ GET ]   /api/orderReport/client/lastOrder'  => 'Apresenta todos pedidos de cliente ordenados do último até o primeiro.',
            ],
        ]);

        if (!$this->Request->has('client_id'))
            return $this->msgNotHasField('client_id');

            switch ($iid) {
                case 'help': return $msgRoute;

                case 'largeOrder': return response()->json(
                        $this->BoardLog->join('users as waiter', 'waiter.id', 'board_logs.waiter_id')
                                       ->join('users as client', 'client.id', 'board_logs.client_id')
                                       ->where('client.id', $this->Request->get('client_id'))
                                       ->select($this->fieldsBoardLog)
                                       ->orderBy('board_logs.total_order', 'desc')
                                       ->get());

                case 'firstOrder': return response()->json(
                                        $this->BoardLog->join('users as waiter', 'waiter.id', 'board_logs.waiter_id')
                                                    ->join('users as client', 'client.id', 'board_logs.client_id')
                                                    ->where('client.id', $this->Request->get('client_id'))
                                                    ->select($this->fieldsBoardLog)
                                                    ->orderBy('board_logs.id')
                                                    ->get());

                case 'lastOrder': return response()->json(
                                        $this->BoardLog->join('users as waiter', 'waiter.id', 'board_logs.waiter_id')
                                                    ->join('users as client', 'client.id', 'board_logs.client_id')
                                                    ->where('client.id', $this->Request->get('client_id'))
                                                    ->select($this->fieldsBoardLog)
                                                    ->orderBy('board_logs.id', 'desc')
                                                    ->get());
                default:
                    return $msgRoute;
            };
    }


    private function help()
    {
        return response()->json([
            'help' => [
                '[ GET ]   /api/orderReport/help'   => 'Informações sobre o point solicitado.',
                '[ GET ]   /api/orderReport'        => 'Apresenta todos relatórios referentes à pedidos do sistema',
                '[ GET ]   /api/orderReport/client/largeOrder'  => 'Apresenta todos pedidos de cliente ordenados do maior para o menor valor de pedido.',
                '[ GET ]   /api/orderReport/client/firstOrder'  => 'Apresenta todos pedidos ordenados do primeiro até o último',
                '[ GET ]   /api/orderReport/client/lastOrder'  => 'Apresenta todos pedidos de cliente ordenados do último até o primeiro.',
                '[ GET ]   /api/orderReport/cooker/waiting'  => 'Todos os pedidos "aguardando" e "preparando"',
                '[ POST ]  /api/orderReport/cooker/waiting-client'  => 'Todos os pedidos, "aguardando" e "preparando" pelo client_id',
                '[ GET ]   /api/orderReport/cooker/ready'  => 'Todos os pedidos "pronto" e "entregue"',
                '[ POST ]  /api/orderReport/cooker/ready-client'  => 'Todos os pedidos, "pronto" e "entregue" pelo client_id',
                '[ GET ]   /api/orderReport/waiter/all'  => 'Todos pedidos, quando logado Garçom, pedidos do garçom, caso Administrador pedidos de todos os garçons',
                '[ GET ]   /api/orderReport/waiter/progress'  => 'Pedidos em andamento do garçom logado ou caso de login Administrador, todos os pedidos em andamento de todos os garçons',
                '[ GET ]   /api/orderReport/waiter/closed'  => 'Pedidos encerrados do garçom logado ou caso de login Administrador, todos os pedidos encerrados de todos os garçons',
                '[ GET ]   /api/orderReport/waiter' => 'Visualizar pedidos referente ao garçom logado',
                '[ GET ]   /api/orderReport/cooker' => 'Visualizar andamento de pedidos encaminhados pra cozinha',
                '[ GET ]   /api/orderReport/admin' => 'Visualizar andamento de pedidos encaminhados pra cozinha',
            ],
        ]);
    }
}
