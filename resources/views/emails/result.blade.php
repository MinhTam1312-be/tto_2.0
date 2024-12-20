<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả thay đổi email</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center bg-light" style="height: 100vh;">

    <div class="card text-center shadow-lg" style="width: 24rem;">
        <div class="card-header {{ $status === 'success' ? 'bg-success text-white' : 'bg-danger text-white' }}">
            <h5 class="mb-0">{{ $status === 'success' ? 'Thành Công' : 'Thất Bại' }}</h5>
        </div>
        <div class="card-body">
            <p class="card-text fs-5">
                {{ $message }}
            </p>
            <a href="https://www.tto.sh/home" class="btn btn-primary">Quay lại trang chủ</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>