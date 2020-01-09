<?php
/**
 * VKontakteOAuth2Service class file.
 *
 * Register application: http://vk.com/editapp?act=create&site=1
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace yareg\eauth\services;

use yareg\eauth\oauth2\Service;
use OAuth\OAuth2\Service\ServiceInterface;

/**
 * VKontakte provider class.
 *
 * @package application.extensions.eauth.services
 */
class VKontakteOAuth2Service extends Service
{
	const SCOPE_EMAIL   = 'email';
	const SCOPE_FRIENDS = 'friends';
	const API_VERSION   = '5.103';

	protected $name        = 'vkontakte';
	protected $title       = 'VK.com';
	protected $type        = 'OAuth2';
	protected $jsArguments = ['popup' => ['width' => 585, 'height' => 350]];

	protected $scopes = [self::SCOPE_EMAIL];
	protected $providerOptions = [
		'authorize'    => 'https://api.vk.com/oauth/authorize',
		'access_token' => 'https://api.vk.com/oauth/access_token',
	];
	protected $baseApiUrl = 'https://api.vk.com/method/';

	protected $response;
    protected $fields = ''; // 'nickname, sex, bdate, city, country, timezone, photo, photo_medium, photo_big, photo_rec'
                            // uid, first_name and last_name is always available

    /**
     * @return bool
     * @throws \yareg\eauth\ErrorException
     */
	protected function fetchAttributes() : bool
	{
		$tokenData = $this->getAccessTokenData();

		$data = $this->makeSignedRequest('users.get', [
			'query' => [
				'uids'   => $tokenData['params']['user_id'],
				'fields' => $this->fields,
				'v'      => self::API_VERSION,
			],
		]);

		if(empty($data['response'])) { return false; }

        $this->response = $data['response'][0];

        $this->attributes['id']   = $this->response['id'];
        $this->attributes['name'] = $this->response['first_name'] . ' ' . $this->response['last_name'];
        $this->attributes['url']  = 'https://vk.com/id' . $this->response['id'];

        /*if (!empty($info['nickname']))
            $this->attributes['username'] = $info['nickname'];
        else
            $this->attributes['username'] = 'id'.$info['uid'];

        $this->attributes['gender'] = $info['sex'] == 1 ? 'F' : 'M';

        $this->attributes['city'] = $info['city'];
        $this->attributes['country'] = $info['country'];

        $this->attributes['timezone'] = timezone_name_from_abbr('', $info['timezone']*3600, date('I'));;

        $this->attributes['photo'] = $info['photo'];
        $this->attributes['photo_medium'] = $info['photo_medium'];
        $this->attributes['photo_big'] = $info['photo_big'];
        $this->attributes['photo_rec'] = $info['photo_rec'];*/

        return true;
	}

	/**
	 * Returns the error array.
	 *
	 * @param array $response
	 * @return array | null the error array with 2 keys: code and message. Should be null if no errors.
	 */
	protected function fetchResponseError($response)
	{
		if (isset($response['error'])) {
			return [
				'code' => is_string($response['error']) ? 0 : $response['error']['error_code'],
//				'message' => is_string($response['error']) ? $response['error'] : $response['error']['error_msg'],
//				'message' => is_string($response['error']) ? $response['error'] : $response['error']['error_msg'],
			];
		}

		return null;
	}

	/**
	 * @param array $data
	 * @return string|null
	 */
	public function getAccessTokenResponseError($data)
	{
		if (!isset($data['error'])) {
			return null;
		}
		$error = $data['error'];
		if (isset($data['error_description'])) {
			$error .= ': ' . $data['error_description'];
		}
		return $error;
	}

	/**
	 * Returns a class constant from ServiceInterface defining the authorization method used for the API.
	 *
	 * @return int
	 */
	public function getAuthorizationMethod() : int
	{
		return ServiceInterface::AUTHORIZATION_METHOD_QUERY_STRING;
	}

}