const app = 'https://adameastwood.com/.vbbmf.co.uk/app.js';

$(document).ready(function() {
    Loader(app, 'initMap');
});

function Loader(url, callback) {
    console.table('attempting load');

    jQuery.ajax({
        url: url,
        dataType: 'script',
        success: callback,
        async: true
    });

    console.table('loaded');
}