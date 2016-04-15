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
