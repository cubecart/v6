$(document).ready(function() {
    $('.fn-dd-url').on('change', function (e) {
        $(this).parents(".fn-dd-url").submit();
    });
    $('.fn-dd-post').on('change', function (e) {
        window.location = $(this).val();
    });
    $('.fn-toggle-vis').on('click', function (e) {
        var id =  '#'+$(this).attr("rel");
        if($(id).hasClass('hidden')) {
            $(id).removeClass('hidden');
        } else {
            $(id).addClass('hidden');
        }
    });
});