<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Sheets Integration</title>
    <!-- Thêm Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center text-primary">Thêm Dữ Liệu vào Google Sheets</h1>

        <!-- Form nhập liệu -->
        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <form action="{{ route('addData') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Tên</label>
                        <input type="text" name="name" class="form-control" placeholder="Nhập tên" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Nhập email" required>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Nội dung</label>
                        <input type="text" name="content" class="form-control" placeholder="Nhập nội dung" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Thêm Dữ Liệu</button>
                </form>
            </div>
        </div>

        <!-- Hiển thị thông báo -->
        @if(session('message'))
            <div class="alert alert-info mt-4">
                {{ session('message') }}
            </div>
        @endif

        <!-- Hiển thị dữ liệu đã nhập -->
        @if(!empty($data))
            <h2 class="text-center text-success mt-4">Dữ liệu vừa thêm</h2>
            <div class="table-responsive mt-3">
                <table class="table table-bordered table-striped">
                    <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>Tên</th>
                            <th>Email</th>
                            <th>Nội dung</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $row)
                            <tr>
                                <td>{{ $row[0] }}</td>
                                <td>{{ $row[1] }}</td>
                                <td>{{ $row[2] }}</td>
                                <td>{{ $row[3] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Thêm Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
