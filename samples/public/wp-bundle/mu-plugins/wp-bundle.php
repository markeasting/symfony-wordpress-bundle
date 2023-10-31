<?php
/**
 * Plugin Name: Wildpress loader
 * Description: Load Wordpress in Symfony
 * Version: 2.2.0
 * Author: Wildsea
 */

 use Metabolism\WordpressBundle\WordpressBundle;

 /**
  * NOTE: THIS FILE WILL BE OVERWRITTEN BY WORDPRESS-BUNDLE
  */
 if (is_admin()) {
     $kernel = new \App\Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
     $kernel->boot(); // Set up minimal DI container in admin interface
 }
 
 WordpressBundle::boostrap();
 