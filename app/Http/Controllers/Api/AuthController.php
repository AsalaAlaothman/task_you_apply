<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    use ApiTrait;
    protected function create(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), [
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'first_name' => ['required', 'string'],
                'last_name' => ['required', 'string'],
                'phone_number' => ['required', 'unique:users', 'regex:/(07)[0-9]{8}/'],
                'username' => ['required', 'string', 'unique:users', 'regex:/^((?!\@).)*$/'],
                'gender' => ['required', 'string', Rule::in(['male', 'female']),],
                'date_of_birth' => ['required', 'date']
            ]);
            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone_number' => $request->phone_number,
                'username' => $request->username,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Login The User
     * @param Request $request
     * @return User
     */
    public function login(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'credential' => 'required',
                    'password' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }


            if (str_contains($request->credential, '@')) {
                $credentials = ['email' => $request->get('credential'), 'password' => $request->get('password')];
                $user = User::where('email', $request->credential)->first();
            } elseif (is_numeric($request->credential)) {
                $credentials = ['phone_number' => $request->get('credential'), 'password' => $request->get('password')];
                $user = User::where('phone_number', $request->credential)->first();
            } else {
                $credentials = ['username' => $request->get('credential'), 'password' => $request->get('password')];
                $user = User::where('username', $request->credential)->first();
            }

            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }
            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function authUserInfo()
    {
        $id = Auth::id();
        $user = User::select('first_name', 'last_name', 'gender', 'date_of_birth')->findOrFail($id);
        return $user;
    }

    public function UserInfo($username)
    {
        $user = User::select('first_name', 'last_name', 'gender', 'date_of_birth')->where('username', $username)->first();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => "this User $username Dose not exist"
            ], 200);
        }
        return response()->json([
            'status' => true,
            'message' => $user,
        ], 200);
    }
}
