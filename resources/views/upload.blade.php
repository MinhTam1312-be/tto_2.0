<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Image</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f8ff; /* Light blue background */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        h2 {
            color: #0066cc;
            margin-bottom: 20px;
        }

        .upload-container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .upload-container input[type="file"] {
            padding: 10px;
            border: 1px solid #c0c0c0;
            border-radius: 10px;
            width: 100%;
            margin-bottom: 15px;
            box-sizing: border-box; /* Fix input stretching */
            font-size: 16px;
        }

        .upload-container button {
            background-color: #0066cc;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .upload-container button:hover {
            background-color: #004d99;
        }

        .success-message {
            color: #28a745;
            margin-bottom: 15px;
        }

        .error-message {
            color: #dc3545;
            margin-bottom: 15px;
        }

        img {
            max-width: 100%;
            border-radius: 10px;
            margin-top: 15px;
        }
    </style>
</head>
<body>

<div class="upload-container">
    <h2>UPLOAD IMAGE FOR TTO</h2>

    @if (session('success'))
        <div class="success-message">
            <strong>{{ session('success') }}</strong>
        </div>
        @if (session('image'))
            <img src="{{ session('image') }}" alt="Uploaded Image">
        @endif
    @endif

    <form action="{{ route('upload.image') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div>
            <input type="file" name="image" id="image">
        </div>

        @if ($errors->has('image'))
            <div class="error-message">{{ $errors->first('image') }}</div>
        @endif

        <div>
            <button type="submit">Upload</button>
        </div>
    </form>
</div>

</body>
</html>