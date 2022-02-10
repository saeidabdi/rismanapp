@extends('admin.admin')

@section('content')
<div class="inner-block" dir="rtl">
    <div class="col-sm-12 col-md-12 col-lg-12 mb-60">
        <div class="horizontal-tab">
            <ul class="nav nav-tabs" dir="rtl" style="text-align: right;">
                <li class="active"><a href="#tab1" data-toggle="tab" aria-expanded="true">ایجاد درس</a></li>
                <li class=""><a href="#tab2" data-toggle="tab" aria-expanded="false">درس ها</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab1">
                    <div class="col-md-4 right" style="padding-top: 10px;">
                        <div class="form-group">
                            <label class="label cat_lable">درس</label>
                            <input type="text" class="form-control" v-model="lesson_name">
                        </div>
                    </div>
                    <div class="col-md-4 right" style="padding-top: 10px;">
                        <div class="form-group">
                            <label class="label cat_lable">پایه</label>
                            <select class="form-control" v-model="paye_id" @change="()=>{reshte_id=''}">
                                <option value="4">هفتم</option>
                                <option value="5">هشتم</option>
                                <option value="0">نهم</option>
                                <option value="1">دهم</option>
                                <option value="2">یازدهم</option>
                                <option value="3">دوازدهم</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 right" style="padding-top: 10px;">
                        <div class="form-group">
                            <label class="label cat_lable">تخصصی</label>
                            <input type="checkbox" class="" v-model="lesson_status">
                        </div>
                    </div>
                    <div class="col-md-4 right" style="padding-top: 10px;">
                        <div class="form-group">
                            <label class="label cat_lable">درس مهم</label>
                            <input type="checkbox" class="" v-model="lesson_important">
                        </div>
                    </div>
                    <div v-if="paye_id != 0 && paye_id != 4 && paye_id != 5" class="col-md-4 right" style="padding-top: 10px;">
                        <div class="form-group">
                            <label class="label cat_lable">رشته</label>
                            <select class="form-control" v-model="reshte_id">
                                <option value="0">ریاضی فیزیک</option>
                                <option value="1">تجربی</option>
                                <option value="2">انسانی</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 right list_button_insert">
                        <button style="margin-top: 42px;" type="button" class="btn btn-success" @click="add_lesson()">ایجاد درس</button>
                    </div>
                </div>
                <div class="tab-pane" id="tab2">
                    <table v-if="!lesson_id" class="table table-striped table-bordered table-hover table-condensed">
                        <div class="col-md-4 right" style="padding-top: 10px;">
                            <div class="form-group">
                                <label class="label cat_lable">پایه</label>
                                <select class="form-control" v-model="paye_id" @change="()=>{reshte_id=''}">
                                    <option value="4">هفتم</option>
                                    <option value="5">هشتم</option>
                                    <option value="0">نهم</option>
                                    <option value="1">دهم</option>
                                    <option value="2">یازدهم</option>
                                    <option value="3">دوازدهم</option>
                                </select>
                            </div>
                        </div>
                        <div v-if="paye_id != 0 && paye_id != 4 && paye_id != 5" class="col-md-4 right" style="padding-top: 10px;">
                            <div class="form-group">
                                <label class="label cat_lable">رشته</label>
                                <select class="form-control" v-model="reshte_id">
                                    <option value="0">ریاضی فیزیک</option>
                                    <option value="1">تجربی</option>
                                    <option value="2">انسانی</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 right list_button_insert">
                            <button style="margin-top: 42px;" type="button" class="btn btn-success" @click="get_lesson(1)">ایجاد درس</button>
                        </div>
                        <thead>
                            <th>ردیف</th>
                            <th>درس</th>
                            <th>ویرایش</th>
                            <th>حذف</th>
                        </thead>
                        <tbody>
                            <tr v-for="lesson in all_lesson">
                                <td>@{{lesson.id}}</td>
                                <td>@{{lesson.title}}</td>
                                <td class="td_edit" @click="lesson_edit(lesson)"><i class="fa fa-edit"></i></td>
                                <td class="td_delete" @click="delete_lesson(lesson.id)"><i class="fa fa-trash"></i></td>
                            </tr>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"> </div>
</div>
@endsection