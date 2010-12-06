$(document).ready(function () {
    var page_index = $('#access li').index($('#access li.current_page_item'));
    if (page_index === 0) {
        // home page
        $('#site-slideshow').createSlideshow();
    }

    // create contact form modal if there's a link for it
    var $contact_trigger = $('#relationship');
    if ($contact_trigger.length) {
        var $contact_modal = $('<div id="modal"/>');
            $overlay = $('<div id="overlay"/>');
        $overlay.appendTo('body');
        $contact_modal.html(
            '<form id="contact" action="/contact" method="POST">' +
            '<a class="close" href="#">x</a>' +
            '<label for="name">Name:</label>' +
            '<input type="text" name="name" id="name" value=""><br/>' +
            '<label for="email">Email:</label>' +
            '<input type="email" required="required" name="email" id="email" value=""><br/>' +
            '<label for="message">Message:</label>' +
            '<textarea name="message" rows="5" cols="40" id="message" required="required"></textarea><br/>' +
            '<input type="submit" value="Send dyloot your message" name="contact">' +
            '</form>');
        $contact_modal.appendTo('body');
        $contact_modal.find('.close').click(function (ev) {
            ev.preventDefault();
            $contact_modal.hide();
            $overlay.hide();
            return false;
        });
        $contact_trigger.click(function (ev) {
            ev.preventDefault();
            $contact_modal.show();
            $overlay.show();
            return false;
        });
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
        speed: 5000,
        fadespeed: 300,
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
