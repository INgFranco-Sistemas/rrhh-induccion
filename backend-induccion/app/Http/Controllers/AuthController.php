<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use App\Models\Dependencia;

class AuthController extends Controller
{
public static function middleware(): array
    {
        return [
            // Apply 'auth:api' to all methods EXCEPT 'login' and 'register'
            'auth:api' => [
                'except' => ['login', 'register'],
            ],
            
            // You can add other middleware here if needed
            // 'throttle:60,1' => [
            //     'only' => ['login'],
            // ],
        ];
    }
    // (Opcional) Registro de usuario - puedes limitar luego solo a admin
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'in:admin,trabajador',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $validator->validated();
        $data['password'] = Hash::make($data['password']);
        $data['role'] = $data['role'] ?? 'trabajador';

        $user = User::create($data);

        return response()->json([
            'message' => 'Usuario registrado correctamente',
            'user'    => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        // $credentials = $request->only('email', 'password');

        // if (!$token = auth('api')->attempt($credentials)) {
        //     return response()->json(['message' => 'Credenciales inválidas'], 401);
        // }
        // return $this->respondWithToken($token);

        $validator = Validator::make($request->all(), [
                'adm_email' => 'required',
                'password' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }
            if (! $token = auth()->attempt($validator->validated())) {
                return response()->json(['error' => 'Usuario no autorizado'], 401);
            }
            return $this->respondWithToken($token);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken(string $token)
    {
        $user = auth()->user();
        $idoficina=auth()->user()->depe_id;
        $iddepe=Dependencia::where('iddependencia',$idoficina)->value('depe_depende');

        // 🔒 Obtener roles como colección
        $roles = $user->getRoleNames();

        // Normalizar rol principal
        $rolPrincipal = null;
        if ($roles->contains('admin_induccion')) {
            $rolPrincipal = 'admin';
        } elseif ($roles->contains('user_induccion')) {
            $rolPrincipal = 'trabajador';
        }


        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60, // segundos
            'user'         => auth()->user(),
            'sede' => $iddepe,
            'roles' => $user->getRoleNames(), // ['admin']
            'rol_principal'=> $rolPrincipal, // 🔑 rol normalizado
            'permissions' => $user->getAllPermissions()->pluck('name'), // ['edit articles']
        ]);
    }
}
