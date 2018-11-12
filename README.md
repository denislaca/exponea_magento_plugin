# Exponea Magento 2 tracking plugin

## Setup

You should run the Magento software in developer mode when you’re extending or customizing it. You can use this command line to show current mode :

`php bin/magento deploy:mode:show`

Use this command to change to developer mode :

`php bin/magento deploy:mode:set developer`

### Installation

1. Copy Exponea plugin to `<your_project>/app/code`, the structure should be `code/Exponea/Exponea`.

2. In `<your_project>` directory run `php bin/magento setup:upgrade && php bin/magento setup:di:compile` 

You can now find Exponea plugin in Magento 2 admin, go to `Stores -> Configuration -> Exponea`

### Configuration

1. Get your project token from your Exponea app `Project settings -> General settings`, you will also need to use API secret and public key, you can generate those in `Project settings -> API settings`, select `private` in the dropdown and generate new key.

2. Default API route is `https://api.exponea.com/track/v2/projects` without the slash at the end.

## Project structure

### Block

Block is taking care of passing backend data to frontend, everything from `Exponea.php` can be passed to frontend using defined functions.

### Controller

Controller classes are taking care of importing catalogs and orders to your project, in case of catalog you need to define the name of catalog you want to create.

### Helpers

`Helper.php` includes all the parsing functions and encapsulations for the right formating of the request. You can use these functions to write observers, there's a parser function for every object you can track. 

### Models

Includes `Tracking.php` - sending POST requests to Exponea API you define in your configuration.

### Observers

Includes all the backend observers. Each of them gets fired on a different action - located in `etc/events.xml`.

### View 

Includes all of the JavaScript that's included on frontend as a `<script></script>`.



