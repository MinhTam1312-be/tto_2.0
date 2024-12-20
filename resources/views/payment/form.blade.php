<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];
    $course_price = $_POST['course_price'];
    $token = $_POST['token']; // Token được truyền từ form

    // URL của API VNPay
    $url = "http://your-laravel-app-url/VNPay/{$course_id}/{$course_price}";

    // Khởi tạo cURL
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);

    // Header bao gồm token
    $headers = [
        "Authorization: Bearer {$token}"
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Nếu có dữ liệu POST cần gửi kèm, thêm vào đây
    curl_setopt($ch, CURLOPT_POSTFIELDS, []);

    // Thực thi request
    $response = curl_exec($ch);

    // Kiểm tra lỗi
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    } else {
        echo '<h3>Response from VNPay API:</h3>';
        echo '<pre>' . htmlspecialchars($response) . '</pre>';
    }

    curl_close($ch);
} else {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>VNPay API Test</title>
    </head>
    <body>
        <h1>VNPay API Test</h1>
        <form method="POST" action="">
            <label for="course_id">Course ID:</label>
            <input type="text" id="course_id" name="course_id" required><br><br>

            <label for="course_price">Course Price:</label>
            <input type="text" id="course_price" name="course_price" required><br><br>

            <label for="token">Token:</label>
            <input type="text" id="token" name="token" required><br><br>

            <button type="submit">Test VNPay API</button>
        </form>
    </body>
    </html>
    <?php
}
?>