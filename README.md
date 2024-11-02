# Castle AV Controls

The **Castle AV Controls** project is a centralized, password-protected system for managing audio-visual equipment across multiple zones within a venue, such as the Bowling Bar, Rink, and other areas. Each zone has its own dedicated interface, located in a subdirectory, tailored to the specific AV needs of that zone, with either remote control functionality or power management buttons.

---

## Table of Contents

- [Project Structure](#project-structure)
- [Features](#features)
- [Setup Instructions](#setup-instructions)
- [Usage Guide](#usage-guide)
- [Authentication and Security](#authentication-and-security)
- [Main Files](#main-files)
- [Zone-Specific Files](#zone-specific-files)
- [File Descriptions](#file-descriptions)
- [Additional Notes](#additional-notes)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)

---

## Project Structure

The main directory serves as the entry point for the Castle AV Controls project. It contains only the files necessary for the password-protected landing page, which links to each zone's control interface. Each zone is represented by a subdirectory containing a complete copy of the project, with tailored functionality for that specific area.

```plaintext
public/
├── index.html           # Main landing page with password protection and zone selection
├── logo.png             # Castle AV branding logo
├── script.js            # JavaScript for authentication and navigation on the main page
├── bowlingbar/          # Bowling Bar zone with power button functionality
│   ├── index.php        # Main interface for the Bowling Bar zone
│   ├── script.js        # JavaScript for Bowling Bar-specific interactions
│   ├── styles.css       # Styling for the Bowling Bar interface
│   ├── config.php       # Configuration for the Bowling Bar zone
│   └── [additional files for functionality]
├── rink/                # Rink zone with remote control functionality
│   ├── index.php        # Main interface for the Rink zone
│   ├── script.js        # JavaScript for Rink-specific interactions
│   ├── styles.css       # Styling for the Rink interface
│   ├── config.php       # Configuration for the Rink zone
│   └── [additional files for functionality]
└── [additional zones]   # Additional zones as needed, each with a full project copy
```

---

## Features

- **Zone-Based Control**: Each zone has a fully independent interface with tailored controls for that specific area’s AV requirements.
- **Remote and Power Controls**: Zone interfaces vary between remote control layouts (for navigation, volume control, etc.) and power button configurations.
- **Password Protection**: The main page is password-protected to ensure only authorized users can access the controls.
- **Daily Session Persistence**: Once authenticated, the session remains active until the end of the day, eliminating the need for repeated logins.
- **Scalability**: New zones can easily be added as separate subdirectories, each with its own tailored interface and configuration.

---

## Setup Instructions

### 1. Clone the Repository
Clone the project files into your web server’s public directory:
```bash
git clone [repository_url]
cd [project_folder]
```

### 2. Configure Each Zone
Each zone subdirectory (e.g., `bowlingbar`, `rink`) contains configuration and files specific to its needs.

- Update `config.php` in each zone’s directory to match the IP addresses and device settings for that area.
- Adjust `payloads.txt` and `transmitters.txt` as necessary to define the IR commands and transmitter IPs specific to each zone.

### 3. Set IR Commands and Transmitters
Each zone’s interface uses its own IR command definitions and transmitter configurations, found in files like `payloads.txt` and `transmitters.txt` within the zone’s directory.

### 4. Set Password
The password for accessing Castle AV Controls is defined in `script.js` in the main directory. To set a new password, modify the following line:
```javascript
const x7y9z3 = "your_new_password";
```

---

## Usage Guide

1. **Access the Main Page**:
   - Open `index.html` in a web browser. This is the main entry point for Castle AV Controls and will prompt for a password.

2. **Authenticate**:
   - Enter the designated password to unlock access. Once authenticated, the main page displays links to each available zone.

3. **Navigate to Zones**:
   - Select a zone (e.g., Bowling Bar, Rink) to open its specific control interface. Each zone's `index.php` file provides the tailored controls needed for that area:
     - **Remote Control Layout**: For zones like the Rink, which require full remote functions (navigation, volume, etc.).
     - **Power Buttons Layout**: For zones like the Bowling Bar, which only need power management options.

4. **Send Commands**:
   - Each zone’s interface sends commands to the designated AV devices within that zone using the transmitter configurations and IR commands defined in its subdirectory.

---

## Authentication and Security

The main page (`index.html`) includes password protection to secure access. Key details:

- **Daily Authentication**: Once authenticated, the session remains active until the end of the day. This is managed using `localStorage` to store an expiration timestamp.
- **Attempt Limit**: Users are allowed up to 10 password attempts. After 10 failed attempts, the form is disabled until the page is refreshed.

### Changing the Password
To change the password, modify the following line in `script.js` in the main directory:
```javascript
const x7y9z3 = "your_new_password";
```
Ensure that the password is complex enough to prevent unauthorized access.

---

## Main Files

1. **index.html** (Main Directory)
   - The primary entry point with a password prompt. After successful authentication, it displays links to each zone’s subdirectory.

2. **script.js** (Main Directory)
   - Handles password authentication, navigation between zones, and session persistence.
   - Functions like `checkPassword` validate the password, while `setAuthenticated` manages session expiration at the end of the day.

3. **logo.png** (Main Directory)
   - Branding logo for Castle AV Controls, displayed on both the main page and zone-specific pages.

---

## Zone-Specific Files

Each zone (e.g., `bowlingbar`, `rink`, `jesters`) has its own subdirectory with a complete copy of the project, containing:

- **index.php**: The main control interface for the zone, customized with either a remote control layout or power buttons, depending on the zone’s needs.
- **script.js**: JavaScript functions specific to the zone, such as sending commands to the AV devices in that area.
- **styles.css**: Additional styling unique to the zone’s interface.
- **config.php**: Configuration file defining transmitter IPs and other zone-specific settings.
- **payloads.txt**: Defines the IR commands for various remote actions used by the zone.
- **transmitters.txt**: Lists transmitter devices and IPs specific to the zone.

### Example:
- **Bowling Bar**: Contains power button functionality with options like “Power All On” and “Power All Off.”
- **Rink**: Contains a full remote control layout with navigation buttons and volume controls at the bottom of the interface.

---

## File Descriptions

Here’s a breakdown of the primary files and their roles within the project:

- **index.html**: The landing page that requires password authentication and provides navigation to each zone.
- **script.js**: Manages password entry, session persistence, and redirection to the selected zone.
- **logo.png**: A branding image for the Castle AV Controls system, used throughout the interface.
- **Zone Subdirectories**:
  - `index.php`: Zone-specific interface for controlling AV devices.
  - `script.js`: JavaScript tailored to each zone, sending AV commands or managing interactions.
  - `styles.css`: CSS specific to each zone, adapting the layout and styling for that area.
  - `config.php`: Configuration settings for each zone, such as device IPs.
  - `payloads.txt` & `transmitters.txt`: Define the IR commands and transmitter details unique to each zone.

---

## Additional Notes

- **Customization for Each Zone**: Each zone is independently configured, allowing you to tailor the controls for each specific area.
- **Testing**: It’s essential to test each zone interface to ensure that the IR commands and transmitter settings in `config.php` are correctly configured.
- **Security Enhancements**: For added security, consider implementing server-side validation for commands in each zone’s backend scripts and restricting IP access to trusted networks.

---

## Troubleshooting

- **Incorrect Password**: If you’re locked out after too many failed password attempts, refresh the page or clear local storage to reset the attempt count.
- **Device Communication Errors**: Ensure the IP addresses in `config.php` are correct and that the devices are accessible from the network.
- **IR Command Mismatches**: Verify that `payloads.txt` in each zone directory has the correct IR command sequences for your devices.

---

## Contributing

We welcome contributions to improve the Castle AV Controls project! To contribute:

1. Fork the repository.
2. Create a new feature branch.
3. Make your changes and test thoroughly.
4. Submit a pull request with a detailed description of the changes.

---

This `README.md` provides a comprehensive overview of the Castle AV Controls project, including setup, usage, and configuration details. For further assistance or customization, please contact the
