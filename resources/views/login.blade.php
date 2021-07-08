<!DOCTYPE HTML>
<html>

<head>
    <title>ورود مدیر</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <script type="application/x-javascript">
        addEventListener("load", function() {
            setTimeout(hideURLbar, 0);
        }, false);

        function hideURLbar() {
            window.scrollTo(0, 1);
        }
    </script>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <link href="/as/css/bootstrap.css" rel="stylesheet" type="text/css" media="all">
    <!-- Custom Theme files -->
    <link href="/as/css/style.css" rel="stylesheet" type="text/css" media="all" />
    <!--js-->
    <script src="/as/js/jquery-2.1.1.min.js"></script>
    <!--icons-css-->
    <link href="/as/css/font-awesome.css" rel="stylesheet">
</head>

<body>
    <div id="app" v-clock>
        <div class="login-page">
            <div class="login-main">
                <div class="login-head">
                    <h1>ورود مدیر</h1>
                </div>
                <div class="login-block">
                    <input type="text" v-model="username" placeholder="نام کاربری" required="">
                    <input type="password" v-model="pass" class="lock" placeholder="کلمه عبور">
                    <div class="forgot-top-grids">
                    </div>
                    <input type="submit" @click="login" value="ورود">
                </div>
            </div>
        </div>
        <loading :active.sync="isLoading" color="#fff" background-color="#000" loader="dots"></loading>
    </div>
    <!--scrolling js-->
    <!-- <script src="/as/js/jquery.nicescroll.js"></script>
    <script src="/as/js/scripts.js"></script> -->
    <!--//scrolling js-->
    <script src="/as/js/bootstrap.js"> </script>
    <!-- mother grid end here-->
    <!-- vue.js -->
    <script src="/js/app.js"></script>
</body>

</html>