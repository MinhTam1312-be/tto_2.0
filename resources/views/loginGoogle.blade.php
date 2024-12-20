<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Test GET API với Token</title>
</head>

<body>
    {{-- <h2>Test GET API với Token</h2>
    <form method="POST">
        <label for="token">Token:</label>
        <input type="text" id="token" name="token" style="width: 400px;" required>
        <br><br>
        <button type="submit">Gọi GET API /posts</button>
    </form> --}}

    <?php
    // if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy token từ form
    $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vdHRvLXByb2R1Y3Rpb24tZGI3Ny51cC5yYWlsd2F5LmFwcC9hcGkvYXV0aC9sb2dpbiIsImlhdCI6MTczMTUxNjE3MCwiZXhwIjoxNzMyMTIwOTcwLCJuYmYiOjE3MzE1MTYxNzAsImp0aSI6IkFHOFBERWVYcFlUMmRrek4iLCJzdWIiOiIwMUpDSzE4VldXMDIxQlIzVjY5QTFQUVAyMyIsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.kGMTuXxffHQr6XsE-LHFsDsB8M-NChuQv7MhNyD4oUA';
    
    // URL API cần lấy dữ liệu
    $url = 'tto-production-db77.up.railway.app/api/admin/posts'; // Thay bằng URL API chính xác của bạn
        
        // Khởi tạo cURL để gọi API GET
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $token",
            "Accept: application/json"
        ]);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Cho phép cURL theo dõi chuyển hướng nếu có
        
        // Thực hiện yêu cầu GET
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Xử lý và hiển thị kết quả trả về từ API dưới dạng JSON
        if ($httpCode == 200) {
            header('Content-Type: application/json');
            echo json_encode(json_decode($response), JSON_PRETTY_PRINT);
        } else {
            echo "<p style='color: red;'>Lỗi: Không thể kết nối với API hoặc token không hợp lệ.</p>";
            echo "<p>Mã lỗi HTTP: $httpCode</p>";
        }
    // }
    ?>
</body>

</html>
