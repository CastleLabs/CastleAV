// Function to load transmitters from the server
function loadTransmitters() {
    fetch('transmitters.txt')
        .then(response => response.text())
        .then(data => {
            // Split the data into lines and filter out empty lines
            const transmitters = data.split('\n').filter(line => line.trim() !== '');
            
            // Create a select element
            const select = document.createElement('select');
            select.id = 'transmitter';
            
            // Create option elements for each transmitter
            transmitters.forEach(transmitter => {
                const [name, url] = transmitter.split(',').map(item => item.trim());
                const option = document.createElement('option');
                option.value = url;
                option.textContent = name;
                select.appendChild(option);
            });
            
            // Update the transmitter select container
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
    
    // Hide the error message after 5 seconds
    setTimeout(() => {
        errorElement.style.display = 'none';
    }, 5000);
}

// Load transmitters when the page loads
document.addEventListener('DOMContentLoaded', loadTransmitters);
