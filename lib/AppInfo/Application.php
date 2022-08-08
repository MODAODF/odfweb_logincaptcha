<?php
namespace OCA\LoginCaptcha\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;

use OCP\User\Events\BeforeUserLoggedInEvent;
use OCA\LoginCaptcha\Listener\BeforeUserLoggedInEventListener;

class Application extends App implements IBootstrap {

	const APP_NAME = 'logincaptcha';

	const CAPTCHA_NAME = 'login_captcha_code';
	const CAPTCHA_FAILED_NAME = 'login_cpatcha_failed';

	public function __construct() {
		parent::__construct(self::APP_NAME);
	}

	public function register(IRegistrationContext $context): void {

		$context->registerEventListener(BeforeUserLoggedInEvent::class, BeforeUserLoggedInEventListener::class);

		$request = \OC::$server->getRequest();
		if (isset($request->server['REQUEST_URI'])) {
			$url = $request->server['REQUEST_URI'];
			if (preg_match('%/login(\?.+)?$%m', $url)) {
				\OCP\Util::addScript(self::APP_NAME, 'main');
				\OCP\Util::addStyle(self::APP_NAME, 'main');
			}
		}
	}

	public function boot(IBootContext $context): void {
		$session = \OC::$server->getSession();
		$cpatchaFail = $session->get(self::CAPTCHA_FAILED_NAME);
		if ($cpatchaFail) {
			\OCP\Util::addScript(self::APP_NAME, 'error');
			$session->remove(self::CAPTCHA_FAILED_NAME);
		}
	}
}
