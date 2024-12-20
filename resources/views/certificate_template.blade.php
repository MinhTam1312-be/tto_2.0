<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Certificate Email</title>
    <style>
        /* General email styles */
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
        }

        table {
            border-spacing: 0;
            border-collapse: collapse;
            width: 100%;
        }

        img {
            border: 0;
            display: block;
            margin: 0 auto;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            border: 1px solid #e2e8f0;
        }

        .header {
            background: linear-gradient(135deg, #2c5282, #38b2ac);
            color: #ffffff;
            padding: 25px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 32px;
            line-height: 1.5;
            letter-spacing: 1px;
        }

        .content {
            padding: 30px;
            text-align: center;
        }

        .content h2 {
            color: #2c5282;
            font-size: 26px;
            margin: 10px 0 15px;
        }

        .content p {
            color: #4a5568;
            font-size: 16px;
            line-height: 1.7;
            margin: 15px 0;
        }

        .content img {
            width: 200px;
            height: auto;
            margin-bottom: 20px;
        }

        .button {
            display: inline-block;
            margin-top: 20px;
            padding: 15px 30px;
            background-color: #38b2ac;
            color: #ffffff;
            text-decoration: none;
            border-radius: 25px;
            font-size: 18px;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .button:hover {
            background-color: #319795;
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
        }

        .footer {
            background-color: #f7fafc;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #718096;
            border-top: 1px solid #e2e8f0;
        }

        @media only screen and (max-width: 600px) {
            .content h2 {
                font-size: 22px;
            }

            .content p {
                font-size: 14px;
            }

            .button {
                font-size: 16px;
                padding: 12px 24px;
            }
        }
    </style>
</head>

<body>
    <table role="presentation" class="email-container" align="center">
        <!-- Header -->
        <tr>
            <td class="header">
                <h1>Chứng Chỉ Hoàn Thành Khóa Học</h1>
            </td>
        </tr>

        <!-- Content -->
        <tr>
            <td class="content">
                <img src="https://media.tenor.com/4Ed2qPQR1ugAAAAi/applause-applauses.gif" alt="Panda">
                <h2>{{ $fullname }}</h2>
                <p>Chúc mừng bạn đã hoàn thành khóa học</p>
                <h3>{{ $courseName }}</h3>
                <p><strong>Ngày cấp chứng chỉ:</strong> {{ $date }}</p>
                <a href="{{ $link }}" class="button">Xem Chứng Chỉ</a>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td class="footer">
                Cảm ơn bạn đã học tập tại <strong>TTO</strong>.<br />
                Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi.
            </td>
        </tr>
    </table>
</body>

</html>
