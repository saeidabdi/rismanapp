@extends('admin.admin')

@section('content')
<div class="inner-block">
    <div class="col-sm-12 col-md-12 col-lg-12 mb-60">
        <div class="horizontal-tab">
            <ul class="nav nav-tabs" dir="rtl" style="text-align: right;">
                <li class="active"><a href="#tab1" data-toggle="tab" aria-expanded="true">ایجاد هفته</a></li>
                <li class=""><a href="#tab2" data-toggle="tab" aria-expanded="false">هفته ها</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab1">
                    <div class="col-md-4 right" style="padding-top: 10px;">
                        <div class="form-group">
                            <label class="label cat_lable">شماره هفته</label>
                            <input type="number" class="form-control" v-model="num_week">
                        </div>
                    </div>
                    <div class="col-md-4 right" style="padding-top: 10px;">
                        <div class="form-group">
                            <label class="label cat_lable">تاریخ هفته</label>
                            <date-picker v-model="starttime" type="datetime" :min="nowdateTime"></date-picker>
                            <date-picker v-model="endtime" :disabled="!starttime" :min="starttime" type="datetime"></date-picker>
                        </div>
                    </div>
                    <div class="col-md-4 right list_button_insert">
                        <button style="margin-top: 42px;" type="button" class="btn btn-success" @click="add_week()">ایجاد هفته</button>
                    </div>
                </div>
                <div class="tab-pane" id="tab2">

                    <div class="chit-chat-layer1">
                        <table class="table table-striped table-bordered table-hover table-condensed col-md-12 saeid_block">
                            <thead>
                                <th>ردیف</th>
                                <th>نام هفته</th>
                                <th>شروع</th>
                                <th>پایان</th>
                                <th>سال</th>
                                <th>ویرایش</th>
                            </thead>
                            <tbody>
                                @foreach($week as $key =>$val )
                                <tr>
                                    <td>{{$val->id}}</td>
                                    <td>{{$val->num}}</td>
                                    <td>{{$val->start}}</td>
                                    <td>{{$val->end}}</td>
                                    <td>{{$val->year}}</td>
                                    <td class="td_delete"><i class="fa fa-edit"></i></td>
                                    <!-- <td class="td_delete" @click="slider_img(stu.id)"><i class="fa fa-trash"></i></td> -->
                                </tr>
                                @endforeach
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"> </div>
</div>
@endsection