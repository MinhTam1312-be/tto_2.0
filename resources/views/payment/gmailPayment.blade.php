<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giao Diện Thanh Toán VIP</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(to right, rgba(8, 103, 231, 1), rgba(255, 255, 255, 1));
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            position: relative;
            overflow-y: hidden;
        }

        .payment-container {
            background: linear-gradient(to bottom, #ffffff, #f8f9fa);
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.3);
            border-radius: 16px;
            width: 420px;
            padding: 30px;
            position: relative;
            overflow: hidden;
            z-index: 2;
        }

        .payment-container::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 126, 95, 0.1);
            border-radius: 50%;
        }

        .payment-container::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 126, 95, 0.1);
            border-radius: 50%;
        }

        .payment-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .payment-header h1 {
            font-size: 26px;
            color: #333;
            margin: 0;
        }

        .payment-header p {
            font-size: 15px;
            color: #0096aa;
            margin: 5px 0 0;
        }

        .payment-details {
            margin-bottom: 30px;
        }

        .payment-details p {
            margin: 15px 0;
            font-size: 16px;
            color: #555;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f1f1f1;
            padding: 10px 15px;
            border-radius: 8px;
        }

        .payment-details p span {
            font-weight: bold;
            color: #333;
        }

        .payment-footer {
            text-align: center;
        }

        .btn {
            display: inline-block;
            background: linear-gradient(to right, rgba(8, 103, 231, 1), rgba(255, 255, 255, 1));
            color: #fdfdfd;
            text-decoration: none;
            padding: 14px 35px;
            border-radius: 50px;
            font-size: 18px;
            font-weight: bold;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .btn:hover {
            background: linear-gradient(to right, rgba(255, 255, 255, 1), rgba(8, 103, 231, 1));
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            transform: translateY(-3px);
        }

        .btn:active {
            transform: translateY(3px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
        }

        .panda {
            position: absolute;
            bottom: 0;
            width: 100px;
            height: 100px;
            animation: jump 2s infinite;
        }

        .panda:nth-child(1) {
            left: 10%;
        }

        .panda:nth-child(2) {
            left: 30%;
            animation-delay: 0.2s;
        }

        .panda:nth-child(3) {
            left: 50%;
            animation-delay: 0.4s;
        }

        .panda:nth-child(4) {
            left: 70%;
            animation-delay: 0.6s;
        }

        @keyframes jump {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .panda img {
            width: 100%;
            height: auto;
        }
    </style>
</head>

<body>
    
    <div class="payment-container">
        <div class="payment-header">
            <h1>Thanh toán thông tin khóa học</h1>
            <p>Hãy kiểm tra và xác nhận thông tin trước khi thanh toán</p>
        </div>
        <div class="payment-details">
            <p><span>Tên người mua:</span> {{$fullname}}</p>
            <p><span>Khóa học:</span>{{$nameCourse}}</p>
            <p><span>Giá tiền:</span> {{ number_format($priceCourse, 0, ',', '.') }} VND</p>
            <p><span>Ngày mua:</span>{{$date}}</p>
        </div>
    </div>
    <div class="panda">
        <img src="https://media.tenor.com/4Ed2qPQR1ugAAAAi/applause-applauses.gif" width="480" height="480"
            alt="a cartoon panda bear is holding a yellow heart in its hands"
            style="max-width: 496px; background-color: unset;">
    </div>
    <div class="panda">
        <img src="https://media.tenor.com/4Ed2qPQR1ugAAAAi/applause-applauses.gif" width="480" height="480"
            alt="a cartoon panda bear is holding a yellow heart in its hands"
            style="max-width: 496px; background-color: unset;">
    </div>
    <div class="panda">
        <img src="https://media.tenor.com/4Ed2qPQR1ugAAAAi/applause-applauses.gif" width="480" height="480"
            alt="a cartoon panda bear is holding a yellow heart in its hands"
            style="max-width: 496px; background-color: unset;">
    </div>
    <div class="panda">
        <img src="https://media.tenor.com/4Ed2qPQR1ugAAAAi/applause-applauses.gif" width="480" height="480"
            alt="a cartoon panda bear is holding a yellow heart in its hands"
            style="max-width: 496px; background-color: unset;">
    </div>
</body>

</html>
