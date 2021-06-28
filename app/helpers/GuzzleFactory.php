<?php

namespace App\Helpers;

use Composer\CaBundle\CaBundle;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\SessionCookieJar;
use GuzzleHttp;

/**
 * Cookie jar extending guzzle SessionCookieJar. On save cookies from cookie jar
 * are stored into server sessions and can be used even during further requests.
 */
class MySessionCookieJar extends SessionCookieJar
{
    /** Session key identification */
    private $sessionKey;

    /**
     * Constructor.
     * @param string $sessionKey
     */
    public function __construct($sessionKey)
    {
        $this->sessionKey = $sessionKey;
        parent::__construct($sessionKey);
    }

    /**
     * Save cookies from jar to the server sessions.
     */
    public function save()
    {
        $json = [];
        foreach ($this as $cookie) {
            $json[] = $cookie->toArray();
        }

        $_SESSION[$this->sessionKey] = json_encode($json);
    }
}

/**
 * Factory method for guzzle client with some meaningful defaults. Alongside
 * that there is factory for special requests permanent cookie jar. Destruction
 * of the cookie jar and its content should be also done from here.
 */
class GuzzleFactory
{
    /**
     * Create guzzle client with default settings.
     * @return GuzzleHttp\Client
     */
    public function createGuzzleClient()
    {
        return new Client([
            'defaults' => [
                "verify" => CaBundle::getSystemCaRootBundlePath(),
            ]
        ]);
    }

    /**
     * Create special permament cookie jar.
     * @return MySessionCookieJar
     */
    public function createMySessionCookieJar()
    {
        return new MySessionCookieJar("ifmsa_id");
    }

    /**
     * Remove cached cookie jar from the servers sessions.
     */
    public function destroySessionCookie()
    {
        unset($_SESSION["ifmsa_id"]);
    }
}
