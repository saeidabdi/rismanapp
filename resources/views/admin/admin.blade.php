@include('admin.header')

@yield('content')

<loading :active.sync="isLoading" 
         color="#fff"
         background-color="#000"
         loader="bars"></loading>
@include('admin.footer')