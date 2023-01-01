<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class MenuController extends Controller
{
    private $Menu;
    private $Request;
    private $loggedIn;
    private int $userId = 0;
    private bool $isAdmin = false;
    private bool $isClient = false;
    private bool $isCooker = false;
    private bool $isWaiter = false;

    private $fields = [
        'users.id',
        'users.name as username',
        'users.email as email',
        'users.password as password',
        'groups.name as groupname',
    ];

    public function __construct(Menu $menu, Request $request)
    {
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
        return response()->json(
            $this->Menu::all()
        );
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
            'name' => 'required|string',
            'description' =>'required|string',
            'price' => 'required|decimal:2',
        ];

        $validator = Validator::make($this->Request->all(), $rules);
        if ($validator->fails())
            return $this->msgMissingValidator($validator);

        $menu = new Menu;
        $menu->setMenu($this->Request);

        return $this->msgInclude($menu);
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
            default    : return $this->msgResourceNotExists();
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
        if (!$this->isAdmin)
            return $this->msgNotAuthorized();

        $rules = [
            'id' => 'required|integer',
        ];

        $validator = Validator::make($this->Request->all(), $rules);
        if ($validator->fails())
            return $this->msgMissingValidator($validator);

        $menu = $this->Menu->find($this->Request->id);
        if (!$menu)
            return $this->msgRecordNotFound();

        $menu->setMenu($this->Request);
        return $this->msgUpdated($menu);
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
            'id' => 'required|integer',
        ];

        $validator = Validator::make($this->Request->all(), $rules);
        if ($validator->fails())
            return $this->msgMissingValidator($validator);

        $menu = $this->Menu->find($this->Request->id);
        if (!$menu)
            return $this->msgRecordNotFound();

        try {
            $menu->delete();
            return $this->msgDeleted();
        } catch (Exception $e) {
            return $this->msgNotDeleted($e);
        }
    }

    private function help() {

        return response()->json([
            'help' => [
                '[ GET ]    /menu/help'   => 'Informações sobre o point solicitado.',
                '[ GET ]    /menu'        => 'Apresenta todos os cardápios do sistema',
                '[ POST ]   /menu -> Cadastrar novos cardápios' => [
                    '{name}' => 'Nome do cardápio',
                    '{description}' => 'Descrição detalhada do cardápio',
                    '{price}'  => 'valor',
                ],
                '[ PUT ]   /menu -> Alterar cardápios cadastrados' => [
                    '{id}' => 'id do cardápio à ser alterado',
                    'name' => 'Nome do cardápio',
                    'description' => 'Descrição detalhada do cardápio',
                    'price'  => 'valor',
                ],
                '[ DELETE ]   /menu -> Excluir cardápio cadastrados' => [
                    '{id}' => 'id do cardápio à ser excluído',
                ],
            ],
        ]);
    }
}
