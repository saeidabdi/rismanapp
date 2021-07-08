@extends('admin.admin')

@section('content')
<div class="inner-block" dir="rtl">
    <div class="col-sm-12 col-md-12 col-lg-12 mb-60">
        <div class="horizontal-tab">
            <ul class="nav nav-tabs" dir="rtl" style="text-align: right;">
                <li @click="plan_id = ''" class="active"><a href="#tab1" data-toggle="tab" aria-expanded="true">ایجاد برنامه</a></li>
                <li class=""><a href="#tab2" data-toggle="tab" aria-expanded="false">برنامه ها</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab1">
                    <div class="col-md-4 right" style="padding-top: 10px;">
                        <div class="form-group">
                            <label class="label cat_lable">عنوان</label>
                            <input type="text" class="form-control" v-model="plan_title">
                        </div>
                    </div>
                    <div class="col-md-4 right" style="padding-top: 10px;">
                        <div class="form-group">
                            <label class="label cat_lable">وضعیت</label>
                            <select class="form-control" v-model="plan_status">
                                <option value="0">مشاور</option>
                                <option value="1">آماده</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 right" style="padding-top: 10px;">
                        <div class="form-group">
                            <label class="label cat_lable">برنامه مادر</label>
                            <select class="form-control" v-model="plan_mother">
                                <option value="0">بدون مادر</option>
                                <option v-for="plan in all_plan" v-if="plan.is_ready == plan_status  && !plan.is_end && plan.parent==plan_mother || plan.id==plan_mother" :value="plan.id">@{{plan.title}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 right" style="padding-top: 10px;">
                        <dropzone-component style="width: 100%;" @addrfilm="funcgetaddr"></dropzone-component>
                    </div>
                    <div class="col-md-4 right" style="padding-top: 10px;">
                        <div class="form-group">
                            <label class="label cat_lable">قیمت (تومان)</label>
                            <input type="text" class="form-control" v-model="plan_price">
                        </div>
                    </div>
                    <div class="col-md-2 right" style="padding-top: 10px;margin-top:20px;">
                        <div class="form-group">
                            <label for="checkbox1" class="label cat_lable">آخرین پارت</label>
                            <input id="checkbox1" type="checkbox" v-model="plan_isend" @change="()=>{plan_isexam=''}">
                        </div>
                    </div>
                    <div v-if="plan_isend" class="col-md-2 right" style="padding-top: 10px;margin-top:20px;">
                        <div class="form-group">
                            <label for="checkbox2" class="label cat_lable">آزمون</label>
                            <input id="checkbox2" type="checkbox" v-model="plan_isexam" @change="()=>{if(!plan_isexam){ file_addr='' } }">
                        </div>
                    </div>
                    <div v-if="plan_isexam" class="col-md-4 right" style="padding-top: 10px;">
                        <fileexam-component style="width: 100%;" @addrfilm="funcgetfile"></fileexam-component>
                    </div>
                    <div class="col-md-4 right list_button_insert">
                        <button style="margin-top: 42px;" type="button" class="btn btn-success hvr-buzz-out" @click="add_plan()">ایجاد برنامه</button>
                    </div>
                </div>
                <div class="tab-pane" id="tab2">
                    <table v-if="!plan_id" class="table table-striped table-bordered table-hover table-condensed">
                        <div v-if="!plan_id" class="col-md-4 right" style="padding-top: 10px;">
                            <div class="form-group">
                                <label class="label cat_lable">وضعیت</label>
                                <select class="form-control" v-model="plan_status">
                                    <option value="0">مشاور</option>
                                    <option value="1">آماده</option>
                                </select>
                            </div>
                        </div>
                        <div v-if="!plan_id" class="col-md-12 right" style="padding-top: 10px;">
                            <a @click="()=>{plan_mother=0}">برگشت</a>
                        </div>
                        <thead>
                            <th>ردیف</th>
                            <th>عنوان برنامه</th>
                            <th>قیمت</th>
                            <th>عکس</th>
                            <th>آخرین</th>
                            <th>آزمون</th>
                            <th>فایل</th>
                            <th>ویرایش</th>
                            <th>حذف</th>
                        </thead>
                        <tbody>
                            <tr v-for="plan in all_plan" v-if="plan.parent == plan_mother && plan.is_ready == plan_status">
                                <td>@{{plan.id}}</td>
                                <td v-if="!plan.is_end" @click="()=>{plan_mother = plan.id}">@{{plan.title}}</td>
                                <td v-if="plan.is_end">@{{plan.title}}</td>
                                <td>@{{plan.price}}</td>
                                <td><a :href="'images/'+plan.img"><img width="70" height="70" :src="'images/'+plan.img" alt=""></a></td>
                                <td v-if="plan.is_end">آخرین شاخه</td>
                                <td v-if="!plan.is_end"> </td>
                                <td v-if="plan.is_exam">آزمون</td>
                                <td v-if="!plan.is_exam"> </td>
                                <td v-if="plan.file"><a target="__blank" :href="'/'+plan.file">@{{plan.file.split('/')[1]}}</a></td>
                                <td v-if="!plan.file"> </td>
                                <td class="td_edit" @click="plan_edit(plan)"><i class="fa fa-edit"></i></td>
                                <td class="td_delete" @click="delete_plan(plan.id)"><i class="fa fa-trash"></i></td>
                            </tr>
                        </tbody>

                    </table>
                    <div v-if="plan_id">
                        <div class="col-md-4 right" style="padding-top: 10px;">
                            <div class="form-group">
                                <label class="label cat_lable">عنوان</label>
                                <input type="text" class="form-control" v-model="plan_title">
                            </div>
                        </div>
                        <div class="col-md-4 right" style="padding-top: 10px;">
                            <div class="form-group">
                                <label class="label cat_lable">وضعیت</label>
                                <select class="form-control" v-model="plan_status">
                                    <option value="0">مشاور</option>
                                    <option value="1">آماده</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 right" style="padding-top: 10px;">
                            <div class="form-group">
                                <label class="label cat_lable">برنامه مادر</label>
                                <select class="form-control" v-model="plan_mother">
                                    <option value="0">بدون مادر</option>
                                    <option v-for="plan in all_plan" v-if="plan.is_ready == plan_status  && !plan.is_end && plan.parent==plan_mother || plan.id==plan_mother" :value="plan.id">@{{plan.title}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 right" style="padding-top: 10px;">
                            <dropzone-component style="width: 100%;" @addrfilm="funcgetaddr"></dropzone-component>
                            <a :href="'/images/'+img_addr">نمایش عکس</a>
                        </div>
                        <div class="col-md-4 right" style="padding-top: 10px;">
                            <div class="form-group">
                                <label class="label cat_lable">قیمت (تومان)</label>
                                <input type="text" class="form-control" v-model="plan_price">
                            </div>
                        </div>
                        <div class="col-md-2 right" style="padding-top: 10px;margin-top:20px;">
                            <div class="form-group">
                                <label for="checkbox1" class="label cat_lable">آخرین پارت</label>
                                <input id="checkbox1" type="checkbox" v-model="plan_isend" @change="()=>{plan_isexam=''}">
                            </div>
                        </div>
                        <div v-if="plan_isend" class="col-md-2 right" style="padding-top: 10px;margin-top:20px;">
                            <div class="form-group">
                                <label for="checkbox2" class="label cat_lable">آزمون</label>
                                <input id="checkbox2" type="checkbox" v-model="plan_isexam">
                            </div>
                        </div>
                        <div v-if="plan_isexam" class="col-md-4 right" style="padding-top: 10px;">
                            <fileexam-component style="width: 100%;" @addrfilm="funcgetfile"></fileexam-component>
                        </div>
                        <div class="col-md-4 right list_button_insert">
                            <button style="margin-top: 42px;" type="button" class="btn btn-success hvr-buzz-out" @click="add_plan()">ویرایش برنامه</button>
                            <button style="margin-top: 42px;" type="button" class="btn btn-danger" @click="()=>{plan_id = ''}">برگشت</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"> </div>
</div>
@endsection