'use strict';

import $ from 'jquery';

let sdk = true;

function embedFacebookSDK()
{
    if (sdk) {
        $('body').append('<script async defer src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.2"></script>');
        sdk = false;
    }
}

$(document).ready(function() {
    if ($('*').is("figure.media")) {
        $('figure.media').each(function () {
            let url = $(this).find('oembed').attr('url');

            // youtube or vimeo media
            let regexp_youtube = /youtu(\.)?be/gi;
            let regexp_vimeo = /vimeo/gi;

            if (url.match(regexp_youtube) || url.match(regexp_vimeo)) {
                let arr = url.split('/');
                let key = arr[arr.length-1];
                let url_embed;
                if (url.match(regexp_youtube)) {
                    url_embed = 'https://www.youtube.com/embed/' + key;
                } else {
                    url_embed = 'https://player.vimeo.com/video/' + key;
                }

                $(this).replaceWith('<div class="embed-responsive embed-responsive-16by9 my-2"><iframe class="embed-responsive-item" src="' + url_embed + '" allowFullScreen="true"></iframe></div>');
            }

            // facebook media
            let regexp_facebook = /facebook/gi;

            if (url.match(regexp_facebook)) {
                $(this).replaceWith('<div class="my-2"><div class="fb-video" data-href="' + url + '" data-width="auto" data-show-captions="true"></div></div>');
                embedFacebookSDK();
            }
        })
    }
});
