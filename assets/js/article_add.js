
import $ from 'jquery';
import 'jquery-confirm';
import Cropper from 'cropperjs';

let bytes = require('bytes');
let cropper = false;

function errorReporting(msg) {
    $.alert({
        icon: 'fas fa-exclamation-triangle',
        title: 'Attention!',
        content: msg,
        type: 'red',
        typeAnimated: true
    });
}

function cropping()
{
    const image = document.getElementById('img_crop');
    cropper = new Cropper(image, {
        aspectRatio: 16 / 9,
        viewMode: 3,
        autoCropArea: 0.95,
        minContainerWidth: 300,
        minCropBoxWidth: 250,
        minCropBoxHeight: 140,
        crop(event) {
            console.log(event.detail.x);
            console.log(event.detail.y);
            console.log(event.detail.width);
            console.log(event.detail.height);
            console.log(event.detail.rotate);
            console.log(event.detail.scaleX);
            console.log(event.detail.scaleY);
        },
        ready(){},
    });

    let settingsCrop = '<div class="p-2 bg-dark w-100 text-center"><a href="javascript:void(0)" class="btn btn-light btn-sm mr-1 mr-sm-2 undo-crop" role="button"><i class="fas fa-undo"></i></a><a href="javascript:void(0)" class="btn btn-light btn-sm mr-1 mr-sm-2 redo-crop" role="button"><i class="fas fa-redo"></i></a><a href="javascript:void(0)" class="btn btn-sm btn-light mr-1 mr-sm-2 scale-crop" role="button"><i class="fas fa-exchange-alt"></i></a><a href="javascript:void(0)" class="btn btn-light btn-sm mr-1 mr-sm-2 move-crop" role="button"><i class="fas fa-crop-alt"></i></a><a href="javascript:void(0)" class="btn btn-light btn-sm mr-1 mr-sm-2 reset-crop" role="button"><i class="fas fa-sync"></i></a><a href="javascript:void(0)" class="btn btn-light btn-sm disable-crop" role="button"><i class="fas fa-lock-open text-success"></i></a></div>';

    $('#selected_image').append(settingsCrop);

    $('a.undo-crop').click(function(){
        cropper.rotate(-45);
    });

    $('a.redo-crop').click(function(){
        cropper.rotate(45);
    });

    let scaleCrop = -1;
    $('a.scale-crop').click(function(){
        cropper.scaleX(scaleCrop);
        if (scaleCrop < 0) {
            scaleCrop = 1;
        } else {
            scaleCrop = -1;
        }
    });

    let dragCrop = 'move';
    $('a.move-crop').click(function(){
        let obj = $(this).find('svg');
        cropper.setDragMode(dragCrop);
        if (dragCrop === 'move') {
            dragCrop = 'crop';
            obj.removeClass('fa-crop-alt').addClass('fa-arrows-alt');
        } else {
            dragCrop = 'move';
            obj.removeClass('fa-arrows-alt').addClass('fa-crop-alt');
        }
    });

    $('a.disable-crop').click(function(){
        let obj = $(this).find('svg');
        if (obj.hasClass('fa-lock-open')) {
            cropper.disable();
            obj.removeClass('fa-lock-open text-success').addClass('fa-lock text-danger');
        } else {
            cropper.enable();
            obj.removeClass('fa-lock text-danger').addClass('fa-lock-open text-success');
        }
    });

    $('a.reset-crop').click(function(){
        cropper.reset();
    });
}

function sendData(formData, path)
{
    let button = $('form#article_form').find('button[type="submit"]');
    button.prop('disabled', true).text('Dispatch ...');

    $.ajax('/admin/article/' + path, {
        method: "POST",
        data: formData,
        async: true,
        cache: false,
        processData: false,
        contentType: false,
        success(json) {
            let dataJson = $.parseJSON(json);
            if (typeof dataJson.error !== "undefined") {
                errorReporting(dataJson.error);
            } else {
                window.location.href = '/admin/article/show/' + dataJson.id;
            }
        },
        error() {
            errorReporting('An error has occurred');
            button.prop('disabled', false).text('Save');
        },
    });
}

$(document).ready(function () {
    $('input[type="file"]').on('change', function () {
        let obj = $(this);
        console.log(this.files);
        let fileObj = this.files[0];
        let type_file = fileObj.type;
        let mime = ['image/jpeg'];
        let defaultPlaceholder = obj.next('label').text();
        if (mime.indexOf(type_file) !== -1) {
            let size_file = fileObj.size;
            obj.next('label').text(fileObj.name);

            let info = '<div class="text-white-50 small p-2 bg-dark"><span>Тип: <strong>' + type_file + '</strong>; </span><span>Размер: <strong>' + bytes(size_file) + '</strong></span></div>';

            let reader = new FileReader();
            reader.onload = function (event) {
                $('#selected_image').html(info + '<img src="' + event.target.result + '" class="img-fluid mt-2" id="img_crop" />');
                cropping();
            };

            reader.readAsDataURL(fileObj);
        } else {
            errorReporting('Invalid file type');
            obj.val('');
            obj.next('label').html(defaultPlaceholder);
        }
    });

    $('form#article_form').on('submit', function(e) {
        e.preventDefault();

        let path = 'add';
        let id = $(this).attr('data-article');
        if (id !== '') {
            path = 'edit/' + id;
        }

        let formData = new FormData($(this)[0]);

        if(cropper)
        {
            cropper.getCroppedCanvas({
                fillColor: '#fff',
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            }).toBlob((img) => {
                formData.delete('article_add[image]');
                formData.append('article_add[image]', img);

                sendData(formData, path);
            },'image/jpeg', 0.8);
        } else {
            if ($('*').is('#old_image')) {
                sendData(formData, path);
            } else {
                errorReporting('Choose image');
            }
        }
    });
});
