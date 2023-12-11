<?php

namespace Metabolism\WordpressBundle\Plugin;

use Metabolism\WordpressBundle\Entity\Blog;

use function Env\env;

class MailPlugin
{

	protected $_smtp_config;

	public function __construct()
	{
		$dsn = env('MAILER_DSN');

        if (!isset($dsn) || empty($dsn) || $dsn === 'null://localhost') {

            add_action('admin_notices', function() {
				printf('<div class="notice notice-warning is-dismissible"><p>Mail delivery disabled - no <code>MAILER_DSN</code> configured.</p></div>');
			});

		} else {

			add_action('init', function() {
				if (is_user_logged_in() && current_user_can('administrator')) {
					$this->addToolsPage();
				}
			});

			$this->setSMTPConfig($dsn);

			if ($this->_smtp_config['scheme'] != null) {
				
				// add_action('wp_mail_failed', function ($wp_error) {
				// 	dd($wp_error);
				// }, 10, 1);

				add_action('phpmailer_init', array($this, 'configureSmtp'));
				add_filter('wp_mail_content_type', function () { return "text/html"; });
				add_filter('wp_mail_from', array($this, 'fromEmail'));
				add_filter('wp_mail_from_name', array($this, 'fromName'));
			}
		}
	}

	public function addToolsPage()
	{
		if (!function_exists('add_management_page')) {
			return;
		}

		add_management_page('Email tester', 'Email tester', 'manage_options', 'test-mail-sender', function() {
			$current_user = wp_get_current_user();
			?>
			<div class="wrap">
				<h2>Email tester</h2>
				<p>Configured <code>MAILER_DSN</code>:</p>
				<p><code><?= esc_html(env('MAILER_DSN')); ?></code></p>
				<form action="" method="post">
					<?php wp_nonce_field('ws_send_test_mail'); ?>
					<input type="text" name="test_mail_address" class="" placeholder="Email" value="<?= $current_user->user_email ?>">
					<input type="submit" name="ws_send_test_mail" class="button-primary" value="Send test mail">
				</form>
			</div>
			<?php
		});

		/* Handle POST request */
		add_action('admin_init', function () {
			if (isset($_POST['ws_send_test_mail']) && $_POST['test_mail_address'] && check_admin_referer('ws_send_test_mail')) {

				$_ENV['PHPMAILER_DEBUG_LEVEL'] = 2;

				$to = $_POST['test_mail_address'];
				$subject = 'WordPress Test Mail';
				$message = 'This is a test mail sent from WordPress';

				ob_start();

				$success = wp_mail($to, $subject, $message);

				$output = ob_get_clean();

				add_action('admin_notices', function() use ($success, $output) {
					?>
					<div class="notice notice-<?= $success ? 'success' : 'error' ?> is-dismissible">
						<p><?= $output; ?></p>
					</div>
					<?php
				});
			}
		});
	}

	/**
	 * Set SMTP Config from MAILER_DSN in .env.
	 * @param null|string $url
	 */
	public function setSMTPConfig($url)
	{
		$this->_smtp_config = [];

		if (!empty($url)) {

			$this->_smtp_config = parse_url($url);

			if (!empty($this->_smtp_config['query'])) {
				parse_str($this->_smtp_config['query'], $query);
				$this->_smtp_config += $query;
			}
		}
	}

	/**
	 * Override From Name
	 * @param $name
	 * @return string|void
	 */
	public function fromName($name)
	{
		if ($blogName = get_bloginfo('blog_name'))
			return $blogName;

		return $name;
	}

	/**
	 * Override From Email
	 * @param $email
	 * @return mixed
	 */
	public function fromEmail($email)
	{
		$domain = Blog::getInstance()->getDomain();

		if (str_contains($domain, 'localhost')) {
			$domain = 'localhost.com';
		}

		/* Using SMTP credentials is possible, but not adviced (e.g. sendgrid user is 'apikey') */
		// if (!empty($this->_smtp_config['user']) && is_email($this->_smtp_config['user']))
		// 	return $this->_smtp_config['user'];

		return "noreply@$domain";
	}

	/**
	 * Configure PHPMailer
	 * @param \PHPMailer $phpmailer
	 */
	public function configureSmtp($phpmailer)
	{
		if (isset($_ENV['PHPMAILER_DEBUG_LEVEL'])) {
			$phpmailer->SMTPDebug = (int) $_ENV['PHPMAILER_DEBUG_LEVEL'];
		}

		$phpmailer->isSMTP();
		$phpmailer->Host = $this->_smtp_config['host'];
		$phpmailer->Port = $this->_smtp_config['port'] ?? 25;
		$phpmailer->Timeout = 10;

		$SMTPAuth = (!empty($this->_smtp_config['auth_mode']) && $this->_smtp_config['auth_mode'] == 'login');

		$phpmailer->SMTPAuth = $SMTPAuth;

		if ($SMTPAuth) {
			$phpmailer->Username = $this->_smtp_config['user'];
			$phpmailer->Password = urldecode($this->_smtp_config['pass']);
		}

		if (isset($this->_smtp_config['encryption'])) {
			$phpmailer->SMTPSecure = $this->_smtp_config['encryption'];
		}
	}
}