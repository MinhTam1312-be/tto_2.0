<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reminder</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #74ebd5, #9face6);
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #333;
        }
        .reminder-card {
            background: linear-gradient(135deg, #4a90e2, #9013fe);
            color: white;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            padding: 50px;
            max-width: 500px;
            text-align: center;
            transform: scale(1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .reminder-card:hover {
            transform: scale(1.05);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }
        .reminder-card h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);
        }
        .reminder-card p {
            font-size: 1.4rem;
            margin: 15px 0;
            line-height: 1.6;
        }
        .reminder-card .course-name {
            font-style: italic;
            font-weight: bold;
            font-size: 1.3rem;
            color: #ffe600;
        }
        .reminder-footer {
            margin-top: 20px;
            font-size: 1.1rem;
            color: #e0e7ff;
        }
        .btn-learn-now {
            margin-top: 30px;
            background: linear-gradient(135deg, #ff6f61, #ff9472);
            color: white;
            padding: 15px 40px;
            font-size: 1.3rem;
            border: none;
            border-radius: 10px;
            text-transform: uppercase;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 8px 15px rgba(255, 111, 97, 0.5);
            transition: background 0.3s ease, box-shadow 0.3s ease;
        }
        footer {
            margin-top: 20px;
            text-align: center;
            font-size: 1rem;
            color: #333;
        }
        @media (max-width: 768px) {
            .reminder-card {
                padding: 30px;
            }
            .reminder-card h2 {
                font-size: 2rem;
            }
            .reminder-card p {
                font-size: 1.2rem;
            }
            .btn-learn-now {
                font-size: 1.1rem;
                padding: 12px 30px;
            }
        }
    </style>
</head>
<body>
    <div class="reminder-card">
        <h2>{{ $fullname }}</h2>
        <p>Khóa học: <span class="course-name">{{ $course->name_course }}</span></p>
        <div class="reminder-footer">Hãy hoàn thành khóa học đúng hạn!</div>
        <!-- Thẻ <a> cho nút "Học ngay" -->
        <a href="https://www.tto.sh/learningCourse/{{ $course->slug_course }}" class="btn-learn-now" target="_blank">Học ngay</a>
    </div>

</body>
</html>
