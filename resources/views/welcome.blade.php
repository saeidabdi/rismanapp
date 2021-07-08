<!DOCTYPE>
<html lang="fa">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>{{$appname}}</title>
    <!-- <link href="/as/css/fontiran.css" rel="stylesheet" media="all"  type='text/css'/> -->
</head>

<body dir="rtl">
    <style>
        @page {
            header: page-header;
            footer: page-footer;
            content: page-content;
        }
    </style>
    <!-- // for header : -->
    @if(isset($notready))
    <!-- for mosharver -->
    <htmlpageheader name="page-header">
        <div>
            @if(isset($isexam))
            <p style="font-family: 'farsi';float: right;width: 70%;">برنامه آزمون مشاور {{$mosh}} مخصوص دانش آموز <span style="color: red;">{{$name}}</span></p>
            @else
            <p style="font-family: 'farsi';float: right;width: 70%;">برنامه هفتگی مشاور {{$mosh}} مخصوص دانش آموز <span style="color: red;">{{$name}}</span></p>
            @endif
            <p style="font-family: 'farsi';float: left;width: 25%;text-align: left;">تاریخ : {{$date}}</p>
        </div>

    </htmlpageheader>
    <hr><br>
    <htmlpagecontent name="page-content">
        @if($isexam==1)
        <table style="font-family: 'farsi';width: 100%;text-align: center;margin-top: 18px;background: #ccc;">
            <tbody>
                <tr style="background: #bbb;font-weight: bold;">
                    <td height="60px">روز</td>
                    <td>بخش اول</td>
                    <td>بخش دوم</td>
                    <td>بخش سوم</td>
                    <td>بخش چهارم</td>
                    <td>بخش پنجم</td>
                    <td style="width: 16%;">نان شب</td>
                </tr>
                <tr style="background: #fff;height: 35px;">
                    <td height="55px" style="font-weight: 500px;">اول</td>
                    @foreach($data[0] as $key => $value)
                    <td>{{ $value }} <br>
                        <span style="font-size: 10px;color:#888">{{ $desc[0][$key] }}</span>
                    </td>
                    @endforeach
                </tr>
                <tr style="background: #fff;height: 35px;">
                    <td height="55px" style="font-weight: 500px;">دوم</td>
                    @foreach($data[1] as $key => $value)
                    <td>{{ $value }} <br>
                        <span style="font-size: 10px;color:#888">{{ $desc[1][$key] }}</span>
                    </td>
                    @endforeach
                </tr>
                <tr style="background: #fff;height: 35px;">
                    <td height="55px" style="font-weight: 500px;">سوم</td>
                    @foreach($data[2] as $key => $value)
                    <td>{{ $value }} <br>
                        <span style="font-size: 10px;color:#888">{{ $desc[2][$key] }}</span>
                    </td>
                    @endforeach
                </tr>
                <tr style="background: #fff;height: 35px;">
                    <td height="55px" style="font-weight: 500px;">چهارم</td>
                    @foreach($data[3] as $key => $value)
                    <td>{{ $value }} <br>
                        <span style="font-size: 10px;color:#888">{{ $desc[3][$key] }}</span>
                    </td>
                    @endforeach
                </tr>
                <tr style="background: #fff;height: 35px;">
                    <td height="55px" style="font-weight: 500px;">پنجم</td>
                    @foreach($data[4] as $key => $value)
                    <td>{{ $value }} <br>
                        <span style="font-size: 10px;color:#888">{{ $desc[4][$key] }}</span>
                    </td>
                    @endforeach
                </tr>
                <tr style="background: #fff;height: 35px;">
                    <td height="55px" style="font-weight: 500px;">ششم</td>
                    @foreach($data[5] as $key => $value)
                    <td>{{ $value }} <br>
                        <span style="font-size: 10px;color:#888">{{ $desc[5][$key] }}</span>
                    </td>
                    @endforeach
                </tr>
                <tr style="background: #fff;height: 35px;">
                    <td height="55px" style="font-weight: 500px;">هفتم</td>
                    @foreach($data[6] as $key => $value)
                    <td>{{ $value }} <br>
                        <span style="font-size: 10px;color:#888">{{ $desc[6][$key] }}</span>
                    </td>
                    @endforeach
                </tr>
                <tr style="background: #fff;height: 35px;">
                    <td height="55px" style="font-weight: 500px;">هشتم</td>
                    @foreach($data[7] as $key => $value)
                    <td>{{ $value }} <br>
                        <span style="font-size: 10px;color:#888">{{ $desc[7][$key] }}</span>
                    </td>
                    @endforeach
                </tr>
                <tr style="background: #fff;height: 35px;">
                    <td height="55px" style="font-weight: 500px;">نهم</td>
                    @foreach($data[8] as $key => $value)
                    <td>{{ $value }} <br>
                        <span style="font-size: 10px;color:#888">{{ $desc[8][$key] }}</span>
                    </td>
                    @endforeach
                </tr>
                <tr style="background: #fff;height: 35px;">
                    <td height="55px" style="font-weight: 500px;">دهم</td>
                    @foreach($data[9] as $key => $value)
                    <td>{{ $value }} <br>
                        <span style="font-size: 10px;color:#888">{{ $desc[9][$key] }}</span>
                    </td>
                    @endforeach
                </tr>
                <tr style="background: #fff;height: 35px;">
                    <td height="55px" style="font-weight: 500px;">یازدهم</td>
                    @foreach($data[10] as $key => $value)
                    <td>{{ $value }} <br>
                        <span style="font-size: 10px;color:#888">{{ $desc[10][$key] }}</span>
                    </td>
                    @endforeach
                </tr>
                <tr style="background: #fff;height: 35px;">
                    <td height="55px" style="font-weight: 500px;">دوازدهم</td>
                    @foreach($data[11] as $key => $value)
                    <td>{{ $value }} <br>
                        <span style="font-size: 10px;color:#888">{{ $desc[11][$key] }}</span>
                    </td>
                    @endforeach
                </tr>
                <tr style="background: #fff;height: 35px;">
                    <td height="55px" style="font-weight: 500px;">سیزدهم</td>
                    @foreach($data[12] as $key => $value)
                    <td>{{ $value }} <br>
                        <span style="font-size: 10px;color:#888">{{ $desc[12][$key] }}</span>
                    </td>
                    @endforeach
                </tr>
                <tr style="background: #fff;height: 35px;">
                    <td height="55px" style="font-weight: 500px;">چهاردهم</td>
                    @foreach($data[13] as $key => $value)
                    <td>{{ $value }} <br>
                        <span style="font-size: 10px;color:#888">{{ $desc[13][$key] }}</span>
                    </td>
                    @endforeach
                </tr>
            </tbody>

        </table>
        @else
        <table style="font-family: 'farsi';width: 100%;text-align: center;margin-top: 18px;background: #ccc;">
            <tbody>
                <tr style="background: #bbb;font-weight: bold;">
                    <td height="60px"></td>
                    <td>بخش اول</td>
                    <td>بخش دوم</td>
                    <td>بخش سوم</td>
                    <td>بخش چهارم</td>
                    <td>بخش پنجم</td>
                    <td style="width: 16%;">نان شب</td>
                </tr>
                <tr style="background: #fff;height: 35px;">
                    <td height="55px" style="font-weight: 500px;">شنبه</td>
                    @foreach($data[0] as $key => $value)
                    <td>{{ $value }} <br>
                        <span style="font-size: 10px;color:#888">{{ $desc[0][$key] }}</span>
                    </td>
                    @endforeach
                </tr>
                <tr style="background: #fff;height: 35px;">
                    <td height="55px" style="font-weight: 500px;">یک شنبه</td>
                    @foreach($data[1] as $key => $value)
                    <td>{{ $value }} <br>
                        <span style="font-size: 10px;color:#888">{{ $desc[1][$key] }}</span>
                    </td>
                    @endforeach
                </tr>
                <tr style="background: #fff;height: 35px;">
                    <td height="55px" style="font-weight: 500px;">دوشنبه</td>
                    @foreach($data[2] as $key => $value)
                    <td>{{ $value }} <br>
                        <span style="font-size: 10px;color:#888">{{ $desc[2][$key] }}</span>
                    </td>
                    @endforeach
                </tr>
                <tr style="background: #fff;height: 35px;">
                    <td height="55px" style="font-weight: 500px;">سه شنبه</td>
                    @foreach($data[3] as $key => $value)
                    <td>{{ $value }} <br>
                        <span style="font-size: 10px;color:#888">{{ $desc[3][$key] }}</span>
                    </td>
                    @endforeach
                </tr>
                <tr style="background: #fff;height: 35px;">
                    <td height="55px" style="font-weight: 500px;">چهارشنبه</td>
                    @foreach($data[4] as $key => $value)
                    <td>{{ $value }} <br>
                        <span style="font-size: 10px;color:#888">{{ $desc[4][$key] }}</span>
                    </td>
                    @endforeach
                </tr>
                <tr style="background: #fff;height: 35px;">
                    <td height="55px" style="font-weight: 500px;">جمعه</td>
                    @foreach($data[5] as $key => $value)
                    <td>{{ $value }} <br>
                        <span style="font-size: 10px;color:#888">{{ $desc[5][$key] }}</span>
                    </td>
                    @endforeach
                </tr>
            </tbody>

        </table>
        @endif
    </htmlpagecontent>
    <htmlpagefooter name="page-footer">
        <hr>
        <p style="font-size: 14px;color: #555;font-family: 'farsi';text-align: center;">نیرو گرفته از <a style="color: blue;text-decoration: none;" href="#">نرم افزار {{$appname}}</a></p>
    </htmlpagefooter>
    @else
    <!-- for ready -->
    <htmlpageheader name="page-header">
        <div>
            <p style="font-family: 'farsi';float: right;width: 70%;">برنامه هفتگی آماده مخصوص دانش آموز <span style="color: red;">{{$name}}</span></p>
            <p style="font-family: 'farsi';float: left;width: 25%;text-align: left;">تاریخ : {{$date}}</p>
        </div>

    </htmlpageheader>
    <hr><br>
    <htmlpagecontent name="page-content">
        <table style="font-family: 'farsi';width: 100%;text-align: center;margin-top: 18px;background: #ccc;">
            <tbody>
                <tr style="background: #bbb;font-weight: bold;">
                    <td height="60px"></td>
                    <td>بخش اول</td>
                    <td>بخش دوم</td>
                    <td>بخش سوم</td>
                    <td>بخش چهارم</td>
                    <td>بخش پنجم</td>
                    <td style="width: 16%;">نان شب</td>
                </tr>
                <tr style="background: #fff;height: 35px;">
                    <td height="55px" style="font-weight: 500px;">شنبه</td>
                    @foreach($data[0] as $key => $value)
                    @if(!empty($value->title))
                    @if($value->status)
                    <td>
                        <div>
                            {{$value->title}} <br>
                            <span style="font-size: 10px;color:#888;">(1:30)</span>
                        </div>
                    </td>
                    @else
                    <td>{{$value->title}} <br><span style="font-size: 10px;color:#888">(1:15)</span></td>
                    @endif
                    @else
                    <td> </td>
                    @endif
                    @endforeach
                    <td></td>
                </tr>
                <tr style="background: #eee;height: 35px;">
                    <td height="55px">یک شنبه</td>
                    @foreach($data[1] as $key => $value)
                    @if(!empty($value->title))
                    @if($value->status)
                    <td>
                        <div>
                            {{$value->title}} <br>
                            <span style="font-size: 10px;color:#888;">(1:30)</span>
                        </div>
                    </td>
                    @else
                    <td>{{$value->title}} <br><span style="font-size: 10px;color:#888">(1:15)</span></td>
                    @endif
                    @else
                    <td> </td>
                    @endif
                    @endforeach
                    <td></td>
                </tr>
                <tr style="background: #fff;height: 35px;">
                    <td height="55px">دوشنبه</td>
                    @foreach($data[2] as $key => $value)
                    @if(!empty($value->title))
                    @if($value->status)
                    <td>
                        <div>
                            {{$value->title}} <br>
                            <span style="font-size: 10px;color:#888;">(1:30)</span>
                        </div>
                    </td>
                    @else
                    <td>{{$value->title}} <br><span style="font-size: 10px;color:#888">(1:15)</span></td>
                    @endif
                    @else
                    <td> </td>
                    @endif
                    @endforeach
                    <td></td>
                </tr>
                <tr style="background: #eee;height: 35px;">
                    <td height="55px">سه شنبه</td>
                    @foreach($data[3] as $key => $value)
                    @if(!empty($value->title))
                    @if($value->status)
                    <td>
                        <div>
                            {{$value->title}} <br>
                            <span style="font-size: 10px;color:#888;">(1:30)</span>
                        </div>
                    </td>
                    @else
                    <td>{{$value->title}} <br><span style="font-size: 10px;color:#888">(1:15)</span></td>
                    @endif
                    @else
                    <td> </td>
                    @endif
                    @endforeach
                    <td></td>
                </tr>
                <tr style="background: #fff;height: 35px;">
                    <td height="55px">چهار شنبه</td>
                    @foreach($data[4] as $key => $value)
                    @if(!empty($value->title))
                    @if($value->status)
                    <td>
                        <div>
                            {{$value->title}} <br>
                            <span style="font-size: 10px;color:#888;">(1:30)</span>
                        </div>
                    </td>
                    @else
                    <td>{{$value->title}} <br><span style="font-size: 10px;color:#888">(1:15)</span></td>
                    @endif
                    @else
                    <td> </td>
                    @endif
                    @endforeach
                    <td></td>
                </tr>
                <tr style="background: #eee;height: 35px;">
                    <td height="55px">پنج شنبه</td>
                    @foreach($data[5] as $key => $value)
                    @if(!empty($value->title))
                    @if($value->status)
                    <td>
                        <div>
                            {{$value->title}} <br>
                            <span style="font-size: 10px;color:#888;">(1:30)</span>
                        </div>
                    </td>
                    @else
                    <td>{{$value->title}} <br><span style="font-size: 10px;color:#888">(1:15)</span></td>
                    @endif
                    @else
                    <td> </td>
                    @endif
                    @endforeach
                    <td></td>
                </tr>
                <tr style="background: #fff;height: 35px;">
                    <td height="55px">جمعه</td>
                    @foreach($data[6] as $key => $value)
                    @if(!empty($value->title))
                    @if($value->status)
                    <td>
                        <div>
                            {{$value->title}} <br>
                            <span style="font-size: 10px;color:#888;">(1:30)</span>
                        </div>
                    </td>
                    @else
                    <td>{{$value->title}} <br><span style="font-size: 10px;color:#888">(1:15)</span></td>
                    @endif
                    @else
                    <td> </td>
                    @endif
                    @endforeach
                    <td></td>
                </tr>
            </tbody>

        </table>
    </htmlpagecontent>
    <htmlpagefooter name="page-footer">
        <hr>
        <p style="font-size: 14px;color: #555;font-family: 'farsi';text-align: center;">ایجاد شده توسط <a style="color: blue;text-decoration: none;" href="#">نرم افزار {{$appname}}</a></p>
    </htmlpagefooter>
    @endif
</body>

</html>