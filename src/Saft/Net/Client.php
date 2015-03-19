<?php

namespace Saft\Net;

class Client
{
    /**
     * @var \Curl\Curl
     */
    protected $client;

    /**
     * @var string
     */
    protected $url;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->client = new \Curl\Curl();
    }

    /**
     *
     * @param
     * @return
     * @throw
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     *
     * @param
     * @return
     * @throw
     */
    public function sendDigestAuthentication($username, $password)
    {
        /**
         * Change that, after the following pull request was merged:
         * https://github.com/php-curl-class/php-curl-class/pull/142
         */

        curl_setopt($this->client->curl, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        curl_setopt($this->client->curl, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($this->client->curl, CURLOPT_RETURNTRANSFER, true);
        return $this->client->get($this->url);
    }

    /**
     *
     * @param string $query
     * @return
     * @throw
     */
    public function sendSparqlSelectQuery($query)
    {
        // TODO extend Accept headers to further formats
        $this->client->setHeader("Accept", "application/sparql-results+json");
        $this->client->setHeader("Content-Type", "application/x-www-form-urlencoded");

        return $this->client->post($this->url, array("query" => $query));
    }

    /**
     *
     * @param string $query
     * @return
     * @throw
     */
    public function sendSparqlUpdateQuery($query)
    {
        // TODO extend Accept headers to further formats
        $this->client->setHeader("Accept", "application/sparql-results+json");
        $this->client->setHeader("Content-Type", "application/sparql-update");
        return $this->client->get($this->url, array("query" => $query));
    }

    /**
     *
     * @param
     * @return
     * @throw
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }
}
