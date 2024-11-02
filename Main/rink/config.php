<?php
/**
 * Configuration file - AV Controls for Just Add Power receivers
 * 
 * This file contains the list of receivers and their corresponding IP addresses,
 * transmitters and their channel numbers, as well as global configuration settings 
 * for the application.
 *
 * @author Seth Morrow
 * @version 1.1.0
 * @date 2023-08-14
 */

// Array of receivers with their names as keys and IP addresses as values
// Add, remove, or modify receivers here as needed
const RECEIVERS = [
    "Rink Music" => "192.168.8.15",
    "Rink Video Wall" => "192.168.8.13",
];

// Array of transmitters with their names as keys and channel numbers as values
// Add, remove, or modify transmitters here as needed
// This new constant replaces the previous MAX_CHANNELS constant
const TRANSMITTERS = [
    "Apple TV" => "7",
    "Cable Box 1" => "2",
    "Cable Box 2" => "3",
    "Cable Box 3" => "4",
    "Unifi Signage" => "5",
    "RockBot Audio" => "1",
    "Rink Spare Audio" =>  "6",
    "Rink Spare Video" => "8",
    // Add more transmitters as needed, e.g.:
    // "ROKU" => "3",
    // "PLAYSTATION" => "4",
];

// Global configuration settings
const MAX_VOLUME = 6;  // Maximum volume level for the devices
const MIN_VOLUME = 1;   // Minimum volume level for the devices
const VOLUME_STEP = 1;  // Step size for volume control slider

// Home page URL - modify this if the home page location changes
const HOME_URL = 'http://192.168.8.127';

// Logging configuration
const LOG_LEVEL = 'info'; // Options: debug, info, warning, error

// API call timeout in seconds
const API_TIMEOUT = 5;

// Supported models for volume control
// Add or remove models from this list as needed
const VOLUME_CONTROL_MODELS = ['3G+4+ TX', '3G+AVP RX', '3G+AVP TX', '3G+WP4 TX', '2G/3G SX'];

// Error messages
const ERROR_MESSAGES = [
    'connection' => 'Unable to connect to %s (%s). Please check the connection and try again.',
    'global' => 'Unable to connect to any receivers. Please check your network connection and try again.',
];
