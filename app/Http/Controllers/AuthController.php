<?php

namespace App\Http\Controllers;

use App\Models\MobileOtp;
use App\Models\TemporaryUser;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\CommonFunction;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    use CommonFunction;

    protected mixed $error_message, $validator_error_code, $backend_error_code, $success_status_code, $controller_name;


    public function __construct()
    {
        $this->error_message = config('constants.error_responses.message');
        $this->validator_error_code = config('constants.error_responses.validator_error_code');
        $this->backend_error_code = config('constants.error_responses.backend_error_code');
        $this->success_status_code = config('constants.error_responses.success_status_code');
        $this->controller_name = "App\Http\Controllers\AuthController";
    }

    public function registerOld(Request $request): \Illuminate\Http\JsonResponse
    {
        $function_name = "register";
        $request_all = $request->all();
        try {

            $validator_rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'mobile_no' => 'required|string|unique:users,mobile_no|size:10',
                'password' => 'required|string|min:6|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ];

            $validator_messages = [
                'name.required' => 'Name is required',
                'email.required' => 'Email id is required',
                'email.email' => 'Invalid email format',
                'email.unique' => 'Email is already taken',
                'mobile_no.required' => 'Mobile number is required',
                'mobile_no.string' => 'Invalid mobile number format',
                'mobile_no.unique' => 'Mobile number is already taken',
                'mobile_no.max' => 'Mobile number should not exceed 10 characters',
                'password.required' => 'Password is required',
                'password.string' => 'Invalid password format',
                'password.min' => 'Password should be at least 6 characters',
                'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one numeric digit',
            ];

            $validator = Validator::make($request_all, $validator_rules, $validator_messages);
            if ($validator->fails()) {
                $firstErrorMessage = $validator->errors()->first();
                logger()->error("$this->controller_name:$function_name: Validation failed - $firstErrorMessage", ['request' => $request_all]);
                return $this->sendError($this->validator_error_code, "$firstErrorMessage");
            }

            $check_otp = MobileOtp::where('mobile_no', $request_all['mobile_no'])->whereNull('mobile_otp_verified_at')->first();
            if ($check_otp) {
                $check_otp->delete();
                TemporaryUser::where('mobile_no', $request_all['mobile_no'])->delete();
            }

            $temporary_user = TemporaryUser::create([
                'name' => $request_all['name'],
                'email' => $request_all['email'],
                'mobile_no' => $request_all['mobile_no'],
                'dob' => $request_all['dob'],
                'password' => encrypt($request_all['password']),
            ]);

            $otp = rand(100000, 999999);

            $mobileOtp = MobileOtp::create([
                'user_id' => $temporary_user->id,
                'mobile_no' => $temporary_user->mobile_no,
                'otp' => $otp,
                'mobile_otp_expire_at' => now()->addMinutes(2),
                'mobile_otp_verified_at' => null,
            ]);

            $user['name'] = $temporary_user->name;
            $user['mobile_no'] = $temporary_user->mobile_no;
            $user['email'] = $temporary_user->email;

            $success = ['user' => $user, 'mobile_otp' => $otp];

            return $this->sendResponse($this->success_status_code, "User register successfully", $success);

        } catch (\Exception $e) {
            logError($this->controller_name, $function_name, $e);
            return $this->sendError($this->backend_error_code, "$this->error_message");
        }

    }

    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        $function_name = "register";
        $request_all = $request->all();
        try {

            $validator_rules = [
                'mobile_no' => 'required|string|unique:users,mobile_no|size:10',
                'password' => 'required|string|min:6|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ];

            $validator_messages = [
                'mobile_no.required' => 'Mobile number is required',
                'mobile_no.string' => 'Invalid mobile number format',
                'mobile_no.unique' => 'Mobile number is already taken',
                'mobile_no.max' => 'Mobile number should not exceed 10 characters',
                'password.required' => 'Password is required',
                'password.string' => 'Invalid password format',
                'password.min' => 'Password should be at least 6 characters',
                'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one numeric digit',
            ];

            $validator = Validator::make($request_all, $validator_rules, $validator_messages);
            if ($validator->fails()) {
                $firstErrorMessage = $validator->errors()->first();
                logger()->error("$this->controller_name:$function_name: Validation failed - $firstErrorMessage", ['request' => $request_all]);
                return $this->sendError($this->validator_error_code, "$firstErrorMessage");
            }

            $check_otp = MobileOtp::where('mobile_no', $request_all['mobile_no'])->whereNull('mobile_otp_verified_at')->first();
            if ($check_otp) {
                $check_otp->delete();
                TemporaryUser::where('mobile_no', $request_all['mobile_no'])->delete();
            }

            $temporary_user = TemporaryUser::create([
                'mobile_no' => $request_all['mobile_no'],
                'password' => encrypt($request_all['password']),
            ]);

            $otp = rand(100000, 999999);

            $mobileOtp = MobileOtp::create([
                'user_id' => $temporary_user->id,
                'mobile_no' => $temporary_user->mobile_no,
                'otp' => $otp,
                'mobile_otp_expire_at' => now()->addMinutes(2),
                'mobile_otp_verified_at' => null,
            ]);

            $user['mobile_no'] = $temporary_user->mobile_no;

            $success = ['user' => $user, 'mobile_otp' => $otp];

            return $this->sendResponse($this->success_status_code, "User register successfully", $success);

        } catch (\Exception $e) {
            logError($this->controller_name, $function_name, $e);
            return $this->sendError($this->backend_error_code, "$this->error_message");
        }

    }

    public function sendOtp(Request $request): \Illuminate\Http\JsonResponse
    {
        $function_name = "sendOtp";
        $request_all = $request->all();
        try {
            $validator_rules = [
                'mobile_no' => 'required|string|size:10|exists:mobile_otp,mobile_no',
                'is_forgot' => 'required',
            ];

            $validator_messages = [
                'mobile_no.exists' => 'Invalid mobile number',
                'mobile_no.required' => 'Mobile number is required',
                'mobile_no.string' => 'Invalid mobile number format',
                'mobile_no.size' => 'Mobile number should be 10 characters',
                'is_forgot.required' => 'Is forgot is required',
            ];

            $validator = Validator::make($request_all, $validator_rules, $validator_messages);
            if ($validator->fails()) {
                $firstErrorMessage = $validator->errors()->first();
                logger()->error("$this->controller_name:$function_name: Validation failed - $firstErrorMessage", ['request' => $request_all]);
                return $this->sendError($this->validator_error_code, "$firstErrorMessage");
            }

            $verified_otp = MobileOtp::where('mobile_no', $request_all['mobile_no'])->first();

            $otp = rand(100000, 999999);

            $verified_otp->update([
                'otp' => $otp,
                'mobile_otp_expire_at' => now()->addMinutes(2),
                'mobile_otp_verified_at' => $request_all['is_forgot'] == 1 ? now() : null,
            ]);

            return $this->sendResponse($this->success_status_code, 'OTP send successfully', $otp);

        } catch (\Exception $e) {
            logError($this->controller_name, $function_name, $e);
            return $this->sendError($this->backend_error_code, "$this->error_message");
        }

    }

    public function mobileOtpVerifiedRegister(Request $request): \Illuminate\Http\JsonResponse
    {
        $function_name = "mobileOtpVerifiedRegister";
        $request_all = $request->all();
        try {
            $validator_rules = [
                'mobile_no' => 'required|string|size:10|exists:mobile_otp,mobile_no',
                'otp' => 'required|numeric|digits:6|exists:mobile_otp,otp',
            ];

            $validator_messages = [
                'mobile_no.exists' => 'Invalid mobile number',
                'mobile_no.required' => 'Mobile number is required',
                'mobile_no.string' => 'Invalid mobile number format',
                'mobile_no.size' => 'Mobile number should be 10 characters',
                'otp.required' => 'OTP is required',
                'otp.numeric' => 'OTP must be a number',
                'otp.digits' => 'OTP must be 6 digits',
                'otp.exists' => 'Invalid OTP',
            ];

            $validator = Validator::make($request_all, $validator_rules, $validator_messages);
            if ($validator->fails()) {
                $firstErrorMessage = $validator->errors()->first();
                logger()->error("$this->controller_name:$function_name: Validation failed - $firstErrorMessage", ['request' => $request_all]);
                return $this->sendError($this->validator_error_code, "$firstErrorMessage");
            }

            $verified_otp = MobileOtp::where('mobile_no', $request_all['mobile_no'])->first();

            if ($verified_otp->mobile_otp_expire_at <= now()) {
                return $this->sendError($this->backend_error_code, "OTP expired");
            } elseif (!$verified_otp || $verified_otp->otp != $request_all['otp']) {
                return $this->sendError($this->backend_error_code, "Invalid OTP");
            } elseif ($verified_otp->mobile_otp_verified_at) {
                return $this->sendError($this->backend_error_code, "Your mobile number is already verified");
            }


            $verified_otp->update(['mobile_otp_verified_at' => now()]);

            if ($verified_otp->mobile_otp_verified_at !== null) {
                $temp_user = TemporaryUser::where('mobile_no', $request_all['mobile_no'])->first();
                $password = decrypt($temp_user->password);
                $verified_user = User::create([
                    'name' => $temp_user->name,
                    'email' => $temp_user->email,
                    'mobile_no' => $temp_user->mobile_no,
                    'dob' => $temp_user->dob,
                    'password' => Hash::make($password),
                ]);
                $verified_otp->update([
                    'user_id' => $verified_user->id,
                    'user_type' => "1",
                ]);

                $temp_user->delete();

                $token = JWTAuth::attempt([
                    'mobile_no' => $verified_user->mobile_no,
                    'password' => $password,
                ]);

                $user['name'] = $verified_user->name;
                $user['email'] = $verified_user->email;
                $user['mobile_no'] = $verified_user->mobile_no;

                $success = ['user' => $user, 'token' => $token];

                return $this->sendResponse($this->success_status_code, 'User registration successful', $success);
            }
            return $this->sendError($this->backend_error_code, 'User registration failed');

        } catch (\Exception $e) {
            logError($this->controller_name, $function_name, $e);
            return $this->sendError($this->backend_error_code, "$this->error_message");
        }

    }

    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        $function_name = "login";
        $request_all = $request->all();
        try {
            $validator_rules = [
                'mobile_no' => 'required|string|max:10',
                'password' => 'required|string|min:6|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ];

            $validator_messages = [
                'mobile_no.required' => 'Mobile number is required',
                'mobile_no.string' => 'Invalid mobile number format',
                'mobile_no.max' => 'Mobile number should not exceed 10 characters',
                'password.required' => 'Password is required',
                'password.string' => 'Invalid password format',
                'password.min' => 'Password should be at least 6 characters',
                'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one numeric digit',
            ];

            $validator = Validator::make($request_all, $validator_rules, $validator_messages);
            if ($validator->fails()) {
                $firstErrorMessage = $validator->errors()->first();
                logger()->error("$this->controller_name:$function_name: Validation failed - $firstErrorMessage", ['request' => $request_all]);
                return $this->sendError($this->validator_error_code, "$firstErrorMessage");
            }


            if (!$token = JWTAuth::attempt($request_all)) {
                return $this->sendError($this->backend_error_code, "Invalid credentials");
            }

            $success = ['token' => $token];

            return $this->sendResponse($this->success_status_code, "User login successfully", $success);

        } catch (\Exception $e) {
            logError($this->controller_name, $function_name, $e);
            return $this->sendError($this->backend_error_code, "$this->error_message");
        }

    }

    public function logout(): \Illuminate\Http\JsonResponse
    {
        $function_name = "logout";
        try {
            $token = JWTAuth::getToken();
            if (!$token) {
                return $this->sendError($this->backend_error_code, "Token not Found");
            }
            JWTAuth::invalidate($token);
            return $this->sendResponse($this->success_status_code, "User logout successfully", []);

        } catch (\Exception $e) {
            logError($this->controller_name, $function_name, $e);
            return $this->sendError($this->backend_error_code, "$this->error_message");
        }

    }

    public function changePassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $function_name = "changePassword";
        $request_all = $request->all();
        try {
            $validator_rules = [
                'current_password' => 'required',
                'new_password' => 'required|string|min:6|different:current_password|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                'new_password_confirmation' => 'required|same:new_password',
            ];

            $validator_messages = [
                'current_password.required' => 'Current password is required',
                'new_password.required' => 'New password is required',
                'new_password.string' => 'Invalid new password format',
                'new_password.min' => 'New password should be at least 6 characters',
                'new_password.different' => 'New password should be different from the current password',
                'new_password.regex' => 'New password must contain at least one uppercase letter, one lowercase letter, and one numeric digit',
                'new_password_confirmation.required' => 'New password confirmation is required',
                'new_password_confirmation.same' => 'New password confirmation should match the new password',
            ];


            $validator = Validator::make($request_all, $validator_rules, $validator_messages);
            if ($validator->fails()) {
                $firstErrorMessage = $validator->errors()->first();
                logger()->error("$this->controller_name:$function_name: Validation failed - $firstErrorMessage", ['request' => $request_all]);
                return $this->sendError($this->validator_error_code, "$firstErrorMessage");
            }

            $user = auth('api')->user();

            if (!Hash::check($request_all['current_password'], $user->password)) {
                return $this->sendError($this->backend_error_code, "The current password is incorrect");
            }

            $user->update([
                'password' => Hash::make($request_all['new_password']),
            ]);

            return $this->sendResponse($this->success_status_code, "Password changed successfully", $data = []);

        } catch (\Exception $e) {
            logError($this->controller_name, $function_name, $e);
            return $this->sendError($this->backend_error_code, "$this->error_message");
        }

    }

    public function mobileOtpVerifiedForgotPassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $function_name = "mobileOtpVerifiedForgotPassword";
        $request_all = $request->all();
        try {
            $validator_rules = [
                'mobile_no' => 'required|string|size:10|exists:users,mobile_no',
                'otp' => 'required|numeric|digits:6|exists:mobile_otp,otp',
            ];

            $validator_messages = [
                'mobile_no.exists' => 'Invalid mobile number',
                'mobile_no.required' => 'Mobile number is required',
                'mobile_no.string' => 'Invalid mobile number format',
                'mobile_no.size' => 'Mobile number should be 10 characters',
                'otp.required' => 'OTP is required',
                'otp.numeric' => 'OTP must be a number',
                'otp.digits' => 'OTP must be 6 digits',
                'otp.exists' => 'Invalid OTP',
            ];

            $validator = Validator::make($request_all, $validator_rules, $validator_messages);
            if ($validator->fails()) {
                $firstErrorMessage = $validator->errors()->first();
                logger()->error("$this->controller_name:$function_name: Validation failed - $firstErrorMessage", ['request' => $request_all]);
                return $this->sendError($this->validator_error_code, "$firstErrorMessage");
            }

            $verified_otp = MobileOtp::where('mobile_no', $request_all['mobile_no'])->first();

            if ($verified_otp) {
                if ($verified_otp->mobile_otp_expire_at <= now()) {
                    return $this->sendError($this->backend_error_code, "OTP expired");
                } elseif ($verified_otp->otp != $request_all['otp']) {
                    return $this->sendError($this->backend_error_code, "Invalid OTP");
                } elseif ($verified_otp->mobile_otp_verified_at) {
                    return $this->sendError($this->backend_error_code, "Your mobile number is already verified");
                }

                $verified_otp->update(['mobile_otp_verified_at' => now()]);
            }

            return $this->sendResponse($this->success_status_code, "Forgot password OTP verified successfully", $data = []);

        } catch (\Exception $e) {
            logError($this->controller_name, $function_name, $e);
            return $this->sendError($this->backend_error_code, "$this->error_message");
        }

    }

    public function forgotPassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $function_name = "forgotPassword";
        $request_all = $request->all();
        try {
            $validator_rules = [
                'mobile_no' => 'required|string|size:10|exists:users,mobile_no',
                'password' => 'required|string|min:6|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ];

            $validator_messages = [
                'mobile_no.exists' => 'Invalid mobile number',
                'mobile_no.required' => 'Mobile number is required',
                'mobile_no.string' => 'Invalid mobile number format',
                'mobile_no.size' => 'Mobile number should be 10 characters',
                'password.required' => 'Password is required',
                'password.string' => 'Invalid password format',
                'password.min' => 'Password should be at least 6 characters',
                'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one numeric digit',
            ];


            $validator = Validator::make($request_all, $validator_rules, $validator_messages);
            if ($validator->fails()) {
                $firstErrorMessage = $validator->errors()->first();
                logger()->error("$this->controller_name:$function_name: Validation failed - $firstErrorMessage", ['request' => $request_all]);
                return $this->sendError($this->validator_error_code, "$firstErrorMessage");
            }

            $reset_password = User::where('mobile_no', $request_all['mobile_no'])->first();
            if ($reset_password) {
                $reset_password->update([
                    'password' => Hash::make($request_all['password']),
                ]);
            }

            return $this->sendResponse($this->success_status_code, "Password reset successfully", $data = []);
        } catch (\Exception $e) {
            logError($this->controller_name, $function_name, $e);
            return $this->sendError($this->backend_error_code, "$this->error_message");
        }

    }

    public function user(Request $request)
    {
        $function_name = "user";
        $request_all = $request->all();
        try {
            $user = auth('api')->user();
            dd($user);
        } catch (\Exception $e) {
            logError($this->controller_name, $function_name, $e);
            return $this->sendError($this->backend_error_code, "$this->error_message");
        }

    }

}
