<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User\User;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;
use App\Services\Api\LoginService;
use App\Http\Controllers\Controller;
use App\Models\User\UserDeviceToken;
use Illuminate\Support\Facades\Auth;
use App\Transformers\Login\LoginTransformer;
use App\Services\Api\PushNotificationService;
use App\Transformers\Login\LoginPatientTransformer;
use App\Models\LoginLogs;
use App\Services\Api\EncryptService;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    /**
     * @var \Tymon\JWTAuth\JWTAuth
     */
    protected $jwt;

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    // Login
    public function login(Request $request)
    {
        try {            
            if (strlen($request->input('password')) > 100) {
                $password = (new EncryptService)->decryptParameter($request->input('password'));
            } else {
                $password = $request->input('password');
            }
            if ($token = auth()->attempt(['email' => $request->input('email'), 'password' => $password])) {
                $deviceToken = $request->deviceToken;
                $deviceType = $request->deviceType;
                if ($request->deviceType == 'ios') {
                   $pushNotification = new PushNotificationService();
                   $deviceToken = $pushNotification->ios_token($deviceToken);
                }
                User::where('deviceToken', $deviceToken)->update([
                    "deviceToken" => "",
                    "deviceType" => ""
                ]);
                User::where('id', Auth::id())->update([
                    "deviceToken" => $deviceToken,
                    "deviceType" => $deviceType,
                    "updatedBy" => Auth::id()
                ]);
                $inputUpdate = ['deletedBy' => Auth::id(), 'isDelete' => 1, 'isActive' => 0];
                UserDeviceToken::where('deviceToken', $deviceToken)->update($inputUpdate);
                UserDeviceToken::where('deviceToken', $deviceToken)->delete();
                $input = [
                    'udid' => Str::uuid()->toString(),
                    "deviceToken" => $deviceToken,
                    "userId" => Auth::id(),
                    "deviceType" => $deviceType,
                    "createdBy" => Auth::id()
                ];
                UserDeviceToken::create($input);
                $user = User::where('email', $request->email)->with('roles', 'staff', 'patient')->firstOrFail();
                if ($user->isActive == 1) {
                    $log['login_id'] = $request->input('email');
                    $log['platform'] = $request->deviceType;
                    $log['browser'] = '';
                    $log['ip_address'] = $request->ip();
                    $log['type'] = 'Login';
                    $log['date'] = date('Y-m-d H:i:s', time());
                    $log['attempt'] = 'success';
                    $log['message'] = 'Login Success';
                    $log['udid'] = Str::uuid()->toString();
                    LoginLogs::create($log);
                    
                    $data = array(
                        'token' => $token,
                        'expiresIn' => auth()->factory()->getTTL() * 60 * 100,
                        'user' => $user
                    );
                    // print_r($data); die;
                    if ($user->roleId == 4) {
                        return fractal()->item($data)->transformWith(new LoginPatientTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();
                    } else {
                        return fractal()->item($data)->transformWith(new LoginTransformer)->serializeWith(new \Spatie\Fractalistic\ArraySerializer())->toArray();
                    }
                } else {
                    $log['udid'] = Str::uuid()->toString();
                    $log['login_id'] = $request->input('email');
                    $log['platform'] = $request->deviceType;
                    $log['browser'] = '';
                    $log['ip_address'] = $request->ip();
                    $log['type'] = 'Login';
                    $log['date'] = date('Y-m-d H:i:s', time());
                    $log['attempt'] = 'failed';
                    $log['message'] = trans('auth.inactive');
                    LoginLogs::create($log);
                    return response()->json(['message' => trans('auth.inactive')], 401);
                }
            } else {
               
                $log['udid'] = Str::uuid()->toString();
                $log['login_id'] = $request->input('email');
                $log['platform'] = $request->deviceType;
                $log['browser'] = '';
                $log['ip_address'] = $request->ip();
                $log['type'] = 'Login';
                $log['date'] = date('Y-m-d H:i:s', time());
                $log['attempt'] = 'failed';
                $log['message'] = trans('auth.failed');
                LoginLogs::create($log);
                return response()->json(['message' => trans('auth.failed')], 422);
            }
        } catch (\Exception $e) {
            echo $e->getMessage().''.$e->getFile().''.$e->getLine();
            //throw new \RuntimeException($e);
        }
    }

    // Logout
    public function logout(Request $request)
    {
        try {
            User::where('id', Auth::id())->update([
                "deviceToken" => "",
                "deviceType" => "",
                "updatedBy" => Auth::id()
            ]);
            $inputUpdate = ['deletedBy' => Auth::id(), 'isDelete' => 1, 'isActive' => 0];
            UserDeviceToken::where('userId', Auth::id())->update($inputUpdate);
            UserDeviceToken::where('userId', Auth::id())->delete();

            $log['login_id'] = Auth::user()->email;
            $log['platform'] = '';
            $log['browser'] = '';
            $log['ip_address'] = $request->ip();
            $log['type'] = 'Log Out';
            $log['date'] = date('Y-m-d H:i:s', time());
            LoginLogs::create($log);
            return (new LoginService)->logout($request);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Referesh Token
    public function refreshToken()
    {
        try {
            return $this->createNewToken(auth()->refresh());
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // New Token
    protected function createNewToken($token)
    {
        try {
            return response()->json([
                'token' => $token,
                'expiresIn' => auth()->factory()->getTTL() * 60 * 100,
            ]);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }
 
}
