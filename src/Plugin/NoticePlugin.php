<?php

namespace Metabolism\WordpressBundle\Plugin;

use function Env\env;

/**
 * Class
 */
class NoticePlugin {
    
    /**
     * Check symlinks and folders
     */
    public function adminNotices(){
        
        $currentScreen = get_current_screen();
        
        if( !current_user_can('administrator') || $currentScreen->base != 'dashboard' )
            return;

        if( ($_GET['fix']??false) == 'controller' ){
            
            $controller = BASE_URI.'/src/Controller/BlogController.php';
            $template = BASE_URI.'/templates/generic.html.twig';
            
            if( !file_exists($controller) ){
                
                copy(__DIR__.'/../../samples/src/Controller/BlogController.php', $controller);
                copy(__DIR__.'/../../samples/templates/generic.html.twig', $template);
            }
        }
        
        $notices = [];
        $errors = [];
        
        //check folder right
        $folders = [PUBLIC_DIR.'/wp-bundle/languages', PUBLIC_DIR.'/uploads', PUBLIC_DIR.'/wp-bundle/upgrade', '/var/cache', '/var/log'];
        $folders = apply_filters('wp-bundle/admin_notices', $folders);
        
        foreach ($folders as $folder ){
            
            $path = BASE_URI.$folder;
            
            if( !file_exists($path) && !@mkdir($path, 0755, true) )
                $errors [] = 'Can\' create folder : '.$folder;
            
            if( file_exists($path) && !is_writable($path) )
                $errors [] = $folder.' folder is not writable';
        }
        
        if( env('MAILER_URL') === 'null://localhost' )
            $errors[] = 'Mail delivery is disabled';
        
        if( is_blog_installed() && !env('WP_INSTALLED') )
            $notices[] = 'Wordpress is now installed, you should add WP_INSTALLED=1 to your environment';
        
        if( !file_exists(BASE_URI.'/src/Controller/BlogController.php') )
            $errors[] = 'There is no controller defined : <a href="?fix=controller">Create one</a>';
        
        if( !empty($errors) )
            echo '<div class="error"><p>'.implode('<br/>', $errors ).'</p></div>';
        
        if( !empty($notices) )
            echo '<div class="updated"><p>'.implode('<br/>', $notices ).'</p></div>';
    }
    
    
    /**
     * Add debug info
     */
    public function debugInfo(){
        
        add_action( 'admin_bar_menu', function( $wp_admin_bar )
        {
            $args = [
                'id'    => 'debug',
                'title' => '<span style="position: fixed; left: 0; top: 0; width: 100%; background: #df0f0f; height: 2px; z-index: 99999"></span>',
                'href' => '#'
            ];
            
            $wp_admin_bar->add_node( $args );
            
        }, 9999 );
    }
    
    
    /**
     * remove wpdb error
     */
    public function suppressError(){
        
        global $wpdb;
        $wpdb->suppress_errors = true;
    }
    
    
    /**
     * NoticePlugin constructor.
     */
    public function __construct()
    {
        if( is_admin() )
        {
            add_action( 'admin_notices', [$this, 'adminNotices']);
            
            if( WP_DEBUG )
                add_action( 'init', [$this, 'debugInfo']);
        }
        else{
            
            if( HEADLESS )
                add_action( 'init', [$this, 'suppressError']);
        }
    }
}
