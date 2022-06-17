<?php

namespace App\Http\Controllers;

use Auth;
use Validator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class JWTController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }

    /**
     * Register user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {       
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $path_img = $request->file("photo_ktp");
        $dt = array();
        if (!empty($path_img)) {
            $_tgl = date('YmdHi');
            $randomletter = substr(str_shuffle("KOPERASIkoperasi"), 0, 8);
            $nama_file = base64_encode($_tgl . "" . $randomletter);
            $fileSize = $path_img->getSize();
            $extension = $path_img->getClientOriginalExtension();
            $imageName = $nama_file . '.' . $extension;
            $tujuan_upload = 'uploads/photo_ktp';

            $_extension = array('png', 'jpg', 'jpeg');
            if ($fileSize > 2099200) { // satuan bytes
                $result = array(
                    'err_code'  => '07',
                    'err_msg'   => 'file size over 2048',
                    'data'      => $fileSize
                );
                return response($result);
                return false;
            }
            if (!in_array($extension, $_extension)) {
                $result = array(
                    'err_code'  => '07',
                    'err_msg'   => 'file extension not valid',
                    'data'      => null
                );
                return response($result);
                return false;
            }
            $path_img->move($tujuan_upload, $imageName);
            $imageName = env('PUBLIC_URL') . '/' . $tujuan_upload . '/' . $imageName;
            $dt += array('foto_ktp' => $imageName);
        }
		$user_create = auth()->user();
		
        $dt += array(
            'name' => $request->name,
            'email' => $request->email,
            'nik' => $request->nik,
            'phone' => $request->phone,
            'type' => $request->type,
            'id_agen' => $request->type == 3 ? $user_create->id : '',
            'created_by' => $user_create->id,
            'password' => Hash::make($request->password)
        );
        $user = User::create($dt);
        $result = array(
            'err_code'      => '00',
            'err_msg'          => 'ok',
            'data'          => $user
        );
        return response($result);
    }

    /**
     * login user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$token = auth()->attempt($validator->validated(),['exp' => Carbon::now()->addHour(3)->timestamp])) {
            $result = array(
                'err_code'      => '02',
                'err_msg'       => 'Unauthorized',
                'data'          => null
            );
            return response()->json($result, 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Logout user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'User successfully logged out.']);
    }

    /**
     * Refresh token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get user profile.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        $result = array(
            'err_code'      => '00',
            'err_msg'       => 'ok',
            'data'          => auth()->user()
        );
        return response($result);
        // return response()->json(auth()->user());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'err_code'      => '00',
            'err_msg'          => 'ok',
            'data'            => array('access_token' => $token)

        ]);
    }
}