<?php

namespace SocialAuther\Adapter;

class Odnoklassniki extends AbstractAdapter
{
    /**
     * Social Public Key
     *
     * @var string|null
     */
    protected $publicKey = null;

    /**
     * Constructor.
     *
     * @param array $config
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($config)
    {
        if (!is_array($config))
            throw new Exception\InvalidArgumentException(
                __METHOD__ . ' expects an array with keys: `client_id`, `client_secret`, `redirect_uri`, `public_key`'
            );
        else {
            if (isset($config['lang']))
                $this->lang = $config['lang'];
        }

        foreach (array('client_id', 'client_secret', 'redirect_uri', 'public_key') as $param) {
            if (!array_key_exists($param, $config)) {
                throw new Exception\InvalidArgumentException(
                    __METHOD__ . ' expects an array with key: `' . $param . '`'
                );
            } else {
                $property = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $param))));
                $this->$property = $config[$param];
            }
        }

        $this->fieldsMap = array(
            'id'         => 'uid',
            'email'      => 'email',
            'image'      => 'pic_2',
            'firstName'  => 'first_name',
            'secondName' => 'last_name',
        );

        $this->provider = 'odnoklassniki';
    }

    /**
     * Get user sex or null if it is not set
     *
     * @return string|null
     */
    public function getSex()
    {
        if (isset($this->response['gender']) && in_array($this->response['gender'], array('male', 'female'))) {
            return $this->response['gender'];
        }

        return null;
    }

    /**
     * Get user social id or null if it is not set
     *
     * @return string|null
     */
    public function getPage()
    {
        if (isset($this->response['uid'])) {
            return 'http://www.odnoklassniki.ru/profile/' . $this->response['uid'];
        }

        return null;
    }

    /**
     * Call to provider server, get access token, authenticate,
     * parse user profile data and return result of all this.
     *
     * @return boolean
     */
    protected function readUserProfile()
    {
        if (isset($_GET['code'])) {
            $params = array(
                'code' => $_GET['code'],
                'redirect_uri' => $this->redirectUri,
                'grant_type' => 'authorization_code',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret
            );

            $tokenInfo = $this->post('http://api.odnoklassniki.ru/oauth/token.do', $params);

            if (isset($tokenInfo['access_token']) && isset($this->publicKey))
            {
                $sign = md5("application_key={$this->publicKey}format=jsonmethod=users.getCurrentUser" . md5("{$tokenInfo['access_token']}{$this->clientSecret}"));

                $params = array(
                    'method'          => 'users.getCurrentUser',
                    'access_token'    => $tokenInfo['access_token'],
                    'application_key' => $this->publicKey,
                    'format'          => 'json',
                    'sig'             => $sign
                );

                $userInfo = $this->get('http://api.odnoklassniki.ru/fb.do', $params);

                if (isset($userInfo['uid'])) {
                    $this->parseUserData($userInfo);

                    if (isset($this->response['birthday'])) {
                        $birthDate = explode('.', $this->response['birthday']);
                        $this->userInfo['birthDay']   = isset($birthDate[0]) ? $birthDate[0] : null;
                        $this->userInfo['birthMonth'] = isset($birthDate[1]) ? $birthDate[1] : null;
                        $this->userInfo['birthYear']  = isset($birthDate[2]) ? $birthDate[2] : null;
                    }
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Prepare params for authentication url
     *
     * @return array
     */
    protected function prepareAuthParams()
    {
        return array(
            'auth_url'    => 'http://www.odnoklassniki.ru/oauth/authorize',
            'auth_params' => array(
                'client_id'     => $this->clientId,
                'response_type' => 'code',
                'redirect_uri'  => $this->redirectUri
            )
        );
    }
}