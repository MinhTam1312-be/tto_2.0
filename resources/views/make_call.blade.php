<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gửi OTP qua Cuộc Gọi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f9;
        }
        .container {
            max-width: 400px;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .message {
            margin-top: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Gửi OTP qua Cuộc Gọi</h2>
        @if(session('success'))
            <p style="color: green;">{{ session('success') }}</p>
        @endif
        @if(session('error'))
            <p style="color: red;">{{ session('error') }}</p>
        @endif
        <form method="POST" action="{{ route('sms.send') }}">
            @csrf
            <input type="text" name="phone" placeholder="Nhập số điện thoại" required>
            <button type="submit">Gửi OTP</button>
        </form>
    </div>
</body>
</html>
