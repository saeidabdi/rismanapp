<!DOCTYPE HTML>
<html>

<head>
    <title>پرداخت</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
    @if(isset($mes2))
    <div class="container">
        @if($result['Status'] == 100)
        <div class="alert alert-success" role="alert">
            {{$mes2}} شناسه : {{$result['RefID']}}
        </div>
        @else
        <div class="alert alert-danger" role="alert">
            {{$mes2}}
        </div>
        @endif
    </div>
    @endif
    <div class="container" style="  margin: 48px 8%;width: 84%;text-align: center;
    background: green;
    height: 300px;
    color: #fff;">
        <a href="tel:09100045125" style="display: inline-block;
    width: 100%;
    height: 100%;
    box-sizing: border-box;
    padding-top: 82px;
    color:#fff!important">
            {{$mes}}</a>
    </div>
    <!--scrolling js-->
    <!-- <script src="/as/js/jquery.nicescroll.js"></script>
    <script src="/as/js/scripts.js"></script> -->
    <!--//scrolling js-->
    <script src="/as/js/bootstrap.js"> </script>
    <!-- mother grid end here-->
    <!-- vue.js -->
    <!-- <script src="/js/app.js"></script> -->
</body>

</html>