<?php

namespace App\Http\Controllers;

use App\Models\BoardLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class OrderController extends Controller
{
    private BoardLog $BoardLog;
    private Request $Request;
    private $loggedIn;
    private int $userId = 0;
    private bool $isAdmin = false;
    private bool $isClient = false;
    private bool $isCooker = false;
    private bool $isWaiter = false;

    private $fields = [
        'board_logs.board_id',
        'board_logs.id as id',
        'menus.name as name',
        'menus.price as price',
        'order_items.amount as amount',
        'order_items.state as state',
        'board_logs.created_at as boardLog_created_at',
        'board_logs.updated_at as boardLog_updated_at',
        'order_items.created_at as order_items_created_at',
        'order_items.updated_at as order_items_updated_at',
    ];

    public function __construct(BoardLog $boardLog, Request $request)
    {
        $this->BoardLog = $boardLog;
        $this->Request = $request;
        $this->loggedIn = auth('sanctum')->user();
        $this->userId = auth('sanctum')->user()->id;

        if ($this->loggedIn) {
            $this->isAdmin = $this->getPermission($this->loggedIn,  1); //administrador
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
            $this->BoardLog->join('order_items', 'order_items.board_log_id', 'board_logs.id')
                           ->join('menus', 'menus.id', 'order_items.menu_id')
                           ->select($this->fields)
                           ->orderBy('order_items.created_at', 'desc')
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
        if (!$this->isWaiter)
            return $this->msgNotAuthorized();

        $rules = [
            'board_id' => 'required|integer|exists:App\Models\Board,id',
        ];

        $validator = Validator::make($this->Request->all(), $rules);
        if ($validator->fails())
            return $this->msgMissingValidator($validator);

        $dataFields = [
            'waiter_id' => $this->userId,
            'state'  => 'aberto',
            'client_id' => 0
        ];

        $boardLog = new BoardLog;
        $boardLog->setBoardLog($this->Request, $dataFields);
        return $this->msgInclude($boardLog);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(string $id = null)
    {
        switch ($id) {
            case 'help': return $this->help();
            default:
                return $this->msgResourceNotExists();
        }
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
        if (!$this->isWaiter)
            return $this->msgNotAuthorized();

        $rules = [
            'order_id' => 'required|integer|exists:App\Models\BoardLog,id',
        ];

        $validator = Validator::make($this->Request->all(), $rules);
        if ($validator->fails())
            return $this->msgMissingValidator($validator);

        $dataFields = [
            'waiter_id' => $this->userId,
            'state'  => 'aberto',
            'client_id' => 0
        ];

        $boardLog = $this->boardLog->find($this->Request->order_id);
        $boardLog->setBoardLog($this->Request, $dataFields);

        return $this->msgUpdated($boardLog);
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
        ];

        $validator = Validator::make($this->Request->all(), $rules);
        if ($validator->fails())
            return $this->msgMissingValidator($validator);

        $boardLog = $this->BoardLog->find($this->Request->order_id);
        if (!$boardLog)
            return $this->msgRecordNotFound();

        try {
            $boardLog->delete();
            return $this->msgDeleted();
        } catch (Exception $e) {
            return $this->msgNotDeleted($e);
        }
    }

    private function help()
    {
        return response()->json([
            'help' => [
                '[ GET ]    /order/help'   => 'Informações sobre o point solicitado.',
                '[ GET ]    /order'        => 'Apresenta todos pedidos do sistema para usuário grupo "administrador", usuário Grupo "garçom" somente seus pedidos e usuário grupo "cozinheiro" pedidos em "preparo" ou "aguardando". ',
                '[ POST ]   /order -> novos pedidos para o sistema' => [
                    '{board_id}' => 'Número da mesa',
                    '{waiter_id}' => 'Id do garçom',
                ],
                '[ PUT ]   /order -> Alterar pedidos do sistema' => [
                    '{order_id}' => 'id do pedido para localizar o registro à ser alterado',
                ],
                '[ DELETE ]   /order -> Deletar pedidos à usuário' => [
                    '{order_id}' => 'id do pedido para localizar o registro à ser excluído',
                ],
            ],
        ]);
    }
}
