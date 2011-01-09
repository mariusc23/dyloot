$(document).ready(function () {
    var page_index = $('#access li').index($('#access li.current_page_item'));
    if (page_index === 0) {
        // home page
        $('#site-slideshow').createSlideshow();
    }

    // create contact form modal if there's a link for it
    var $contact_trigger = $('#relationship');

    function initModal() {
        $contact_modal.html(
            '<a href="#" title="Close" class="close">x</a>' +
            '<h1>Send us a message to find out!</h1>' +
            '<h2>We respond within 24 hours.</h2>' +
            '<form id="contact" action="/contact/" method="POST">' +
                '<label for="name">Name:</label>' +
                '<input type="text" name="name" id="name" value="" required><br/>' +
                '<label for="email">Email:</label>' +
                '<input type="email" required="required" name="email" id="email" value="" required><br/>' +
                '<label for="message">Message:</label>' +
                '<textarea name="message" rows="5" cols="40" id="message" required></textarea><br/>' +
                '<div class="submit">' +
                  '<input type="submit" value="Send dyloot your message" name="contact">' +
                '</div>' +
            '</form>');
        function closeModal(ev) {
            ev.preventDefault();
            $contact_modal.hide();
            $overlay.hide();
            initModal();
            return false;
        }
        $contact_modal.find('form').submit(submitModal);
        $contact_modal.find('.close').click(closeModal);
        $overlay.click(closeModal);
        $('body').keypress(function (ev) {
            if (ev.keyCode === 27) {
                // escape closes the modal
                return closeModal(ev);
            }
        });
    }
    function showMessage(h1, h2, message) {
        $contact_modal.find('h1').html(h1);
        $contact_modal.find('h2').html(h2);
        $contact_modal.find('form').html(message);
    }
    function submitModal(ev) {
        var $form = $contact_modal.find('form');
        ev.preventDefault();
        if (!validate_then_submit(ev, $form)) {
            return false;
        }
        $.ajax({
            type: 'POST',
            url: '/contact/',
            dataType: 'json',
            data: $form.serialize() + '&contact=1',
            error: function(request, textStatus, error) {
                if (request.status === 404) {
                    showMessage("Oops. Server says page not found.");
                } else if (request.status === 500 &&
                    request.responseText.indexOf('already said that') >= 0) {
                    showMessage('Oops, duping around?', 'Hmm.',
                                '<p>Looks like you sent us this message before?</p>');
                } else {
                    showMessage('Woah, something exploded!', 'We have no idea what happened :(',
                                'Oops. Unknown error. Status code ' + request.status + '.');
                }
            },
            success: function(response, textStatus, request) {
                if (response.status === 'success' ||
                    response.status === 'spam') {  // spam acts normal
                    showMessage('Thank you for contacting us!', 'Yay!',
                                '<p>Check your email for a confirmation.</p>' + 
                                '<p>We will get back to you shortly.</p>');
                }
                else if (response.status === 'duplicate') {
                    showMessage('Oops, duping around?', 'Hmm.',
                                '<p>Looks like you sent us this message before?</p>');
                }
                else if (response.status === 'fail-mail') {
                    showMessage('Could not send message!', 'Hmm.',
                                '<p>Our server could not send the email.</p>' +
                                '<p>Please try again later.</p>');
                }
                else if (response.status === 'invalid') {
                    showMessage('Please check your data!', 'Hmm.',
                                '<p>We could not validate your information.</p>' +
                                '<p>Please try again.</p>');
                }
                else {
                    showMessage('Thank you for contacting us!', 'Yay!',
                                '<p>Check your email for a confirmation.</p>' + 
                                '<p>We will get back to you shortly.</p>');
                }
            }
        });
        return false;
    }

    if ($contact_trigger.length) {
        var $contact_modal = $('<div id="modal"/>');
            $overlay = $('<div id="overlay"/>');
        $overlay.appendTo('body');
        initModal();
        $contact_modal.appendTo('body');
        $contact_trigger.click(function (ev) {
            ev.preventDefault();
            $contact_modal.show();
            $overlay.show();
            return false;
        });
    }


    function isValidEmailAddress(emailAddress) {
        var pattern = new RegExp(/^\s*[\w\-\+_]+(\.[\w\-\+_]+)*\@[\w\-\+_]+\.[\w\-\+_]+(\.[\w\-\+_]+)*\s*$/);
        return pattern.test(emailAddress);
    }

    var labels = {
        name: $('label[for="name"]', $('#contact')).html(),
        email: $('label[for="email"]', $('#contact')).html(),
        message: $('label[for="message"]', $('#contact')).html()
    };
    $('#contact').submit(function (ev) {
        if (!validate_then_submit(ev, $(this))) {
            return false;
        }
    });
    function validate_then_submit(ev, $form) {
        var $i_name = $('input[name="name"]', $form),
            $i_email = $('input[name="email"]', $form),
            $i_message = $('textarea', $form),
            c_name = $i_name.val(),
            c_email = $i_email.val(),
            c_message = $i_message.val();

        // check name length
        if (c_name.length < 5) {
            markField($i_name, 'Too short!');
        } else {
            markField($i_name);
        }

        // check email length
        if (c_email.length < 5) {
            markField($i_email, 'Too short!');
        } else if (!isValidEmailAddress(c_email)) {
            markField($i_email, 'Email is not valid!');
        } else {
            markField($i_email);
        }

        // check message length
        if (c_message.length < 25) {
            markField($i_message, 'Too short!');
        } else {
            markField($i_message);
        }

        if ($i_name.hasClass('error') ||
            $i_email.hasClass('error') ||
            $i_message.hasClass('error')) {
            ev.preventDefault();
            return false;
        }
        $form.find('input[type="submit"]')
               .val('Sending...');
        return true;
    }

    /**
     * Mark the field with an error or remove that error.
     */
    function markField($input, message) {
        var name = $input.attr('name')

        if (message) {
            $('label[for="' + name + '"]').html(
                labels[name] + ' <span class="error">' + message + '</span>');
            $input.addClass('error');
        } else {
            $('label[for="' + name + '"]').html(labels[name]);
            $input.removeClass('error');
        }
    }

    $('#contact input[type="submit"]').attr('disabled', '');
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
