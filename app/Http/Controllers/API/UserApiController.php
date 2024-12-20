<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\UserResource;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Mail\changeMail;
use App\Mail\CheckMail;
use App\Mail\SendMail;
use App\Mail\sendMailToConfirmChanges;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Google_Client;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;


class UserApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    public function index()
    {
        try {
            $users = User::where('del_flag', true)->get();
            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'data' => UserResource::collection($users),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $user = User::findOrFail($id);
            return response()->json([
                'status' => 'success',
                'message' => 'Dữ liệu được lấy thành công',
                'data' => new UserResource($user),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 400);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }
    // done update (tâm)
    public function changeDiscriptionUser(Request $request)
    {
        try {
            $user_id = auth('api')->user()->id;

            $validatedData = $request->validate([
                'discription_user' => 'required',
            ], [
                'discription_user.required' => 'Không được bỏ trống'
            ]);
            $user = User::find($user_id);
            $user->update([
                'discription_user' => $request->discription_user,
            ]);
            $user->save();
            return response()->json([
                'status' => 'success',
                'message' => 'Thay đổi mô tả thành công',
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $validatedData) {
            // Catch validation exceptions and return the errors
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validatedData->errors(), // Return detailed validation errors
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
    public function cancel(Request $request)
    {
        $oldEmail = $request->input('old_email');
        Cache::forget('email-change-' . $oldEmail);
        return view('emails.result', [
            'status' => 'fail',
            'message' => 'Cập nhật email thất bại.',
        ]);
    }
    // done age (tâm)
    public function changeAgeUser(Request $request)
    {
        try {
            $user_id = auth('api')->user()->id;

            $validatedData = $request->validate([
                'age' => 'required|integer|min:5|max:80',
            ], [
                'age.required' => 'Không được bỏ trống',
                'age.integer' => 'Tuổi phải là số',
                'age.min' => 'Tuổi phải lớn hơn hoặc bằng 5',
                'age.max' => 'Tuổi không được vượt quá 80',
            ]);
            $user = User::find($user_id);
            $user->update([
                'age' => $request->age,
            ]);
            $user->save();
            return response()->json([
                'status' => 'success',
                'message' => 'Thay đổi tuổi thành công',
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $validatedData) {
            // Catch validation exceptions and return the errors
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validatedData->errors(), // Return detailed validation errors
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
    public function changeEmailUser(Request $request)
    {
        $request->validate([
            'old_email' => 'required|email:filter',
            'new_email' => 'required|email:filter',
        ], [
            'old_email.required' => 'Không được bỏ trống mail cũ',
            'new_email.required' => 'Không được bỏ trống mail mới',
        ]);

        // Kiểm tra cache để xác nhận thời gian hiệu lực
        $cacheKey = 'email-change-' . $request->old_email;

        // Lấy dữ liệu từ cache
        $cacheData = Cache::get($cacheKey);
        // dd($cacheData);

        if (!$cacheData || $cacheData['new_email'] !== $request->new_email || now()->isAfter($cacheData['expires_at'])) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Yêu cầu thay đổi email đã hết hiệu lực hoặc không hợp lệ.',
            ], 400);
        }

        // Tìm người dùng theo email cũ
        $user = User::where('email', $request->old_email)->first();
        if (!$user) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Không tìm thấy người dùng với email cũ.',
            ], 404);
        }

        // Cập nhật email mới
        $user->update([
            'email' => $request->new_email,
        ]);

        // Xóa cache sau khi cập nhật thành công
        Cache::forget($cacheKey);

        return view('emails.result', [
            'status' => 'success',
            'message' => 'Cập nhật email thành công.',
        ]);
    }
    // done fullname (tâm)
    public function changeFullnameUser(Request $request)
    {
        try {
            $user_id = auth('api')->user()->id;

            $validatedData = $request->validate([
                'fullname' => 'required|max:150',
            ], [
                'fullname.required' => 'Không được bỏ trống',
                'fullname.max' => 'Username không quá 150 kí tự',
            ]);
            $user = User::find($user_id);
            $user->update([
                'fullname' => $request->fullname,
            ]);
            $user->save();
            return response()->json([
                'status' => 'success',
                'message' => 'Thay đổi tên thành công',
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $validatedData) {
            // Catch validation exceptions and return the errors
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validatedData->errors(), // Return detailed validation errors
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    // Kiểm tra số điện thoại, xác thực
    public function checkPhoneUser(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'phonenumber' => [
                    'required',
                    'regex:/^(032|033|034|035|036|037|038|039|096|097|098|086|083|084|085|081|082|088|091|094|070|079|077|076|078|090|093|089|056|058|092|059|099)[0-9]{7}$/',
                ],
            ], [
                'phonenumber.required' => 'Vui lòng nhập số điện thoại',
                // 'phonenumber.max' => 'Số điện thoại không quá 10 số',
                'phonenumber.regex' => 'Số điện thoại không đúng định dạng',
            ]);
            // cần check trùng số só điện thoại
            $user = User::where('phonenumber', $request->phonenumber)->first();
            if ($user) {
                return response()->json(['message' => 'Số điện này đã tồn tại trong hệ thống'], 422);
            }
            $APIKey = "EAD1A6113D61D8C6A4FF70469E0608";
            $SecretKey = "BCFEF1BBB0A198CF2B2B6032BD2D0C";
            $brandname = 'Baotrixemay';
            $Code = 'Cam on quy khach da su dung dich vu cua chung toi. Chuc quy khach mot ngay tot lanh!';
            $YourPhone = $request->phonenumber;

            // Gọi API
            $response = Http::get("https://rest.esms.vn/MainService.svc/json/SendMultipleMessage_V4_get", [
                'SmsType' => 2,
                'ApiKey' => $APIKey,
                'SecretKey' => $SecretKey,
                'Phone' => $YourPhone,
                'Content' => $Code,
                'Brandname' => $brandname,
            ]);

            // Xử lý phản hồi từ API
            if ($response->successful()) {
                return response()->json([
                    'status' => 'suscces',
                    'message' => 'Đã gửi mã thành công',
                ], 201);
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Đã gửi mã thất bại',
                ], 422);
            }
        } catch (\Illuminate\Validation\ValidationException $validatedData) {
            // Bắt các lỗi xác thực và trả về lỗi chi tiết
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validatedData->errors(), // Trả về lỗi xác thực chi tiết
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    // Nhập, xác nhận mã xác thực và thay đổi số điện thoại
    public function verifyPhone(Request $request)
    {
        try {
            $user_id = auth('api')->user()->id;

            $validatedData = $request->validate([
                'phonenumber' => [
                    'required',
                    'regex:/^(032|033|034|035|036|037|038|039|096|097|098|086|083|084|085|081|082|088|091|094|070|079|077|076|078|090|093|089|056|058|092|059|099)[0-9]{7}$/',
                ],
                'verify' => 'size:6|required',
            ], [
                'phonenumber.required' => 'Vui lòng nhập số điện thoại.',
                'phonenumber.regex' => 'Số điện thoại không đúng định dạng.',
                'verify.required' => 'Vui lòng nhập mã xác nhận.',
                'verify.size' => 'Mã xác nhận không đúng định dạng.',
            ]);
            $result = $request->verify == 123456;
            if ($result) {
                $user = User::find($user_id);

                // Cập nhật số điện thoại
                $user->update([
                    'phonenumber' => $request->phonenumber,
                ]);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Cập nhật số điện thoại thành công',
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Mã xác minh không đúng. Vui lòng kiểm tra và thử lại.',
                ], 422);
            }
        } catch (\Illuminate\Validation\ValidationException $validatedData) {
            // Bắt các lỗi xác thực và trả về lỗi chi tiết
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validatedData->errors(), // Trả về lỗi xác thực chi tiết
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    // Thay đổi mật khẩu người dùng
    public function changePasswordUser(Request $request)
    {
        try {
            $user_id = auth('api')->user()->id;

            $validatedData = $request->validate([
                'old_password' => 'required|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*?&#]/',
                'new_password' => 'required|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*?&#]/',
                'new_confirm_password' => 'required|same:new_password',
            ], [
                'old_password.required' => 'Vui lòng nhập mật khẩu',
                'old_password.min' => 'Vui lòng nhập mật khẩu có ít nhất 8 kí tự',
                'old_password.regex' => 'Mật khẩu phải chứa ít nhất 1 chữ cái thường, 1 chữ cái in hoa, 1 chữ số và 1 ký tự đặc biệt (@$!%*?&#)',
                'new_password.required' => 'Vui lòng nhập mật khẩu mới',
                'new_password.min' => 'Vui lòng nhập mật khẩu có ít nhất 8 kí tự',
                'new_password.regex' => 'Mật khẩu phải chứa ít nhất 1 chữ cái thường, 1 chữ cái in hoa, 1 chữ số và 1 ký tự đặc biệt (@$!%*?&#)',
                'new_confirm_password.required' => 'Vui lòng nhập lại mật khẩu mới.',
                'new_confirm_password.same' => 'Mật khẩu không trùng nhau.',
            ]);
            $user = User::find($user_id);
            if (!Hash::check($request->old_password, $user->password)) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Mật khẩu cũ không đúng.',
                ], 400);
            }
            $user->update([
                'password' => Hash::make($request->new_confirm_password),
            ]);
            $user->save();
            return response()->json([
                'status' => 'success',
                'message' => 'Thay đổi mật khẩu thành công.',
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $validatedData) {
            // Catch validation exceptions and return the errors
            return response()->json([
                'status' => 'fail',
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validatedData->errors(), // Return detailed validation errors
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    //FORGOT PASSWORD 
    public function forgotPassword(Request $request)
    {
        // Kiểm tra định dạng email
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', $validator->errors()], 422);
        }

        // Kiểm tra email có tồn tại không
        $user = DB::table('users')->where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Email không tồn tại'], 404);
        }

        // Kiểm tra thời gian gửi yêu cầu trước đó
        $lastRequest = DB::table('password_reset_tokens')->where('email', $request->email)->first();
        // Tạo mã xác thực (OTP)
        $token = Str::random(6);
        Log::info('Mã OTP được tạo: ' . $token . ' cho email: ' . $request->email);

        // Cập nhật hoặc tạo mới token
        if ($lastRequest) {
            if (!Carbon::parse($lastRequest->created_at)->addSeconds(30)->isPast()) {
                return response()->json(['message' => 'Vui lòng chờ ít nhất 120 giây trước khi yêu cầu mã xác thực mới'], 429);
            }
            // Nếu tồn tại, cập nhật token mới
            DB::table('password_reset_tokens')->where('email', $request->email)->update([
                'token' => $token,
                'created_at' => Carbon::now('Asia/Ho_Chi_Minh') // Cập nhật thời gian tạo
            ]);
        } else {
            // Nếu chưa tồn tại, tạo mới token
            DB::table('password_reset_tokens')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => Carbon::now() // Thời gian tạo mới
            ]);
        }

        // Gửi email chứa mã xác thực
        try {
            Mail::to($request->email)->send(new SendMail($token));
            Log::info('Gửi được mail thành công: ' . $request->email);
            return response()->json(['message' => 'Mã xác thực đã được gửi đến email'], 200);
        } catch (\Exception $e) {
            Log::error('Gửi email thất bại: ' . $e->getMessage());
            return response()->json(['message' => 'Gửi email thất bại: ' . $e->getMessage()], 500);
        }
    }
    public function mail()
    {
        try {
            Mail::to('nmtam1312022@gmail.com')->send(new SendMail('fd5MWr'));
            // Log::info('Gửi được mail thành công: ' . $request->email);
            return response()->json(['message' => 'Mã xác thực đã được gửi đến email'], 200);
        } catch (\Exception $e) {
            Log::error('Gửi email thất bại: ' . $e->getMessage());
            return response()->json(['message' => 'Gửi email thất bại: ' . $e->getMessage()], 500);
        }
    }
    //RESET PASSWORD
    // done(tâm)
    public function verifyToken(Request $request)
    {
        $request->validate([
            'token' => 'required|size:6',
        ], [
            'token.required' => 'Vui lòng nhập mã xác nhận',
            'token.size' => 'Mã phải đúng 6 kí tự',
        ]);

        // Kiểm tra mã xác thực
        $passwordReset = DB::table('password_reset_tokens')->where('token', $request->token)->first();

        if (!$passwordReset) {
            return response()->json(['message' => 'Mã xác thực không hợp lệ'], 400);
        }

        // Kiểm tra thời gian mã kiểm tra trạng thái đã gửi được hay chưa
        if (Carbon::parse($passwordReset->created_at)->addSeconds(120)->isPast()) {
            return response()->json(['message' => 'Mã xác thực đã hết hạn'], 400);
        }

        // Tìm người dùng dựa trên email từ bảng `password_resets`
        $user = User::where('email', $passwordReset->email)->first();

        // Trả về ID của người dùng để dùng trong bước tiếp theo
        return response()->json(['message' => 'Mã xác thực hợp lệ', 'user_id' => $user->id], 200);
    }
    // done (tâm)
    public function resetPassword(Request $request)
    {
        try {
            // Xác thực dữ liệu
            $request->validate([
                'password' => 'required|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*?&#]/',
                'confirm_password' => 'required|same:password',
                'user_id' => 'required|exists:users,id',
            ], [
                'password.required' => 'Vui lòng nhập mật khẩu',
                'password.min' => 'Vui lòng nhập mật khẩu có ít nhất 8 kí tự',
                'password.regex' => 'Mật khẩu phải chứa ít nhất 1 chữ cái thường, 1 chữ cái in hoa, 1 chữ số và 1 ký tự đặc biệt (@$!%*?&#)',
                'confirm_password.required' => 'Vui lòng nhập lại mật khẩu',
                'confirm_password.same' => 'Mật khẩu không trùng',
                'user_id.required' => 'Thiếu ID người dùng',
                'user_id.exists' => 'Người dùng không tồn tại',
            ]);

            // Tìm người dùng bằng ID
            $user = User::find($request->user_id);

            if (!$user) {
                return response()->json(['message' => 'Người dùng không tồn tại'], 404);
            }

            // Đổi mật khẩu
            $user->password = Hash::make($request->confirm_password);
            $user->save();

            // Xóa mã xác thực sau khi đổi mật khẩu thành công
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();

            return response()->json(['message' => 'Mật khẩu đã được thay đổi thành công'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Xử lý lỗi xác thực
            return response()->json([
                'status' => 'error',
                'errors' => $e->validator->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Xử lý các lỗi khác
            return response()->json(['message' => 'Đã xảy ra lỗi: ' . $e->getMessage()], 500);
        }
    }
    public function redirectToGoogle(Request $request)
    {
        try {
            $idToken = $request->accessToken;

            if (!$idToken) {
                return response()->json(['error' => 'id_token không được cung cấp'], 400);
            }

            // Check if user exists by email
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                // Generate unique provider ID
                $provider_id = 'google_' . Str::random(10);

                // Create new user
                $user = User::create([
                    'fullname' => $request->name,
                    'email' => $request->email,
                    'role' => 'client',
                    'del_flag' => true,
                    'provider_id' => $provider_id,
                    'avatar' => $request->image,
                ]);
            }

            try {
                // Generate JWT token from the user instance
                $token = JWTAuth::fromUser($user);
            } catch (JWTException $e) {
                return response()->json(['error' => 'Không thể tạo token'], 500);
            }

            // Return token if successful
            return $this->respondWithToken($token);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Đã xảy ra lỗi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function redirectToFacebook(Request $request)
    {
        try {
            $idToken = $request->accessToken;

            if (!$idToken) {
                return response()->json(['error' => 'id_token không được cung cấp'], 400);
            }

            // Check if user exists by email
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                // Generate unique provider ID
                $provider_id = 'facebook_' . Str::random(10);

                // Create new user
                $user = User::create([
                    'fullname' => $request->name,
                    'email' => $request->email,
                    'role' => 'client',
                    'del_flag' => true,
                    'provider_id' => $provider_id,
                    'avatar' => $request->image,
                ]);
            }

            try {
                // Generate JWT token from the user instance
                $token = JWTAuth::fromUser($user);
            } catch (JWTException $e) {
                return response()->json(['error' => 'Không thể tạo token'], 500);
            }

            // Return token if successful
            return $this->respondWithToken($token);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Đã xảy ra lỗi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email_or_phone' => 'required', // Trường này dùng để nhập email hoặc số điện thoại
            'password' => 'required|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*?&#]/',
        ], [
            'password.required' => 'Vui lòng nhập mật khẩu',
            'password.min' => 'Vui lòng nhập mật khẩu có ít nhất 8 kí tự',
            'password.regex' => 'Mật khẩu phải chứa ít nhất 1 chữ cái thường, 1 chữ cái in hoa, 1 chữ số và 1 ký tự đặc biệt (@$!%*?&#)',
            'email_or_phone.required' => 'Vui lòng nhập email hoặc số điện thoại', // Trường yêu cầu
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Lấy thông tin người dùng
        $credentials = $request->only('email_or_phone', 'password');
        $input = $credentials['email_or_phone'];

        // Kiểm tra nếu dữ liệu nhập vào là email hay số điện thoại
        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            // Nếu là email, tìm người dùng theo email
            $user = User::where('email', $input)->first();
        } else {
            // Nếu là số điện thoại, tìm người dùng theo số điện thoại
            $user = User::where('phonenumber', $input)->first(); // Giả sử bạn có cột `phone_number`
        }

        if (!$user) {
            return response()->json(['error' => 'Tài khoản chưa được đăng ký'], 404);
        }

        if ($user->del_flag == false) {
            return response()->json(['error' => 'Tài khoản này đã bị vô hiệu hóa'], 403);
        }

        if (Hash::check($credentials['password'], $user->password)) {  // Kiểm tra mật khẩu
            try {
                // Tạo token JWT
                $token = JWTAuth::fromUser($user); // Tạo token từ người dùng
            } catch (JWTException $e) {
                return response()->json(['error' => 'Không thể tạo token'], 500);
            }

            // Trả về token nếu đăng nhập thành công
            return $this->respondWithToken($token);
        } else {
            return response()->json(['error' => 'Mật khẩu không chính xác'], 401);
        }
    }
    // Phương thức trả về token và thông tin hết hạn
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60 // Thời gian hết hạn tính theo giây
        ]);
    }
    public function checkMailChange(Request $request)
    {
        // Kiểm tra định dạng email
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
        ]);
        $user = User::where('email', $request->email)->first();
        if ($user) {
            if ($user->password) {
                return response()->json(['message' => 'Email này đã được sử dụng. Vui lòng điền email khác!'], 422);
            }
        }

        if ($validator->fails()) {
            return response()->json(['status' => 'error', $validator->errors()], 422);
        }

        // Kiểm tra thời gian gửi yêu cầu trước đó
        $lastRequest = DB::table('password_reset_tokens')->where('email', $request->email)->first();
        // Tạo mã xác thực (OTP)
        $token = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Log::info('Mã OTP được tạo: ' . $token . ' cho email: ' . $request->email);

        // Cập nhật hoặc tạo mới token
        if ($lastRequest) {
            if (!Carbon::parse($lastRequest->created_at)->addSeconds(120)->isPast()) {
                return response()->json(['message' => 'Vui lòng chờ ít nhất 120 giây trước khi yêu cầu mã xác thực mới'], 429);
            }
            // Nếu tồn tại, cập nhật token mới
            DB::table('password_reset_tokens')->where('email', $request->email)->update([
                'token' => $token,
                'created_at' => Carbon::now('Asia/Ho_Chi_Minh') // Cập nhật thời gian tạo
            ]);
        } else {
            // Nếu chưa tồn tại, tạo mới token
            DB::table('password_reset_tokens')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => Carbon::now() // Thời gian tạo mới
            ]);
        }

        // Gửi email chứa mã xác thực
        try {
            Mail::to($request->email)->send(new changeMail($token));
            Log::info('Gửi được mail thành công: ' . $request->email);
            return response()->json(['message' => 'Mã xác thực đã được gửi đến email'], 200);
        } catch (\Exception $e) {
            Log::error('Gửi email thất bại: ' . $e->getMessage());
            return response()->json(['message' => 'Gửi email thất bại: ' . $e->getMessage()], 500);
        }
    }
    public function checkMailTokenChange(Request $request)
    {
        try {
            // Validate url_certificate
            $request->validate([
                'email' => 'required|email:filter',
                'token' => 'required|size:6',
            ], [
                'token.required' => 'Vui lòng nhập mã xác nhận',
                'token.size' => 'Mã phải đúng 6 kí tự',
            ]);

            $passwordReset =  DB::table('password_reset_tokens')->where('email', $request->email)->where('token', $request->token)->first();

            if (!$passwordReset) {
                return response()->json(['message' => 'Mã xác thực không hợp lệ'], 400);
            }

            // Kiểm tra thời gian mã kiểm tra trạng thái đã gửi được hay chưa
            if (Carbon::parse($passwordReset->created_at)->addSeconds(120)->isPast()) {
                return response()->json(['message' => 'Mã xác thực đã hết hạn'], 400);
            }
            // Gửi email chứa mã xác thực
            $user = auth('api')->user();
            try {
                // Lưu email vào cache với thời gian hiệu lực 5 phút
                $expiresAt = now()->addMinutes(5);
                // Lưu email mới và thời gian hết hạn vào cache
                Cache::put('email-change-' . $user->email, [
                    'new_email' => $request->email,
                    'expires_at' => $expiresAt
                ], $expiresAt);
                // Cache::put('email-change-' . $user->email, $request->email, now()->addSeconds(30));
                Mail::to($user->email)->send(new sendMailToConfirmChanges($user->email, $request->email, $user->fullname));
                return response()->json(['message' => 'Mã xác thực đã được gửi đến email'], 200);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Gửi email thất bại: ' . $e->getMessage()], 500);
            }
            // Trả về kết quả thành công
            return response()->json([
                'status' => 'success',
                'message' => 'Cấp chứng chỉ thành công.',
                'data' => $passwordReset->email
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
            ], 500);
        }
    }
    public function checkMailRegister(Request $request)
    {
        // Kiểm tra định dạng email
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
        ]);
        $user = User::where('email', $request->email)->first();
        if ($user) {
            if ($user->password) {
                return response()->json(['message' => 'Email này đã được sử dụng. Vui lòng điền email khác!'], 422);
            }
        }

        if ($validator->fails()) {
            return response()->json(['status' => 'error', $validator->errors()], 422);
        }

        // Kiểm tra thời gian gửi yêu cầu trước đó
        $lastRequest = DB::table('password_reset_tokens')->where('email', $request->email)->first();
        // Tạo mã xác thực (OTP)
        $token = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Log::info('Mã OTP được tạo: ' . $token . ' cho email: ' . $request->email);

        // Cập nhật hoặc tạo mới token
        if ($lastRequest) {
            if (!Carbon::parse($lastRequest->created_at)->addSeconds(120)->isPast()) {
                return response()->json(['message' => 'Vui lòng chờ ít nhất 120 giây trước khi yêu cầu mã xác thực mới'], 429);
            }
            // Nếu tồn tại, cập nhật token mới
            DB::table('password_reset_tokens')->where('email', $request->email)->update([
                'token' => $token,
                'created_at' => Carbon::now('Asia/Ho_Chi_Minh') // Cập nhật thời gian tạo
            ]);
        } else {
            // Nếu chưa tồn tại, tạo mới token
            DB::table('password_reset_tokens')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => Carbon::now() // Thời gian tạo mới
            ]);
        }

        // Gửi email chứa mã xác thực
        try {
            Mail::to($request->email)->send(new CheckMail($token));
            Log::info('Gửi được mail thành công: ' . $request->email);
            return response()->json(['message' => 'Mã xác thực đã được gửi đến email'], 200);
        } catch (\Exception $e) {
            Log::error('Gửi email thất bại: ' . $e->getMessage());
            return response()->json(['message' => 'Gửi email thất bại: ' . $e->getMessage()], 500);
        }
    }
    public function register(Request $request)
    {
        try {
            if ($request->has('email')) {
                $validatedData = $this->validateEmailRequest($request);

                // Kiểm tra mã xác thực
                $passwordReset = DB::table('password_reset_tokens')->where('token', $request->token)->first();
                if (!$passwordReset) {
                    return $this->errorResponse('Mã xác thực không hợp lệ', 400);
                }

                if (Carbon::parse($passwordReset->created_at)->addSeconds(120)->isPast()) {
                    return $this->errorResponse('Mã xác thực đã hết hạn', 400);
                }

                $user = User::where('email', $request->email)->first();

                // Nếu user đã tồn tại
                if ($user) {
                    if ($user->provider_id && $user->password) {
                        return $this->errorResponse('Email này đã được sử dụng. Vui lòng thử email khác.', 422);
                    }

                    $user->update($this->getUserData($request));
                    return $this->successResponse('Người dùng cập nhật thành công', new UserResource($user));
                }

                // Nếu user chưa tồn tại
                $newUser = User::create($this->getUserData($request));
                return $this->successResponse('Tạo tài khoản thành công', new UserResource($newUser), 201);
            } else {
                // Xử lý đăng ký qua số điện thoại
                $validatedData = $this->validatePhoneRequest($request);
                if ($request->verify == 123456) {
                    $user = User::where('phonenumber', $request->phonenumber)->first();
                    // dd($request->phonenumber);
                    if ($user) {
                        return response()->json(['messenge' => 'Số điện thoại đã được sử dụng. Vui lòng nhập số điện thoại khác!'], 422);
                    }
                    $newUser = User::create($this->getUserData($request));
                    return $this->successResponse('Tạo tài khoản thành công', 201);
                } else {
                    return $this->errorResponse('Xác minh không hợp lệ', 422);
                }
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Lỗi xác thực dữ liệu', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Validate request data for email registration
     */
    private function validateEmailRequest($request)
    {
        return $request->validate([
            'fullname' => 'required|max:150',
            'password' => 'required|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*?&#]/',
            'confirm_password' => 'required|same:password',
            'email' => 'required|email:filter',
            'token' => ['required', 'regex:/^\d{6}$/']
        ], $this->validationMessages());
    }

    /**
     * Validate request data for phone registration
     */
    private function validatePhoneRequest($request)
    {
        return $request->validate([
            'fullname' => 'required|max:150',
            'password' => 'required|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*?&#]/',
            'confirm_password' => 'required|same:password',
            'phonenumber' => [
                'required',
                'regex:/^(032|033|034|035|036|037|038|039|096|097|098|086|083|084|085|081|082|088|091|094|070|079|077|076|078|090|093|089|056|058|092|059|099)[0-9]{7}$/',
            ],
            'verify' => 'size:6|required',
        ], $this->validationMessages());
    }

    /**
     * Common validation messages
     */
    private function validationMessages()
    {
        return [
            'fullname.required' => 'Vui lòng nhập họ và tên.',
            'fullname.max' => 'Họ và tên không quá 150 ký tự.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.regex' => 'Mật khẩu phải bao gồm ít nhất một chữ cái in hoa, một chữ cái thường, một số, và một ký tự đặc biệt.',
            'confirm_password.required' => 'Vui lòng nhập lại mật khẩu.',
            'confirm_password.same' => 'Mật khẩu nhập lại không khớp.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'token.required' => 'Vui lòng nhập mã xác nhận.',
            'token.regex' => 'Mã xác nhận phải bao gồm 6 chữ số.',
            'phonenumber.required' => 'Không được bỏ trống.',
            'phonenumber.regex' => 'Số điện thoại không đúng định dạng.',
            'verify.required' => 'Không được bỏ trống.',
            'verify.size' => 'Số mã không đúng định dạng.',
        ];
    }

    /**
     * Get user data from request
     */
    private function getUserData($request)
    {
        return [
            'fullname' => $request->fullname,
            'email' => $request->email ?? null, // Thêm email nếu có
            'password' => bcrypt($request->confirm_password),
            'phonenumber' => $request->phonenumber ?? null,
            'role' => 'client',
            'del_flag' => true,
            'avatar' => 'https://res.cloudinary.com/dnmc89c8b/image/upload/v1730112951/avatars/Avt%20m%E1%BA%B7c%20%C4%91%E1%BB%8Bnh%20cho%20ai%20c%E1%BA%A7n.jpg',
        ];
    }

    /**
     * Return a success response
     */
    private function successResponse($message, $data, $status = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Return an error response
     */
    private function errorResponse($message, $status, $errors = null)
    {
        return response()->json([
            'status' => 'fail',
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

    public function logout()
    {
        // Huỷ token
        auth('api')->logout();
        return response()->json(['message' => 'Đăng xuất thành công'], 200);
    }

    // API Kiểm tra người dùng đăng nhập
    public function me()
    {
        try {
            // Thử lấy thông tin người dùng đã xác thực
            $user = auth('api')->user();

            if (!$user) {
                return response()->json(['error' => 'Người dùng không tồn tại! Vui lòng đăng nhập lại.'], 404);
            }

            return response()->json($user, 200);
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token đã hết hạn'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Token không hợp lệ'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Không có token'], 400);
        }
    }
    public function checkToken(Request $request)
    {
        // Lấy token từ header
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Token không tồn tại'], 400);
        }

        try {
            // Giải mã token
            $payload = JWTAuth::setToken($token)->getPayload();

            // Lấy thông tin về thời gian hết hạn từ token
            $expires_at = $payload->get('exp'); // Thời gian hết hạn (unix timestamp)
            $current_time = now()->timestamp; // Lấy thời gian hiện tại (unix timestamp)

            if ($current_time >= $expires_at) {
                return response()->json(['message' => 'Token đã hết hạn'], 401);
            }

            return response()->json(['message' => 'Token vẫn còn hiệu lực']);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['message' => 'Token đã hết hạn'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['message' => 'Token không hợp lệ'], 400);
        }
    }

    public function updateAvatar(Request $request): JsonResponse
    {
        try {
            // Kiểm tra người dùng đăng nhập
            $user_id = auth('api')->user()->id;

            // Xác thực dữ liệu đầu vào với thông báo lỗi tùy chỉnh
            $validatedData = $request->validate([
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ], [
                'avatar.required' => 'Ảnh đại diện là bắt buộc.',
                'avatar.image' => 'Tệp tải lên phải là một hình ảnh.',
                'avatar.mimes' => 'Định dạng ảnh không được hỗ trợ. Vui lòng tải lên tệp .jpeg, .png, .jpg, hoặc .gif.',
                'avatar.max' => 'Dung lượng tệp quá lớn. Tối đa cho phép là 2MB.',
            ]);
            $user = User::findOrFail($user_id);
            // Xử lý file ảnh mới
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');

                // Upload ảnh mới lên Cloudinary
                $uploadedFileUrl = Cloudinary::upload($file->getRealPath(), [
                    'folder' => 'avatars',
                    'public_id' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
                ])->getSecurePath();

                // Cập nhật URL của ảnh đại diện mới vào cơ sở dữ liệu
                $user->avatar = $uploadedFileUrl;
                $user->save();
            }

            return response()->json([
                'message' => 'Cập nhật ảnh đại diện thành công.',
                'avatar' => $user->avatar,
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'message' => 'Đã xảy ra lỗi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteAvatar(): JsonResponse
    {
        try {
            $user = auth('api')->user();

            // Kiểm tra xem người dùng có avatar hay không
            if ($user->avatar) {
                $publicId = pathinfo(parse_url($user->avatar, PHP_URL_PATH), PATHINFO_FILENAME);

                // Xóa avatar từ Cloudinary
                Cloudinary::destroy("avatars/{$publicId}");
            }

            // Thay thế bằng ảnh mặc định
            $user->avatar = 'https://res.cloudinary.com/dnmc89c8b/image/upload/v1729964470/avatars/userDefault.jpg';
            // $user->save();

            return response()->json([
                'message' => 'Xóa ảnh đại diện thành công, ảnh mặc định đã được thay thế.',
                'avatar' => $user->avatar,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Đã xảy ra lỗi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function checkPassword(Request $request)
    {
        try {
            $request->validate([
                'password' => 'required|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*?&#]/',
            ], [
                'password.required' => 'Vui lòng nhập mật khẩu',
                'password.min' => 'Vui lòng nhập mật khẩu có ít nhất 8 kí tự',
                'password.regex' => 'Mật khẩu phải chứa ít nhất 1 chữ cái thường, 1 chữ cái in hoa, 1 chữ số và 1 ký tự đặc biệt (@$!%*?&#)',
            ]);
            $user_id = auth('api')->user()->id;
            $user = User::findOrFail($user_id);
            // Kiểm tra mật khẩu

            $isPassword = Hash::check($request->password, $user->password);
            if ($isPassword) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Lấy password thành công',
                    'data' =>$isPassword,
                ], 200);
            } else {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Lấy password thành công',
                    'data' =>$isPassword,
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Đã xảy ra lỗi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
