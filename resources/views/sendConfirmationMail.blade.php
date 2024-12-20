</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MUO - Technology, Simplified</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f3f3f5;
            display: flex;
            height: 100vh;
            align-content: center;
            align-items: center;
        }

        .container {
            width: 100%;
            height: auto;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 10px;
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #fff;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }

        .header img {
            width: 120px;
        }

        .header .company-name {
            color: #828282;
            font-size: 14px;
            text-align: center;
        }

        h1 {
            color: #363636;
            font-size: 24px;
            font-weight: bold;
        }

        h2 {
            color: #363636;
            font-size: 20px;
        }

        .email-info {
            margin-top: 20px;
        }

        .email-info div {
            font-size: 16px;
            color: #363636;
            margin: 5px 0;
        }

        .email-info .old-email {
            color: #888888;
        }

        .email-info .new-email {
            font-weight: bold;
            color: #4CAF50;
        }

        .button-container {
            display: flex;
            justify-content: flex-end;
            gap: 20px;
            margin-top: 30px;
            padding-top: 30px;
        }

        .button-container .btn-success {
            background-color: rgba(73, 147, 248, 1);
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .button-container .btn-success:hover {
            background-color: rgba(8, 103, 231, 1);
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
            transform: scale(1.05);
        }

        .button-container .btn-success:active {
            background-color: rgba(73, 147, 248, 1);
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            transform: scale(0.98);
        }
        .button-container .btn-danger {
            background-color: rgba(250, 142, 112, 1);
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .button-container .btn-danger:hover {
            background-color: rgba(231, 57, 8, 1);
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
            transform: scale(1.05);
        }

        .button-container .btn-danger:active {
            background-color: rgba(250, 142, 112, 1);
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            transform: scale(0.98);
        }
    </style>
</head>

<body>

    <div class="container">
        <header class="header">
            <div>
                <img src="https://www.tto.sh/img/LogoPage.jpg" alt="tto.sh">
            </div>
            <div class="company-name">
                CÔNG TY CỔ PHẦN CÔNG NGHỆ GIÁO DỤC TTO
            </div>
        </header>
        <section>
            <h1>Hi, <b>{{ $fullname }}</b></h1>
            <h2>Xác minh Email thay đổi của bạn</h2>

            <div class="email-info">
                <div>
                    <strong>Email cũ:</strong>
                    <span class="old-email">{{ $mailOld }}</span>
                </div>
                <div>
                    <strong>Email mới:</strong>
                    <span class="new-email">{{ $mailNew }}</span>
                </div>
                <div class="button-container">
                    <form action="https://tto-production-db77.up.railway.app/api/client/cancel-email-change" method="POST">
                        @csrf
                        <input type="hidden" name="old_email" value="{{ $mailOld }}">
                        <button type="submit" class="btn btn-danger">Hủy</button>
                    </form>
    
                    <form action="https://tto-production-db77.up.railway.app/api/client/change-profile-email-user" method="POST">
                        @csrf
                        <input type="hidden" name="_method" value="PATCH">
                        <input type="hidden" name="old_email" value="{{ $mailOld }}">
                        <input type="hidden" name="new_email" value="{{ $mailNew }}">
                        <button type="submit" class="btn btn-success">Thay đổi</button>
                    </form>
                </div>

            </div>

        </section>
    </div>

</body>

</html>
