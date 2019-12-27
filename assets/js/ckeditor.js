'use strict';

import $ from 'jquery';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import '@ckeditor/ckeditor5-build-classic/build/translations/ru.js';

$('body').append('<script type="text/javascript" src="/plugins/ckfinder/ckfinder.js"></script>');

ClassicEditor
    .create( document.querySelector( '.editor' ), {
        ckfinder: {
            uploadUrl: '/plugins/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files&responseType=json'
        },
        toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'insertTable', '|', 'mediaEmbed', 'ckfinder', 'imageUpload' ],
        language: 'ru'
    })
    .then( editor => {
        console.log( editor );
    } )
    .catch( error => {
        console.error( error );
    } );
