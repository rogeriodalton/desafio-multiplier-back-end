<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    private $User;
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

    public function __construct(User $user, Request $request)
    {
        $this->User = $user;
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
            $this->User->join('user_groups', 'user_groups.user_id', 'users.id')
                       ->join('groups', 'groups.id','user_groups.group_id')
                       ->select($this->fields)
                       ->get()
                       ->toArray()
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
        $rules = [
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'cpf' => 'required|cpf',
            'password' => 'required|string|confirmed'
        ];

        $validator = Validator::make($this->Request->all(), $rules);
        if ($validator->fails())
            return $this->msgMissingValidator($validator);

        if (($this->isAdmin == false) || (($this->Request->id != $this->userId) && ($this->isAdmin == false)))
            return $this->msgNotAuthorized();

        $cpfUser = User::where('cpf', $this->Request->cpf)->select('id','name')->first();
        if ($cpfUser)
            return $this->msgDuplicatedField('cpf', $cpfUser);

        $user = $this->User->find($this->Request->id);
        if ($user) {
            $user->setUser($this->Request);
            return $this->msgUpdated($user);
        }
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
        $rules = [
            'id' => 'required|integer',
        ];

        $validator = Validator::make($this->Request->all(), $rules);
        if ($validator->fails())
            return $this->msgMissingValidator($validator);

        if (($this->isAdmin == false) || (($this->Request->id != $this->userId) && ($this->isAdmin == false)))
            return $this->msgNotAuthorized();

        $user = $this->User->find($this->Request->id);
        if ($user) {
            $user->setUser($this->Request);
            return $this->msgUpdated($user);
        }
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

        $user = $this->User->find($this->Request->id);

        if (!$user)
            return $this->msgRecordNotFound();

        $userMsg = $user->setUserActive(false);

        return $this->msgGeneric($userMsg, 'Registro de usuário desativado com sucesso.');

    }

    private function help()
    {
        return response()->json([
            'help' => [
                '[ GET ]    /user/help'   => 'Informações sobre o point solicitado.',
                '[ GET ]    /user'        => 'Apresenta todos usuários do sistema',
                '[ POST ]   /user -> Movos usuários para o sistema' => [
                    '{name}' => 'Nome do usuário',
                    '{cpf}' => 'CPF do usuário',
                    '{email}'  => 'Email do usuário utilizado para login no sistema',
                    '{password}' => 'Senha de Usuário',
                    '{password_confirmation}' => 'Confirmação da senha informada',
                ],
                '[ PUT ]   /user -> Alterar usuários do sistema' => [
                    '{id}' => 'id do usuário para localizar o registro à ser alterado',
                    'name' => 'Nome do usuário',
                    'cpf' => 'CPF do usuário',
                    'email'  => 'Email do usuário utilizado para login no sistema',
                    'password' => 'Senha de Usuário',
                ],
                '[ DELETE ]   /user -> Desativar acesso à usuário' => [
                    '{id}' => 'id do usuário para localizar o registro à ser alterado',
                  ],

            ],
        ]);
    }
}
