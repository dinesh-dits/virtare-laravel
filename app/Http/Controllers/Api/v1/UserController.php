<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Services\Api\UserService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Password\CurrentPasswordRequest;

class UserController extends Controller
{

  // Get User Profile
  public function userProfile(Request $request)
  {
    return (new UserService)->userProfile($request);
  }

  // Update User Profile 
  public function profile(Request $request)
  {
    return (new UserService)->profile($request);
  }

  // List User
  public function listUser(Request $request, $id)
  {
    return (new UserService)->userList($request, $id);
  }

  // Change Password
  public function changePassword(CurrentPasswordRequest $request)
  {
    return (new UserService)->passwordChange($request);
  }

  // First Login
  public function firstLogin(Request $request)
  {
    return (new UserService)->loginFirst($request);
  }

  // Forget Password
  public function forgotPassword(Request $request)
  {
    return (new UserService)->forgotPassword($request);
  }

  // New Password
  public function newPassword(Request $request)
  {
    return (new UserService)->newPassword($request);
  }

  // Forget Password Code Verify
  public function forgotPasswordCodeVerify(Request $request)
  {
    return (new UserService)->forgotPasswordCodeVerify($request);
  }

  // Test Mail
  public function testMail(Request $request)
  {
    return (new UserService)->testMail($request);
  }

  // User Setting
  public function userSetting(Request $request)
  {
    return (new UserService)->userSetting($request);
  }

  // List User Setting
  public function userSettingList(Request $request)
  {
    return (new UserService)->userSettingList($request);
  }

  public function getMail(Request $request)
  {
    return (new UserService)->getMail($request);
  }
}
