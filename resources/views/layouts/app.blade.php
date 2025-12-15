<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Small Business - Start Bootstrap Template</title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="css/styles.css" rel="stylesheet" />
    </head>    
    <body>
        <div class="wrapper">
            <!-- Navbar (bu ham wrapper ichida bo'lishi kerak!) -->
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
                <div class="container px-5">
                    <a class="navbar-brand" href="#">Start Bootstrap</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                            <li class="nav-item"><a class="nav-link active" aria-current="page" href="#">Home</a></li>
                            <li class="nav-item"><a class="nav-link" href="#">About</a></li>
                            <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
                            <li class="nav-item"><a class="nav-link" href="#">Services</a></li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Sahifa kontenti -->
            <main class="container px-4 px-lg-5 content py-4">
                @yield('content')
            </main>

            <!-- Footer -->
            {{-- <footer class="py-5 bg-dark">
                <div class="container px-4 px-lg-5">
                    <p class="m-0 text-center text-white">Copyright &copy; Your Website 2023</p>
                </div>
            </footer> --}}
        </div>

        <!-- Bootstrap core JS-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Core theme JS-->
        <script src="js/scripts.js"></script>
        
        @stack('scripts')
    </body>
</html>
