@extends('admin.admin')

@section('content')
<div class="inner-block" style="padding-top: 20px;">
	<div class="search-box" v-if="!mosh_id">
		<!-- <form> -->
		<input type="text" v-model="search_item" placeholder="جستوجو..." required="">
		<input type="submit" @click="search_mosh" value="">
		<!-- </form> -->
	</div>
	<div class="clearfix"> </div>
	<div class="chit-chat-layer1">
		<table v-if="all_mosh.length && !mosh_id" class="table table-striped table-bordered table-hover table-condensed col-md-12 saeid_block">
			<thead>
				<th>ردیف</th>
				<th>نام</th>
				<th>موبایل</th>
				<th>کد ملی</th>
				<th>کد مشاور</th>
				<th>عکس</th>
				<th>اعتبار کیف</th>
				<th>آیکن</th>
				<th>پیام</th>
				<th>دانش آموزان</th>
				<th>غیر فعال</th>
			</thead>
			<tbody>
				<tr v-for="stu in all_mosh">
					<td>@{{stu.id}}</td>
					<td>@{{stu.name}}</td>
					<td>@{{stu.mobile}}</td>
					<td>@{{stu.nation_code}}</td>
					<td>@{{stu.code}}</td>
					<td><a v-if="stu.img" :href="'images/'+stu.img"><img width="70" height="70" :src="'images/'+stu.img" alt=""></a></td>
					<td><a v-if="stu.logo" :href="'images/'+stu.logo"><img width="70" height="70" :src="'images/'+stu.logo" alt=""></a></td>
					<td>@{{stu.rest}}</td>
					<td>@{{stu.message}}</td>
					<td class="td_delete" @click="get_last_stu(1,stu.code)"><i class="fa fa-users"></i></td>
					<td class="td_delete" @click="unactive_mosh(stu.id)"><i class="fa fa-trash"></i></td>
				</tr>
			</tbody>

		</table>
		<div class="row col-md-12 top_back" v-if="mosh_id">
			<div class="" style="text-align: left;cursor: pointer;">
				<i class="fa fa-arrow-left" @click="()=>{mosh_id=''}"></i>
			</div>
		</div>
		<table v-if="all_stu.length && mosh_id" class="table table-striped table-bordered table-hover table-condensed col-md-12 saeid_block">
			<thead>
				<th>ردیف</th>
				<th>نام</th>
				<th>موبایل</th>
				<th>کد ملی</th>
				<th>مشاور</th>
				<th>پایه</th>
				<th>رشته</th>
				<th>عکس</th>
				<th>موجودی</th>
				<th>وضعیت</th>
			</thead>
			<tbody>
				<tr v-for="stu in all_stu">
					<td>@{{stu.id}}</td>
					<td>@{{stu.name}}</td>
					<td>@{{stu.mobile}}</td>
					<td>@{{stu.nation_code}}</td>
					<td>@{{stu.mosh_id}}</td>
					<td v-if="stu.base_id == 0">نهم</td>
					<td v-if="stu.base_id == 1">دهم</td>
					<td v-if="stu.base_id == 2">یازدهم</td>
					<td v-if="stu.base_id == 3">دوازدهم</td>
					<td v-if="!stu.base_id"> </td>
					<td v-if="stu.r_id == 0">ریاضی فیزیک</td>
					<td v-if="stu.r_id == 1">تجربی</td>
					<td v-if="stu.r_id == 2">انسانی</td>
					<td v-if="stu.img"><a :href="'images/'+stu.img"><img width="70" height="70" :src="'images/'+stu.img" alt=""></a></td>
					<td v-if="!stu.img"> </td>
					<td>@{{stu.rest}}</td>
					<td v-if="stu.status == 0">ثبت شده</td>
					<td v-if="stu.status == 1">اهراز هویت</td>
					<td v-if="stu.status == 2">اطلاعات اولیه</td>
					<td v-if="stu.status == 3">فعال</td>
				</tr>
			</tbody>

		</table>
	</div>
</div>
@endsection