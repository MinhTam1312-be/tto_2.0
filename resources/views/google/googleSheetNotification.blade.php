<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .header h1 {
            color: #333;
        }
        .content {
            margin: 20px 0;
            color: #555;
            line-height: 1.6;
        }
        .content p {
            margin: 10px 0;
        }
        .highlight {
            background-color: #fff9c4;
            border-left: 4px solid #ffc107;
            padding: 10px;
            margin: 20px 0;
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #888;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Trả lời câu hỏi</h1>
        </div>
        <div class="content">
            <p>Xin chào {{ $fullname }},</p>
            <p>Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi. Dưới đây là nội dung quan trọng:</p>
            <div class="highlight">{{ $text }}</div>
        </div>
        <div class="footer">
            <p>Bạn nhận được email này vì bạn đã đăng ký nhận thông báo từ chúng tôi.</p>
            <p>© 2024 tto. Mọi quyền được bảo lưu.</p>
        </div>
    </div>
</body>
</html>
