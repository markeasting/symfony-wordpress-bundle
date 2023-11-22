<?php

namespace Metabolism\WordpressBundle;

use Env\Env;
use Metabolism\WordpressBundle\Helper\PathHelper;
use Metabolism\WordpressBundle\Loader\WordpressLoader;
use Metabolism\WordpressBundle\Loader\WordpressRegisterable;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use function Env\env;

class WordpressBundle extends Bundle
{
    private string $root_dir;
    private string $public_dir;
    private string $log_dir;
    private RouterInterface $router;

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function boot()
    {

        Env::$options = Env::USE_ENV_ARRAY;

        $kernel = $this->container->get('kernel');

        $this->log_dir = $kernel->getLogDir();
        $this->root_dir = $kernel->getProjectDir();
        $this->router = $this->container->get('router');

        $this->public_dir = $this->root_dir . (is_dir($this->root_dir . '/public') ? '/public' : '/web');

        $this->resolveServer();
        $this->loadWordpress();
    }

    public function build(ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(WordpressRegisterable::class)
            ->addTag('wordpress.registerable');

        $container->addCompilerPass(new class () implements CompilerPassInterface {
            public function process(ContainerBuilder $container): void
            {

                if (class_exists('\App\Action\WordpressAction')) {
                    $container->getDefinition('metabolism.action.wordpress_action')
                        ->setClass('\App\Action\WordpressAction');
                }
                if (class_exists('\App\Action\AdminAction')) {
                    $container->getDefinition('metabolism.action.admin_action')
                        ->setClass('\App\Action\AdminAction');
                }
                if (class_exists('\App\Action\FrontAction')) {
                    $container->getDefinition('metabolism.action.front_action')
                        ->setClass('\App\Action\FrontAction');
                }
                if (class_exists('\App\Action\LoginAction')) {
                    $container->getDefinition('metabolism.action.login_action')
                        ->setClass('\App\Action\LoginAction');
                }
            }
        });
    }

