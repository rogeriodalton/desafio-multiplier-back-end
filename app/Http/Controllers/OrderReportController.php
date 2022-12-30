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
    public function show(string $id = null, string $iid = null, string $iiid = null)
    {
        $msgRoute = '/api/orderReport/?   (help  or  client  or  admin  or  waiter  or  cooker)';

        switch ($id) {
            case 'help'  : return $this->help();
            case 'client': return $this->rptClient($iid, $iiid);
            case 'admin' : return $this->rptAdmin($iid, $iiid);
            case 'waiter': return $this->rptWaiter($iid, $iiid);
            case 'cooker': return $this->rptCooker($iid, $iiid);
        default:
            return $this->msgGeneric($msgRoute);
        };

    }

    private function rptAdmin(string $iid = null)
    {
        $msgRoute = '/api/orderReport/admin/?   (day-week-year  or  month-year  or  year  or  board  or  client)';

        if (!$this->isAdmin)
            return $this->msgNotAuthorized();

        if ($iid == null)
            return $this->msgGeneric($msgRoute);


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
                return $this->msgGeneric($msgRoute);
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
        $msgRoute = '/api/orderReport/waiter/?   (all  or  firstOrder  or  firstOrder)';

        if ((!$this->isWaiter) && (!$this->isAdmin))
            return $this->msgNotAuthorized();

        if ($iid == null)
            return $this->msgGeneric($msgRoute);


        switch ($iid) {
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

        default:
            return $this->msgGeneric($msgRoute);
        };
    }

    private function rptCooker(string $iid = null)
    {
        $msgRoute = '/api/orderReport/cooker/?   (waiting   or   ready   or   waiting-client   or   ready-client)';

        if ((!$this->isCooker) && (!$this->isWaiter) && (!$this->isAdmin))
            return $this->msgNotAuthorized();

        if ($iid == null)
            return $this->msgGeneric($msgRoute);

        if ((($iid == 'waiting-client') || ($iid == 'ready-client')) && (!$this->Request->has('client_id')))
            return $this->msgNotHasField('client_id');


        switch ($iid) {
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
            return $this->msgGeneric($msgRoute);
        };
    }


    private function rptClient(string $iid = null, string $iiid = null)
    {
        $msgRoute = '/api/orderReport/client/?   (largeOrder  or  firstOrder  or  firstOrder)';

        if ($iid == null)
            return $this->msgGeneric($msgRoute);

        if (!$this->Request->has('client_id'))
            return $this->msgNotHasField('client_id');

            switch ($iid) {
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
                    return $this->msgGeneric($msgRoute);
            };
    }


    private function help()
    {
        return response()->json([
            'help' => [
                '[ GET ]   /orderReport/help'   => 'Informações sobre o point solicitado.',
                '[ GET ]   /orderReport'        => 'Apresenta todos relatórios referentes à pedidos do sistema',
                '[ GET ]   /orderReport/waiter' => 'Visualizar pedidos referente ao garçom logado',
                '[ GET ]   /orderReport/cooker' => 'Visualizar andamento de pedidos encaminhados pra cozinha',
                '[ GET ]   /orderReport/admin' => 'Visualizar andamento de pedidos encaminhados pra cozinha',
            ],
        ]);
    }
}
