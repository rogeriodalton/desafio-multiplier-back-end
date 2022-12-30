<?php

namespace App\Http\Controllers;

use App\Models\Board;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BoardController extends Controller
{
    private $Board;
    private $Request;
    private $loggedIn;
    private int $userId = 0;
    private bool $isAdmin = false;
    private bool $isClient = false;
    private bool $isCooker = false;
    private bool $isWaiter = false;

    public function __construct(Board $board, Request $request)
    {
        $this->Board = $board;
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
        if (($this->isAdmin) || ($this->isCooker) || ($this->isWaiter))
            return $this->Board->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        if (!$this->isAdmin)
            return $this->msgNotAuthorized();

        $rules = [
            'numBoards' => 'required|integer',
        ];

        $validator = Validator::make($this->Request->all(), $rules);
        if ($validator->fails())
            return $this->msgMissingValidator($validator);

        for ($i = 0; $i < $this->Request->numBoards; $i++) {
            $board = new Board;
            $board->addBoards();
        }
        return $this->msgInclude($board);
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
           case 'help': return  $this->help();
           default    : return $this->msgResourceNotExists();
        }
    }

    private function help()
    {
        return response()->json([
            'help' => [
                '[ GET ]    /board/help'   => 'Informações sobre o point solicitado.',
                '[ GET ]    /board'        => 'apresenta as mesas existentes',
                '[ POST ]   /board'   => [
                    '{numBoards}' => 'Número de mesas à acrescentar',
                ],
            ],
        ]);

    }
}