    private function resolveServer()
    {

        $context = $this->router->getContext();

        if (!isset($_SERVER['HTTP_HOST'])) {

            $multisite = env('WP_MULTISITE');

            if ($multisite && php_sapi_name() == 'cli') {

                $url = parse_url($multisite);

                $_SERVER['SERVER_PORT'] = $url['port'] ?? 80;
                $_SERVER['REQUEST_SCHEME'] = $url['scheme'] ?? 'https';

                if ($_SERVER['REQUEST_SCHEME'] == 'https')
                    $_SERVER['HTTP_HOST'] = $url['host'] ?? '127.0.0.1' . ($_SERVER['SERVER_PORT'] != 443 ? ':' . $_SERVER['SERVER_PORT'] : '');
                else
                    $_SERVER['HTTP_HOST'] = $url['host'] ?? '127.0.0.1' . ($_SERVER['SERVER_PORT'] != 80 ? ':' . $_SERVER['SERVER_PORT'] : '');
            } else {

                $_SERVER['SERVER_PORT'] = $context->isSecure() ? $context->getHttpsPort() : $context->getHttpPort();
                $_SERVER['REQUEST_SCHEME'] = $context->isSecure() ? 'https' : 'http';

                if ($context->isSecure())
                    $_SERVER['HTTP_HOST'] = $context->getHost() . ($context->getHttpsPort() != 443 ? ':' . $context->getHttpsPort() : '');
                else
                    $_SERVER['HTTP_HOST'] = $context->getHost() . ($context->getHttpPort() != 80 ? ':' . $context->getHttpPort() : '');
            }

            $_SERVER['HTTPS'] = $_SERVER['REQUEST_SCHEME'] == 'https' ? 'on' : 'off';
        }

        if (!isset($_SERVER['REMOTE_ADDR']) && php_sapi_name() == "cli")
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    public static function isLoginUrl()
    {
        $uri = explode('/', $_SERVER['SCRIPT_NAME']);
        $page = end($uri);

        return in_array($page, ['wp-login.php', 'wp-signup.php']);
    }

    /**
     * Called from the main mu-plugin entrypoint
     * The reason for this is that the WP core has to be loaded before we can use 
     */
    public static function bootstrap()
    {
        if (defined('WP_INSTALLING') && WP_INSTALLING)
            return;

        /** 
         * Redirect login paths:
         * 
         * /WP_FOLDER   >  /WP_FOLDER/wp-admin
         * /wp-admin    >  /WP_FOLDER/wp-admin
         */
        $path = rtrim($_SERVER['REQUEST_URI'], '/');
        if ($path == WP_FOLDER || str_starts_with($path, '/wp-admin')) {
            Header('Location: ' . WP_FOLDER . '/wp-admin');
            exit;
        }

        if (is_blog_installed()) {
            new \Roots\Bedrock\Autoloader();
        }

        /** @var \App\Kernel */
        global $kernel;
        $container = $kernel->getContainer();

        self::loadPlugins();
        self::loadWordpressRegisterables($container);
        self::loadActions($container);
    }

    /**
     * @TODO handle using service tagging
     * 
     * Called from the main mu-plugin entrypoint
     * @see WordpressBundle::bootstrap()
     * 
     * @return void
     */
    private static function loadPlugins()
    {
        $plugins = scandir(__DIR__ . '/Plugin');

        foreach ($plugins as $plugin) {

            if (!in_array($plugin, ['.', '..'])) {
                $classname = '\Metabolism\WordpressBundle\Plugin\\' . str_replace('.php', '', $plugin);

                if (class_exists($classname))
                    new $classname();
            }
        }
    }

    /**
     * Called from the main mu-plugin entrypoint
     * @see WordpressBundle::bootstrap()
     * @see \Metabolism\WordpressBundle\Loader\WordpressRegisterable
     * 
     * @param ContainerInterface $container
     * @return void
     */
    private static function loadWordpressRegisterables(ContainerInterface $container)
    {
        /** @var WordpressLoader */
        $wordpressLoader = $container->get('metabolism.loader.wordpress_loader');

        add_action('init', [$wordpressLoader, 'register']);
    }

    /**
     * Called from the main mu-plugin entrypoint
     * @see WordpressBundle::bootstrap()
     * 
     * @param ContainerInterface $container
     * @return void
     */
    private static function loadActions(ContainerInterface $container)
    {
        /* Note, loginAction doesn't work, because the kernel isn't loaded at this point */
        if (self::isLoginUrl()) {
            $loginAction = $container->get('metabolism.action.login_action');
            $loginAction->init();

            return;
        }

        if (is_admin()) {

            /* wp-admin only actions */
            $adminAction = $container->get('metabolism.action.admin_action');
            // add_action('kernel_loaded', [$adminAction, 'loaded']);
            add_action('admin_init', [$adminAction, 'init'], 99);

        } else {

            /* Frontend only actions */
            $frontAction = $container->get('metabolism.action.front_action');
            add_action('kernel_loaded', [$frontAction, 'loaded']);
            add_action('init', [$frontAction, 'init']);
            add_action('init', '_wp_admin_bar_init', 0);
        }

        /* General only actions */
        $wordpressAction = $container->get('metabolism.action.wordpress_action');
        add_action('kernel_loaded', [$wordpressAction, 'loaded'], 99);
        add_action('init', [$wordpressAction, 'init'], 99);

        /* Prevent overwriting .htaccess */
        add_filter('flush_rewrite_rules_hard', '__return_false');
    }

    /**
     * @see wp-includes/class-wp.php, main function
     */
    private function loadWordpress()
    {
        /* Wordpress is already loaded, exit */
        if (defined('ABSPATH'))
            return;

        /* wp-config is missing, exit */
        if (!file_exists($this->public_dir . '/wp-config.php'))
            return;

        if (!defined('WP_DEBUG_LOG'))
            define('WP_DEBUG_LOG', realpath($this->log_dir . '/wp-errors.log'));

        /* Get WordPress path */
        $wp_path = PathHelper::getWordpressRoot($this->root_dir);

        /* Start loading WordPress core without theme support */
        $wp_load_script = $this->root_dir . '/' . $wp_path . 'wp-load.php';

        if (!file_exists($wp_load_script))
            return;

        include $wp_load_script;

        remove_action('template_redirect', 'redirect_canonical');
    }
}
