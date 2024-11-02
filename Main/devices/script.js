$(document).ready(function() {
    const rebootModal = $('#rebootModal');

    $('#reboot-all').on('click', function() {
        rebootModal.show();
    });

    $('#cancelReboot').on('click', function() {
        rebootModal.hide();
    });

    $('#confirmReboot').on('click', function() {
        rebootModal.hide();
        executeRebootAll();
    });

    $('#fix-rockbot').on('click', function() {
        executeFixRockBot();
    });

    // Close the modal if user clicks outside of it
    $(window).on('click', function(event) {
        if (event.target == rebootModal[0]) {
            rebootModal.hide();
        }
    });
});

function executeRebootAll() {
    $.ajax({
        url: 'reboot_all.php',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert(response.message);
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("AJAX Error:", textStatus, errorThrown);
            console.log("Response Text:", jqXHR.responseText);
            alert('An error occurred while trying to reboot devices. Check the console for details.');
        }
    });
}

function executeFixRockBot() {
    const $button = $('#fix-rockbot');
    $button.prop('disabled', true).text('Fixing...');
    
    $.ajax({
        url: 'fix_rockbot.php',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('RockBot fixed successfully: ' + response.message);
            } else {
                alert('Error fixing RockBot: ' + response.message);
            }
            console.log('Detailed results:', response.details);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("AJAX Error:", textStatus, errorThrown);
            console.log("Response Text:", jqXHR.responseText);
            alert('An error occurred while trying to fix RockBot. Check the console for details.');
        },
        complete: function() {
            $button.prop('disabled', false).text('Fix RockBot');
        }
    });
}
