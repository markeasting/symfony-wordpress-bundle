<?php

namespace Metabolism\WordpressBundle\Plugin;

use function Env\env;

class MailPlugin
{

	protected $_smtp_config;

	public function __construct()
	{
		$dsn = env('MAILER_DSN');

		if (!empty($dsn)) {

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
		if (str_contains($email, 'localhost')) {
			$email = 'wordpress@testdomain.com';
		} else {
			if (!empty($this->_smtp_config['user']) && is_email($this->_smtp_config['user']))
				return $this->_smtp_config['user'];
		}

		return $email;
	}

	/**
	 * Configure PHPMailer
	 * @param \PHPMailer $phpmailer
	 */
	public function configureSmtp($phpmailer)
	{
		// $phpmailer->SMTPDebug = 1;
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