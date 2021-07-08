
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
import Swal from 'sweetalert2'
import axios from 'axios';

window.Vue = require('vue');
// loding component
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

// persian date
import VuePersianDatetimePicker from 'vue-persian-datetime-picker';
Vue.component('date-picker', VuePersianDatetimePicker);

// checkeditor
import { VueEditor, Quill } from 'vue2-editor'
// import { ImageDrop } from 'quill-image-drop-module'
// import ImageResize from 'quill-image-resize-module'

// Quill.register('modules/imageDrop', ImageDrop)
// Quill.register('modules/imageResize', ImageResize)

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('example-component', require('./components/ExampleComponent.vue'));
Vue.component('dropzone-component', require('./components/DropzoneComponent.vue'));
Vue.component('fileexam-component', require('./components/FileExamComponent.vue'));

const app = new Vue({
    el: '#app',
    data: {
        isLoading: false, username: '', pass: '', logined: '', name: '', img_addr: '', message: '',
        all_stu: [], search_item: '', stu_id: '', weekly: [],
        paye_id: '', reshte_id: '', lesson_name: '', lesson_id: '', all_lesson: [],lesson_status:0,lesson_important:0,
        all_mosh: [], mosh_id: '',
        plan_title: '', plan_mother: 0, plan_status: '', plan_price: '', plan_isend: 0, plan_isexam: 0, all_plan: [], plan_id: '', file_addr: '',
        num_week: '', starttime: '', endtime: '',
        content: '<h1>Initial Content</h1>',
        editorSettings: {
          modules: {
            imageDrop: true,
            imageResize: {}
          }
        },
        // customModulesForEditor: [
        //   { alias: "imageDrop", module: ImageDrop },
        //   { alias: "imageResize", module: ImageResize }
        // ]
    },
    components: {
        Loading,
        VueEditor
    },
    mounted() {
        this.getuser();
    },
    methods: {
        handleImageAdded: function(file, Editor, cursorLocation, resetUploader) {
          // An example of using FormData
          // NOTE: Your key could be different such as:
          // formData.append('file', file)
     
          var formData = new FormData();
          formData.append("image", file);
     
          axios({
            url: "https://fakeapi.yoursite.com/images",
            method: "POST",
            data: formData
          })
            .then(result => {
              let url = result.data.url; // Get url from response
              Editor.insertEmbed(cursorLocation, "image", url);
              resetUploader();
            })
            .catch(err => {
              console.log(err);
            });
        },
        login() {
            this.isLoading = true;
            axios
                .post('/login', {
                    username: this.username,
                    pass: this.pass

                }).then(response => {
                    this.isLoading = false;
                    if (response.data.username != undefined) {
                        Swal.fire('', 'مدیر گرامی ' + response.data.name + ' شما وارد شدید', 'success');
                        location.href = "/dashbord";

                    } else {
                        Swal.fire('', 'کاربر وجود ندارد', 'warning');

                    }

                }, response => {
                    this.isLoading = false;
                    Swal.fire('', 'مشکل در اتصال به سرور', 'warning');
                });
        },
        getuser() {
            if (window.location.pathname != '/') {
                axios.get('/getuser').then(response => {
                    if (response.data.username != undefined) {
                        if (window.location.pathname == '/slider') {
                            this.get_imag_slide();
                            this.get_message();
                        }
                        if (window.location.pathname == '/stu') {
                            this.get_last_stu();
                        }
                        if (window.location.pathname == '/mosh') {
                            this.get_last_mosh();
                        }
                        if (window.location.pathname == '/plan') {
                            this.get_plan();
                        }
                        this.logined = 1;
                        this.username = response.data.username
                        this.name = response.data.name
                    } else {
                        location.href = '/';
                        this.logined = '';
                    }
                });
            }
        },
        exit_admin() {
            this.isLoading = true;
            axios
                .get('/exit_admin')
                .then(response => {
                    this.isLoading = false
                    location.href = "/";

                }, response => {
                    Swal.fire('', ' شما خارج شدید', 'success');
                });
        },
        // option
        funcaddrimg(val) {
            this.img_addr = val;
            this.get_imag_slide();
        },
        get_imag_slide() {
            this.isLoading = true
            axios
                .get('/get_imag_slide')
                .then(response => {
                    this.isLoading = false;
                    this.all_img_slider = response.data;
                })
        },
        slider_img(id) {
            this.isLoading = true
            axios
                .post('/slider_img', { id: id })
                .then(response => {
                    this.isLoading = false
                    Swal.fire('', response.data.mes, 'success');
                    this.get_imag_slide();
                })
        },
        get_message() {
            axios
                .get('/get_message')
                .then(response => {
                    this.message = response.data.vlaue;
                })
        },
        edit_message() {
            this.isLoading = true
            axios
                .post('/edit_message', { message: this.message })
                .then(response => {
                    this.isLoading = false
                    Swal.fire('', 'پیام بروزرسانی شد', 'success');
                })
        },
        // stu
        get_last_stu(ar = 0, mosh_id = 0) {
            if (ar) {
                this.isLoading = true
            }
            axios.post('/get_stu', {
                ar: ar,
                mosh_id: mosh_id,
            })
                .then(response => {
                    this.all_stu = response.data;
                    if (ar) {
                        this.isLoading = false
                        this.mosh_id = mosh_id;
                    }
                })

        },
        search_stu() {
            if (this.search_item) {
                this.isLoading = true
                axios
                    .post('/search_stu', {
                        name: this.search_item
                    })
                    .then(response => {
                        this.isLoading = false
                        this.all_stu = response.data;
                    })
            } else {

            }
        },
        // lesson
        add_lesson() {
            this.isLoading = true;
            this.lesson_status == true ? this.lesson_status=1 : this.lesson_status = 0;
            this.lesson_important == true ? this.lesson_important=1 : this.lesson_important = 0;
            axios
                .post('/add_lesson', {
                    name: this.lesson_name,
                    paye_id: this.paye_id,
                    reshte_id: this.reshte_id,
                    lesson_status : this.lesson_status,
                    lesson_important : this.lesson_important,
                    id: this.lesson_id
                }).then(response => {
                    this.isLoading = false
                    this.get_lesson();
                    Swal.fire('', response.data.mes, 'success')
                })
        },
        get_lesson(a = 0) {
            if (a == 1) {
                this.isLoading = true
                axios.post('/get_lesson', {
                    p_id: this.paye_id,
                    r_id: this.reshte_id,
                }).then(response => {
                    this.isLoading = false
                    this.all_lesson = response.data;
                })
            } else {
                axios.post('/get_lesson', {
                    p_id: this.paye_id,
                    r_id: this.reshte_id,
                }).then(response => {
                    this.all_lesson = response.data;
                })
            }
        },
        //mosh
        get_last_mosh() {
            axios.get('/get_mosh')
                .then(response => {
                    this.all_mosh = response.data;
                })

        },
        search_mosh() {
            if (this.search_item) {
                this.isLoading = true
                axios
                    .post('/search_mosh', {
                        name: this.search_item
                    })
                    .then(response => {
                        this.isLoading = false
                        this.all_mosh = response.data;
                    })
            } else {
                this.get_last_mosh();
            }
        },
        unactive_mosh(id) {
            this.isLoading = true;
            axios
                .post('/unactive_mosh', { id: id })
                .then(response => {
                    this.isLoading = false;
                    Swal.fire('', 'با موفقیت حذف شد', 'success')
                })
        },
        // planing
        funcgetaddr(val) {
            console.log(val)
            this.img_addr = val;
        },
        add_plan() {
            if (this.plan_title) {
                this.isLoading = true
                if (this.plan_isend) {
                    var plan_isend = 1;
                    if (this.plan_isexam) {
                        var plan_isexam = 1
                    } else {
                        var plan_isexam = 0;
                    }
                } else {
                    var plan_isend = 0;
                }
                axios
                    .post('/add_plan', {
                        title: this.plan_title,
                        parent: this.plan_mother,
                        is_ready: this.plan_status,
                        price: this.plan_price,
                        img: this.img_addr,
                        is_end: plan_isend,
                        plan_isexam: plan_isexam,
                        file_addr: this.file_addr,
                        id: this.plan_id,
                    }).then(response => {
                        this.isLoading = false;
                        this.get_plan();
                        Swal.fire('', response.data.mes, 'success')
                    })
            }
        },
        get_plan() {
            this.isLoading = true
            axios.get('/get_plan')
                .then(response => {
                    this.all_plan = response.data;
                    this.isLoading = false
                })
        },
        delete_plan(id) {
            this.isLoading = true;
            axios
                .post('/delete_plan', { id: id })
                .then(response => {
                    this.isLoading = false;
                    this.get_plan();
                    Swal.fire('', 'با موفقیت حذف شد', 'success')
                })
        },
        plan_edit(plan) {
            this.plan_id = plan.id;
            this.plan_title = plan.title;
            this.plan_status = plan.is_ready;
            this.plan_price = plan.price;
            this.img_addr = plan.img;
            this.plan_isend = plan.is_end;
            this.plan_isexam = plan.is_exam;
        },
        funcgetfile(val) {
            this.file_addr = val;
        },
        get_paln_stu(id) {
            this.isLoading = true
            axios.post('/get_paln_stu', {
                stu_id: id,
            }).then(response => {
                this.isLoading = false
                this.weekly = response.data;
                this.stu_id = id;
            })
        },
        // week
        add_week() {
            if (this.num_week && this.starttime && this.endtime) {
                this.isLoading = true;
                axios
                    .post('/add_week', {
                        num_week: this.num_week,
                        starttime: this.starttime,
                        endtime: this.endtime,
                    })
                    .then(response => {
                        this.isLoading = false;
                        Swal.fire('', 'هفته جدید ایجاد شد', 'success')
                    })
            }else{
                Swal.fire('', 'لطفا تمام فیلد ها را تکمیل کنید!!!', 'warning')
            }
        },
    }
});
