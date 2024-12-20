<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo thanh toán</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }

        .payment-container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .payment-header {
            background-color: #4caf50;
            color: white;
            text-align: center;
            padding: 20px;
        }

        .payment-header.failed {
            background-color: #f44336;
        }

        .payment-header h1 {
            margin: 0;
        }

        .payment-details {
            padding: 20px;
            line-height: 1.6;
        }

        .payment-details span {
            font-weight: bold;
        }

        .payment-footer {
            text-align: center;
            padding: 20px;
            background-color: #f9f9f9;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            color: white;
            background-color: #4caf50;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }

        .btn.failed {
            background-color: #f44336;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .panda img {
            display: block;
            margin: 20px auto;
            max-width: 100%;
            height: auto;
            border-radius: 10px;
        }
    </style>
</head>

<body>
    <div class="payment-container">
        <div class="payment-header {{ $data['status'] == 'success' ? '' : 'failed' }}">
            <h1>{{ $data['status'] == 'success' ? 'Thanh toán thành công!' : 'Thanh toán thất bại!' }}</h1>
            <p>{{ $data['message'] }}</p>
        </div>
        <div class="payment-details">
            <p><span>Tên người mua:</span> {{ $data['fullname'] }}</p>
            <p><span>Khóa học:</span> {{ $data['nameCourse'] }}</p>
            <p><span>Giá tiền:</span> {{ number_format($data['priceCourse'], 0, ',', '.') }} VND</p>
        </div>
        <div class="payment-footer">
            @if ($data['status'] == 'success')
                <a href="https://www.tto.sh/learningCourse/{{ $data['slugCourse'] }}" class="btn">Truy cập khóa học</a>
            @else
                <a href="https://www.tto.sh/" class="btn failed">Quay về trang chủ</a>
            @endif
        </div>
    </div>
    <div class="panda">
        <img src="https://media.tenor.com/4Ed2qPQR1ugAAAAi/applause-applauses.gif" alt="Panda animation">
    </div>
</body>

</html>
