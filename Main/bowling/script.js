// Function to update volume label
function updateVolumeLabel(slider) {
    const label = slider.parentElement.querySelector('.volume-label');
    label.textContent = slider.value;
}

// Function to load transmitters from the server
function loadTransmitters() {
    fetch('transmitters.txt')
        .then(response => response.text())
        .then(data => {
            const transmitters = data.split('\n').filter(line => line.trim() !== '');
            const select = document.createElement('select');
            select.id = 'transmitter';
            
            transmitters.forEach(transmitter => {
                const [name, url] = transmitter.split(',').map(item => item.trim());
                const option = document.createElement('option');
                option.value = url;
                option.textContent = name;
                select.appendChild(option);
            });
            
            document.getElementById('transmitter-select').innerHTML = 'Select Transmitter: ';
            document.getElementById('transmitter-select').appendChild(select);
        })
        .catch(error => {
            console.error('Error loading transmitters:', error);
            showError('Failed to load transmitters');
        });
}

// Function to send a command to the selected transmitter
function sendCommand(action) {
    const transmitter = document.getElementById('transmitter').value;
    const formData = new FormData();
    formData.append('device_url', transmitter);
    formData.append('action', action);

    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            showError(data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Failed to send command');
    });
}

// Function to display error messages
function showError(message) {
    const errorElement = document.getElementById('error-message');
    const errorTextElement = document.getElementById('error-text');
    errorTextElement.textContent = message;
    errorElement.style.display = 'block';
    
    setTimeout(() => {
        errorElement.style.display = 'none';
    }, 5000);
}

// Event listener for form submissions
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

    // Load transmitters when the page loads
    loadTransmitters();
});
