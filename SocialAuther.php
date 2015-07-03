<?php
/**
 * SocialAuther (http://socialauther.stanislasgroup.com/)
 *
 * @author Stanislav Protasevich
 * @author Andrey Izman <izmanw@gmail.com>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace SocialAuther;

use SocialAuther\Adapter\AdapterInterface;
use SocialAuther\Exception\InvalidArgumentException;


/**
 * Class SocialAuther
 * @package SocialAuther
 */
class SocialAuther
{
    /**
     * Adapter manager
     *
     * @var AdapterInterface
     */
    protected $adapter = null;


    /**
     * Constructor.
     *
     * @param string $provider
     * @param array $config
     * @throws InvalidArgumentException
     * @author Andrey Izman <izmanw@gmail.com>
     */
    public function __construct($provider, $config)
    {
        $adapter = 'SocialAuther\\Adapter\\'.ucfirst(strtolower($provider));
        try {
            $this->adapter = new $adapter($config);

        }
        catch (Exception $e) {
            throw new Exception\InvalidArgumentException(
                'Unknown provider : "'.$provider.'"'
            );
        }
    }

    /**
     * Call method login() of adapter class.
     *
     * Redirect to provider authentication url or
     * authenticate and read user profile when redirected back.
     *
     * @author Andrey Izman <izmanw@gmail.com>
     * @return boolean|SocialUserProfile
     */
    public function login()
    {
        return $this->adapter->login() ? $this->adapter->getUserProfile() : false;
    }

    /**
     * Getting user profile
     *
     * @author Andrey Izman <izmanw@gmail.com>
     * @return SocialUserProfile
     */
    public function getUserProfile()
    {
        return $this->adapter->getUserProfile();
    }

    /**
     * Checking for redirect from the provider
     *
     * @author Andrey Izman <izmanw@gmail.com>
     * @return boolean
     */
    public function isRedirected()
    {
        return $this->adapter->isRedirected();
    }

    /**
     * Checking for errors
     *
     * @author Andrey Izman <izmanw@gmail.com>
     * @return boolean
     */
    public function haveErrors()
    {
        return $this->adapter->haveErrors();
    }
}