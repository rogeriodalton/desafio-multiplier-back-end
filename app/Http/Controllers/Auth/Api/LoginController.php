<?php

namespace App\Http\Controllers\Auth\Api;

use Exception;
use Throwable;
use App\Models\User;

use App\Models\UserGroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    private Request $Request;
    private bool $isAdmin  = false;
    private bool $isClient = false;
    private bool $isCooker = false;
    private bool $isWaiter = false;

    public function __construct(Request $request)
    {
        $this->Request = $request;
    }

    /**
     * Registrar um novo usuário
     */

    public function register()
    {
        $rules = [
            'name' => 'required|string',
            //'email' => 'required|string|unique:users,email',
            'cpf' => 'required|cpf',
            //'password' => 'required|string|confirmed'
        ];

        $validator = Validator::make($this->Request->all(), $rules);
        if ($validator->fails())
            return $this->msgMissingValidator($validator);

        $cpfUser = User::where('cpf', $this->Request->cpf)->select('id','name')->first();
        if ($cpfUser)
            return $this->msgDuplicatedField('cpf', $cpfUser);

        $user = new User;
        $user->setNewUser($this->Request);

        $userGroup =  new UserGroup;
        $userGroup->setUserGroups([
            'user_id' => $user->id,
            'group_id' => 2 // cliente
        ]);

        //definir grupo cliente

        if (($this->Request->has('email')) && ($this->Request->has('password')))
            return response()->json([
                'token' => $user->createToken($this->Request->email)->plainTextToken
            ], 201);
        else
            return $this->msgInclude($user);
    }

     /**
      * Login do usuário
      */
    public function login()
    {
        $rules = [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];

        $validator = Validator::make($this->Request->all(), $rules);
        if ($validator->fails())
            return $this->msgMissingValidator($validator);

        $this->isAdmin = $this->getPermissionEmail($this->Request->email,  1); //administrador
        $this->isClient = $this->getPermissionEmail($this->Request->email, 2); //cliente
        $this->isCooker = $this->getPermissionEmail($this->Request->email, 3); //cozinheiro
        $this->isWaiter = $this->getPermissionEmail($this->Request->email, 4); //garçom


        if (($this->isClient == true) && ($this->isAdmin == false) && ($this->isCooker == false) && ($this->isWaiter == false))
            return $this->msgNotAuthorized();

        if (!Auth::attempt($this->Request->only('email', 'password')))
            return $this->msgNotAuthorized();

        $user = User::where('email', $this->Request->email)->where('active', true)->first();

        if (!$user)
            return $this->msgNotAuthorized();

        if (!$user || !Hash::check($this->Request->password, $user->password))
            return $this->msgNotAuthorized();

        $token = $user->createToken($this->Request->email)->plainTextToken;

        return response()->json([
            'token' => $token
        ], 201);
    }

    /**
     * Logout do usuário
     */
    public function logout()
    {
        try {
            auth('sanctum')->user()->tokens()->delete();
        } catch (Throwable $e) {
        } finally {
            return response()->json([
                'message' => "Logout efetuado com sucesso.",
            ], 201);
        }
    }

    public function me()
    {
        try {
            auth('sanctum')->user()->tokens()->delete();
                return response()->json([
                    'logged in' => [
                        'name' => auth('sanctum')->user()->name,
                        'email' => auth('sanctum')->user()->email
                    ]
                ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'message' => "nenhum usuário logado.",
            ], 201);
        }
    }

    public function registerHelp()
    {
        return response()->json([
            'help' => [
                '[ GET ]    /register/help'   => 'Informações sobre o point solicitado.',
                '[ GET ]    /register'        => 'registrar usuário do sistema',
                '[ POST ]   /register'   => [
                  '{name}' => 'Nome do usuário',
                  '{cpf}'  => 'Número de Cadastro de Pessoa física do Usuário',
                  'email'  => 'Email do usuário utilizado para login no sistema',
                  'password' => 'Senha de Usuário',
                ],
            ],
        ]);
    }

    public function loginHelp()
    {
        return response()->json([
            'help' => [
                '[ GET ]    /login/help'   => 'Informações sobre o point solicitado.',
                '[ GET ]    /login'        => 'Informações sobre o point solicitado.',
                '[ POST ]   /login -> logar no sistema (não permitido para CLIENTES)' => [
                  '{email}'  => 'Email do usuário utilizado para login no sistema',
                  '{password}' => 'Senha de Usuário',
                ],
            ],
        ]);
    }
}
