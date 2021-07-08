<template>
    <div class="container">
            <vue-dropzone 
            ref="myVueDropzone" 
            id="dropzone" 
            :options="dropzoneOptions"
            v-on:vdropzone-success="success"
            ></vue-dropzone>
    </div>
</template>
 
<script>
 
import vue2Dropzone from 'vue2-dropzone'
import 'vue2-dropzone/dist/vue2Dropzone.min.css'
  
    export default {
        components: {
        vueDropzone: vue2Dropzone
        },
        data: function () {
        return {
            dropzoneOptions: {
                url: '/formSubmit',
                // maxFiles: 1,
                // maxFilesize: 500000,
                addRemoveLinks: true,
                // acceptedFiles: ".mp4",
                dictDefaultMessage: "<i class='fa fa-cloud-upload'></i>آپلود عکس",
                headers: {
                "X-CSRF-TOKEN": document.head.querySelector("[name=csrf-token]").content
                }
            },
        }
        },
        mounted() {
            // console.log('Component mounted.')
        },
        methods:{
            success(file, response){
                console.log(response)
                this.$emit('addrfilm', response)
            }
        }
    }
</script>