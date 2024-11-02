/**
 * 
 * 
 * This script handles the client-side interactions for the AV Controls for Just Add Power receivers interface.
 *
 * @author Seth Morrow
 * @version 1.1
 */

function updateVolumeLabel(slider) {
    const label = slider.parentElement.querySelector('.volume-label');
    label.textContent = slider.value;
}

$(document).ready(function() {
    $('.receiver form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        $.ajax({
            url: '',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                const messageDiv = $('#response-message');
                messageDiv.removeClass('success error').addClass(response.success ? 'success' : 'error');
                messageDiv.text(response.message).show();
                setTimeout(function() {
                    messageDiv.hide();
                }, 5000);
            },
            error: function() {
                $('#response-message').removeClass('success').addClass('error')
                    .text("An error occurred. Please try again.").show();
            }
        });
    });
});
