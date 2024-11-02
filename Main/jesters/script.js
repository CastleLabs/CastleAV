/**
 * This script handles the client-side interactions for the AV Controls for Just Add Power receivers interface.
 *
 * @author Seth Morrow
 * @version 1.4
 * @date 2023-08-15
 */

function updateVolumeLabel(slider) {
    const label = slider.parentElement.querySelector('.volume-label');
    label.textContent = slider.value;
}

function sendPowerCommand(deviceIp, command) {
    return $.ajax({
        url: '',
        type: 'POST',
        data: {
            receiver_ip: deviceIp,
            power_command: command
        },
        dataType: 'json'
    });
}

function sendPowerCommandToAll(command) {
    const receivers = $('.receiver');
    let promises = [];

    receivers.each(function() {
        const deviceIp = $(this).find('input[name="receiver_ip"]').val();
        promises.push(sendPowerCommand(deviceIp, command));
    });

    Promise.all(promises);
}

$(document).ready(function() {
    $('.receiver form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        $.ajax({
            url: '',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json'
        });
    });

    $('#power-all-on').on('click', function() {
        sendPowerCommandToAll('cec_tv_on.sh');
    });

    $('#power-all-off').on('click', function() {
        sendPowerCommandToAll('cec_tv_off.sh');
    });
});
