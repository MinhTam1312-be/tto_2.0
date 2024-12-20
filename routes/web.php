<?php

use App\Http\Controllers\API\UserApiController;
use App\Http\Controllers\API\VNPayApiController;
use App\Http\Controllers\CallController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ImageController;
use App\Http\Controllers\PhoneController;
use App\Http\Controllers\SocialController;

use App\Http\Controllers\GoogleSheetController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/payment', function () {
    $data = session('paymentData', null);
    return view('payment.payMentNotifi', ['data' => $data]);
});

Route::get('/upload', function () {
    return view('upload');
});
Route::post('VNPay/{course_id}/{course_price}', [VNPayApiController::class, 'getVNPay'])->name('vnpay.redirect');
Route::get('/vnpay-return', [VNPayApiController::class, 'vnpayReturn'])->name('vnpay.return');
Route::post('MOMO/{course_id}/{course_price}', [VNPayApiController::class, 'getMomo'])->name('momo.payUrl');
Route::get('momo-return', [VNPayApiController::class, 'momoReturn'])->name('momo.return');

Route::post('/upload-image', [ImageController::class, 'uploadImage'])->name('upload.image');
Route::get('auth/google', [SocialController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [SocialController::class, 'handleGoogleCallback']);
// login google
Route::get('/auth/redirect/{provider}', [SocialController::class, 'redirectToGoogle']);
Route::get('/callback/{provider}', [SocialController::class, 'callback']);
// login github
Route::get('/auth/redirects/{provider}', [SocialController::class, 'redirectToGithub'])->name('login.github');
Route::get('/auth/{provider}/callback', [SocialController::class, 'handleGithubCallback']);
// login facebook
Route::get('/auth/redirects/{provider}', [SocialController::class, 'redirectToFacebook'])->name('login.facebook');
Route::get('/auth/{provider}/callback', [SocialController::class, 'handleFacebookCallback']);

Route::get('/phone-number', [PhoneController::class, 'show']);
Route::post('/send-phone', [PhoneController::class, 'storePhoneNumber'])->name('send-phone');
Route::post('/custom', [PhoneController::class, 'sendCustomMessage'])->name('custom');

// Route::get('send-sms', [PhoneController::class, 'showForm'])->name('send.sms.form'); // Hiển thị form gửi SMS
// Route::post('send-sms', [PhoneController::class, 'SMS'])->name('send.sms'); // Gửi SMS và kiểm tra trạng thái

// Route::post('/payment/vnpay', [VNPayApiController::class, 'createPayment'])->name('payment.vnpay');
// Route::get('/payment/vnpay/return', [VNPayApiController::class, 'vnpayReturn'])->name('payment.vnpay.return');

Route::get('/posts', function () {
    return view('courseRegister');
});
Route::get('/send-sms', function () {
    return view('make_call');
})->name('send.sms.form');


// Route::post('/send-sms', [CallController::class, 'sendVerificationCode'])->name('send.verification.code');
Route::get('/send-verification', [CallController::class, 'sendVerification']);

Route::post('/sms/send', [CallController::class, 'sendSms'])->name('sms.send');
Route::post('/sms/callback', [CallController::class, 'callback']);

// Route để đọc dữ liệu từ Google Sheet
Route::get('/read-google-sheet', [GoogleSheetController::class, 'readGoogleSheet']);

// Route để ghi dữ liệu vào Google Sheet
Route::get('/write-google-sheet', [GoogleSheetController::class, 'writeGoogleSheet']);

// get dữ liệu
Route::get('/google-sheet', [GoogleSheetController::class, 'showForm'])->name('showForm');
// thêm dữ liệu
Route::post('/google-sheet/add', [GoogleSheetController::class, 'addData'])->name('addData');