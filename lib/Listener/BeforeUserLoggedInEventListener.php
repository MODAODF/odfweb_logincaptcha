<?php


namespace OCA\LoginCaptcha\Listener;

use OCA\LoginCaptcha\AppInfo\Application;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\User\Events\BeforeUserLoggedInEvent;

class BeforeUserLoggedInEventListener implements IEventListener {

	public function handle(Event $event): void {
		if (!$event instanceof BeforeUserLoggedInEvent) {
			return;
		}
        try {
            $captcha = \OC::$server->getRequest()->getParam('captcha', '');
            $savedCaptcha = \OC::$server->getSession()->get(Application::CAPTCHA_NAME);
            $isOK = (strtolower($savedCaptcha) == strtolower(trim($captcha)));
            if ($isOK) {
                return;
            } else {
                throw new \Exception('captcha');
            }
        } catch (\Exception $e) {
            \OC::$server->getSession()->set(Application::CAPTCHA_FAILED_NAME, true);
            header('Location:' . $this->getRedirectUrl($event->getUsername()));
            exit;
        }
	}

    /**
	 * 驗證無效，重新導向登入頁面
	 *
	 * @NoCSRFRequired
	 * @PublicPage
     * @UseSession
	 *
	 * @param string $username
	 * @return string
	 */
	private function getRedirectUrl($username) {
		$urlGenerator = \OC::$server->getURLGenerator();
        $resuest = \OC::$server->getRequest();
		$args = $username !== null ? ['user' => $username] : [];
		$redirectUrl = $resuest->getServerProtocol() . '://';
		$redirectUrl .= $resuest->getServerHost();
		$redirectUrl .= $urlGenerator->linkToRoute('core.login.showLoginForm', $args);
		return $redirectUrl;
	}
}
