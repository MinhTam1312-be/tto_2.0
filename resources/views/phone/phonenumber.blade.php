{{-- <!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SMS Portal With Twilio</title>
    <!-- Styles -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS"
        crossorigin="anonymous">
</head>
<body>
    <div class="container">
        <div class="jumbotron">
            @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <div class="row">
                <div class="col">
                    <div class="card">
                        <div class="card-header">
                            Add Phone Number
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('send-phone') }}">
                                @csrf
                                <div class="form-group">
                                    <label>Enter Phone Number</label>
                                    <input type="tel" class="form-control" name="phonenumber" placeholder="Enter Phone Number">
                                </div>
                                <button type="submit" class="btn btn-primary">Register User</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card">
                        <div class="card-header">
                            Send SMS message
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('custom') }}">
                                @csrf
                                <div class="form-group">   
                                    <label>Select users to notify</label>
                                    <select name="users[]" multiple class="form-control">
                                        @foreach ($users as $user)
                                        <option>+84{{$user->phonenumber}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Notification Message</label>
                                    <textarea name="body" class="form-control" rows="3"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Send Notification</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> --}}
<!-- resources/views/send-sms.blade.php -->

{{-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gửi SMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Gửi SMS</h2>

        <!-- Form gửi tin nhắn -->
        <form action="{{ route('send.sms') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="phone_number" class="form-label">Số điện thoại người nhận</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number" required>
            </div>
            <button type="submit" class="btn btn-primary">Gửi tin nhắn</button>
        </form>

        <!-- Hiển thị kết quả gửi tin nhắn -->
        @if(isset($messageStatus))
            <div class="mt-5">
                <h4>Trạng thái tin nhắn</h4>
                <p><strong>Message SID:</strong> {{ $messageSid }}</p>
                <p><strong>Trạng thái:</strong> {{ ucfirst($messageStatus) }}</p>
                <p><strong>Mã token của bạn là:</strong> {{ $generatedToken }}</p>
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> --}}
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gửi Cuộc Gọi OTP</title>
</head>
<body>
    <h1>Gửi Cuộc Gọi OTP</h1>

    <!-- Hiển thị thông báo thành công hoặc lỗi -->
    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif
    @if(session('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif

    <form action="{{ route('sms.send') }}" method="POST">
        @csrf
        <div>
            <label for="phone">Số điện thoại:</label>
            <input type="text" id="phone" name="phone" required>
        </div>
        <div>
            <label for="content">Nội dung cuộc gọi:</label>
            <input type="text" id="content" name="content" required>
        </div>
        <div>
            <label for="request_id">ID yêu cầu:</label>
            <input type="text" id="request_id" name="request_id" required>
        </div>
        <button type="submit">Gửi yêu cầu cuộc gọi</button>
    </form>
</body>
</html>
