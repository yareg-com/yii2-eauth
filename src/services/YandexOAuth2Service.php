<?php
/**
 * YandexOAuth2Service class file.
 *
 * Register application: https://oauth.yandex.ru/client/my
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace yareg\eauth\services;

use OAuth\Common\Token\TokenInterface;
use yareg\eauth\oauth2\Service;

/**
 * Yandex OAuth provider class.
 *
 * @package application.extensions.eauth.services
 */
class YandexOAuth2Service extends Service
{

	protected $name = 'yandex_oauth';
	protected $title = 'Yandex';
	protected $type = 'OAuth2';
	protected $jsArguments = ['popup' => ['width' => 500, 'height' => 450]];
	protected $tokenDefaultLifetime = TokenInterface::EOL_NEVER_EXPIRES;

	protected $scope = [];
	protected $providerOptions = [
		'authorize' => 'https://oauth.yandex.ru/authorize',
		'access_token' => 'https://oauth.yandex.ru/token',
	];

    protected $response;

    /**
     * @return bool
     * @throws \yareg\eauth\ErrorException
     */
	protected function fetchAttributes() : bool
	{
        $this->response = $this->makeSignedRequest('https://login.yandex.ru/info');

		$this->attributes['id']      = $this->response['id'] ?? '';
		$this->attributes['name']    = $this->response['real_name'] ?? '';
		//$this->attributes['login'] = $this->response['display_name'];
		//$this->attributes['email'] = $this->response['emails'][0];
		$this->attributes['email']   = $this->response['default_email'] ?? '';
		$this->attributes['gender']  = ($this->response['sex'] === 'male') ? 'M' : 'F';

		return true;
	}

}