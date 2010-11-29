$(document).ready(function () {
    var page_index = $('#access li').index($('#access li.current_page_item'));
    if (page_index === 0) {
        // home page
        $('#site-slideshow').createSlideshow();
    }
});

/**
 * Creates a slideshow. Options:
 * @param int speed: how fast to show next frame, in milliseconds
 * @param int fadespeed: how fast to fade, in milliseconds
 */
jQuery.fn.createSlideshow = function(options) {
    var $this = this,
        timeout = null, current = 0;

    options = $.extend({
        speed: 2000,
        fadespeed: 400,
        count: $this.find('li').length,
        progress_target: $('#site-list')
    }, options);

    $this.find('li').not(':eq(0)').hide();

    function nextFrame() {
        var old = current;
        clearTimeout(timeout);

        current = (current + 1) % options.count;
        $this.find('li').eq(old)
            .fadeOut(options.fadespeed, function () { $(this).hide(); });
        options.progress_target.find('li.active')
            .removeClass('active');
        $this.find('li').eq(current)
            .fadeIn(options.fadespeed, function () {
                options.progress_target.find('li')
                    .eq(current).addClass('active');
                $(this).show();
            });

        timeout = setTimeout(nextFrame, options.speed);
    }
    timeout = setTimeout(nextFrame, options.speed);
    
    return this;
}
