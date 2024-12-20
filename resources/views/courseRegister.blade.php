<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pusher Event UI</title>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .message-box {
            border: 1px solid #ccc;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
            background-color: #f9f9f9;
            transition: opacity 1s ease;
            /* Hiệu ứng mờ dần */
        }

        .message-box.fade-out {
            opacity: 0;
        }

        .message-title {
            font-weight: bold;
            color: #333;
        }

        .message-content {
            margin-top: 5px;
            color: #555;
        }
    </style>
</head>

<body>
    <h1>Nhận dữ liệu từ Pusher</h1>
    <div id="messages">
        <p>Chưa có dữ liệu nào được nhận.</p>
    </div>

    <script>
        // Kết nối tới Pusher
        var pusher = new Pusher('81b38a443c8e7f96deef', {
            cluster: 'ap1'
        });

        // Đăng ký channel
        var channel = pusher.subscribe('my-channel');

        // Lắng nghe sự kiện
        channel.bind('my-event', function(data) {
            // Kiểm tra và truy cập đến dữ liệu message
            if (data.message) {
                var message = data.message;

                // Hiển thị dữ liệu lên giao diện
                var messagesDiv = document.getElementById('messages');

                // Tạo box hiển thị
                var messageBox = document.createElement('div');
                messageBox.className = 'message-box';

                // Tạo phần tiêu đề
                var title = document.createElement('div');
                title.className = 'message-title';
                title.textContent = "Tiêu đề: " + message.title;

                // Tạo phần nội dung
                var content = document.createElement('div');
                content.className = 'message-content';
                content.textContent = "Nội dung: " + message.content;

                // Gắn các thành phần vào box
                messageBox.appendChild(title);
                messageBox.appendChild(content);

                // Gắn box vào giao diện
                messagesDiv.appendChild(messageBox);

                // Đặt thời gian để xóa box (ví dụ: sau 5 giây)
                setTimeout(function() {
                    messagesDiv.removeChild(messageBox);
                }, 5000); // 5000ms = 5 giây
            } else {
                console.error("Dữ liệu nhận được không hợp lệ: ", data);
            }
        });
    </script>
</body>

</html>
