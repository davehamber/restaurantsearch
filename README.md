# Restaurant Search Demonstration

### About
The Restaurant Search Demonstration allows a user to input the address of any geographical location when logged in. Upon submitting a query, a list of the nearest twenty restaurants is returned in ascending order of distance. The search results contain a photo of the restaurant, its name, address, a rating and its distance from the searched location.

This demonstration shows the usage of account based authentication, OAuth Facebook API authentication, Google Places API and Doctrine entities with a database backend within Symfony2.

All user results are cached locally to database, and the respective images are stored in the filesystem. Subsequently repeated searches no longer require interaction with Google.

### Key Technologies
* Symfony 2.7 Framework
* Database user account authentication (Friends of Symfony bundle).
* Facebook Connect OAuth (HWI OAuth bundle).
* Google Places API
* Google Street View API

### Details
In order to use the Restaurant Search, the user must first log in to the site on the login page. Either an account can be created using user submitted details allowing them to log in, or alternatively Facebook can be used.

The address the user types is converted via Google Geocoding into latitude and longitude. Using the Google Places API, a "nearby search" is sent using the latitude and longitude from the geocoding. The nearest twenty restaurants to the location are returned from Google. If photo information exists in a result, the first photo image will then be fetched from google. If there are no photos for any given place result then an image closest to the location will be fetched from Google Street View API.

All results are committed to a database via Doctrine. The image files and photos are saved and stored on the server. All subsequently repeated results are fetched locally.

### Installation

#### Prerequisites
* A php / web server environment with a database. MySQL is assumed (change from pdo_mysql driver in the app/config.yml if you use another).
* A new facebook app with its client id and api secret https://developers.facebook.com
* A Google API Key configured via https://console.developers.google.com with the Google Places API Web Service and Google Street View Image API enabled.
* Make sure you have composer installed https://getcomposer.org

#### Process (using Vagrant)
* git clone https://github.com/davehamber/restaurantsearch.git
* cd restaurantsearch
* composer update

* Get ready to input the following values:
    * database_host (localhost):
    * database_port (null):
    * database_name (restaurantsearch):
    * database_user (restaurantsearch):
    * database_password (restaurantsearch):
    * mailer_transport (smtp):
    * mailer_host (127.0.0.1):
    * mailer_user (null):
    * mailer_password (null):
    * secret (ThisTokenIsNotSoSecretChangeIt):
    * fb_client_id (AddFacebookClientIdHere):
    * fb_secret (AddFacebookSecretKeyHere):
    * google_api_key (AddGoogleAPIKeyHere):
    * street_view_image_path (../web/streetview):
All of these should be set as default when using the vagrant set up, with the exception of the Facebook client id, secret and Google API key which must be your own.    

* vagrant box add centos/7
* vagrant up

If you have failed to add your Facebook client id, secret and google api key you can log into your vagrant instance at anytime using:

* vagrant ssh
* nano /vagrant/app/config/parameters.yml

After vagrant sets up the installation, the complete environment including the database and tables will be now configured.
You should now be able to view the site at localhost:8080 but for the facebook login functionality you need to use a domain name.

When creating your Facebook app at https://developers.facebook.com you must specify a domain name and not an IP address
In my case I set the Site URL to http://restaurantsearch.davehamber.local:8080/login/check-facebook

You will need to modify your own hosts file to include the URL you specify as the URL in the Facebook dev console
Windows: c:\windows\system32\drivers\etc\hosts
Linux /etc/hosts
Add this line:

* 127.0.0.1           restaurantsearch.davehamber.local

To run Symfony in dev mode you can use the built in php server. Port 8000 is already forwarded in the VagrantFile. You must bind the IP to 0.0.0.0:8000 to make the set up accessable by your host machine.
Remember, your Facebook app only works for one address, you may wish to configure a second dev app to work on the different port.

* vagrant ssh (if not logged in already)
* php /vagrant/app/console server:run 0.0.0.0:8000




