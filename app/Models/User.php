<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'cpf',
        'password',
        'active'
    ];

    protected $casts = [
		'id' => 'integer',
        'name' => 'string',
        'email' =>'string',
        'email_verified_at',
        'cpf' =>'string',
        'password' =>'string',
        'active' => 'boolean',
	];

    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
    ];

public function setUser(Request $request)
{
    if ($request->has('name'))
        $this->attributes['name'] = $request->name;

    if ($request->has('email'))
        $this->attributes['email'] = $request->email;

    if ($request->has('cpf'))
        $this->attributes['cpf'] = $request->cpf;

    if ($request->has('password'))
        $this->attributes['password'] = Hash::make($request->password);

    if ($request->has('active'))
        $this->attributes['active'] = $request->active;

    $this->save();
    return $this;
}

public function setNewUser(Request $request)
{
    if ($request->has('name'))
        $this->attributes['name'] = $request->name;

    if ($request->has('email'))
        $this->attributes['email'] = $request->email;

    if ($request->has('cpf'))
        $this->attributes['cpf'] = $request->cpf;

    if ($request->has('password'))
        $this->attributes['password'] = Hash::make($request->password);

    $this->attributes['active'] = true;

    $this->save();
    return $this;
}

public function setUserActive(bool $isActive)
{
    $this->attributes['active'] = $isActive;
    $this->save();
    return [
        'id' => $this->attributes['id'],
        'name' => $this->attributes['name'],
        'active' => $this->attributes['active'],
    ];
}

}
