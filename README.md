# Wordpress To  MVC

## Setup for Laravel 5.x
1. set up your Laravel site
2. download and set up WordPress in a separate folder within your public folder, e.g., /public/wordpress
3. Place the wordpress-to-laravel-mvc directory to your /public/wordpress/wp-content/themes/ directory, and then choose that theme from your Wordpress dashboard
4. Add the following lines to the top of your /public/index.php file (before the boostrapping):
```
/*
|--------------------------------------------------------------------------
| WordPress
|--------------------------------------------------------------------------
|
| Integrate WordPress with Laravel core
|
*/

define('WP_USE_THEMES', false);
require __DIR__.'/../public/wordpress/wp-blog-header.php';
```
5. Place WordPressServiceProvider.php into your /app/Providers directory
6. add the following line to the config/app.php 'providers' array: 
```
App\Providers\WordPressServiceProvider::class
```


## Usage

* **Check if a post or page URL slug exists within WordPress**:
```
WordPressServiceProvider::check_route('my-page-or-post');
// Returns an object with a post type
```
* **Build Heirarchical menu based off of WordPress Menus**:
```
WordPressServiceProvider::menu('My Awesome Menu', 1);
// returns a menu object. The second parameter is the current page ID
```
* **Get a specific page or post object by ID**:
```
WordPressServiceProvider::post(1);
// returns a post object
```
* **Query for specific posts or pages using the WP_Query syntax**:
```
WordPressServiceProvider::query($query);
// returns an array of post objects
```
