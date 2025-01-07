<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    {{-- <link rel="stylesheet" href="./style.css"> --}}
</head>

<body>
    <form action="{{ route('momo.payUrl', ['course_id' => '01JFJ7Y6BK7WGY0NN1PW5H1QR1', 'course_price' => 99000]) }}" method="post">
        @csrf
        <button type="submit" name="payUrl">MOMO</button>
    </form>
    <form action="{{ route('vnpay.redirect', ['course_id' => '01JFSRQVP6Q3M0D69JB2F2B04G', 'course_price' => 10000]) }}" method="post">
        @csrf
        <button type="submit" name="redirect">VNPay</button>
    </form>


    <hr>
    {{-- <div class="form-group row mb-0">
        <div class="col-md-8 offset-md-4">
            <a href="{{ route('api.login.google', 'google') }}" class="btn btn-primary">
                <i class="fa fa-google"></i> Google
            </a>
        </div>
    </div> --}}
    {{-- <hr>
    <div class="form-group row mb-0">
        <div class="col-md-8 offset-md-4">
            <a href="{{ route('login.github', 'github') }}" class="btn btn-primary">
                <i class="fa fa-google"></i> github
            </a>
        </div>
    </div> --}}
    {{-- <hr>
    <div class="form-group row mb-0">
        <div class="col-md-8 offset-md-4">
            <a href="{{ route('api.login.facebook', 'facebook') }}" class="btn btn-primary">
                <i class="fa fa-google"></i> facebook
            </a>
        </div>
    </div> --}}

    <nav class=" navbar background">
        <ul class="nav-list">
            <div class="logo">
                <img
                    src=" https://media.licdn.com/dms/image/C4D0BAQEwg5FK93uumQ/company-logo_200_200/0/1519923012279?e=2147483647&v=beta&t=63CNoS8OTR4lHjPhHSO7eFFqwLGwYunWfyDBV3tdc0c">
            </div>
            <li><a href="#web">Nguyễn Minh Tâm</a></li>
            <li><a href="#program">C Programming</a></li>
            <li><a href="#course">Courses</a></li>
        </ul>

        <div class="rightNav">
            <input type="text" name="search" id="search">
            <button class="btn btn-sm">Search</button>
        </div>
    </nav>

    <section class=" first section">
        <div class="box-main">
            <div class="firstHalf">
                <h1 class="text-big" id="web">
                    Web Technology
                </h1>

                <p class="text-small">
                    HTML referred to as HyperText Markup
                    Language. It is the most widely used language that is used to develop a webpage. it was created by
                    Berner-Lee in the year of 1991. The first standard version of HTML is HTML 2.0 .It was launched in
                    the year of 1995. The major version of HTML is HTML5 which was launched in the year of 1999. Now we
                    are using the latest version of HTML, which is HTML5. With the help of HTML, we can create a website
                    and become web developers.
                </p>


            </div>
        </div>
    </section>

    <section class= "second section">
        <div class="box-main">
            <div class="secondHalf">
                <h1 class="text-big" id="program">
                    C Programming
                </h1>
                <p class="text-small">
                    C is a powerful general-purpose programming language developed at AT & T's Bell Laboratories of USA
                    in 1972.It was designed and written by Dennis Ritchie. C become popular because it is reliable,
                    simple, and easy to use.C Programming used to develop software like operating systems, databases,
                    compilers, and so on. C programming is an excellent language to learn to program for beginners.
                    Although numerous computer languages are used for writing computer applications, the computer
                    programming language, C, is the most popular language worldwide. Everything from microcontrollers to
                    operating systems is written in C since it's very flexible and versatile, allowing maximum control
                    with minimal commands. If you are interested in a career in computer programming, it would be wise
                    to start by learning the C programming language.
                </p>


            </div>
        </div>
    </section>

    <section class="section">
        <div class="paras">
            <h1 class="sectionTag text-big">Java</h1>

            <p class= "sectionSubTag text-small">
                Java is the one of the most
                popular programming language
                for many years. Java is also known as Object
                Oriented Programming Language. But we cannot say that Java is not
                considered as pure object-oriented
                as it provides support for primitive
                data types (like int, char, etc) The
                Java codes are first compiled into byte
                code (machine-independent code). Then
                the byte code is run on Java Virtual
                Machine (JVM), regardless of the
                underlying architecture.
            </p>


        </div>

        <div class="thumbnail">
            <img src="https://wallpapers.com/images/featured/murjp1nk4lp1idlt.jpg" alt="laptop image">
        </div>
    </section>

    <footer class="background">
        <p class="text-footer">
            Copyright ?-All rights are reserved
        </p>


    </footer>
    <script>
        fetch('https://tto-production-db77.up.railway.app/api/auth/login-google', {
                method: 'GET',
                headers: {
                    'Origin': 'http://127.0.0.1:4000' // CORS sẽ kiểm tra origin này,
                },
                // mode: 'no-cors',
            })
            .then(response => {
                if (response.ok) {
                    console.log('CORS configured correctly:', response);
                } else {
                    console.error('CORS issue detected:', response);
                }
            })
            .catch(error => console.error('Fetch error:', error));
    </script>
</body>

</html>
