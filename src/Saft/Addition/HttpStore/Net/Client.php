<?php

namespace Saft\Addition\HttpStore\Net;

use Curl\Curl;

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
        $this->client = new Curl();
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
     * Send digest authentication to the server via GET.
     *
     * @param  string $username
     * @param  string $password optional
     * @return string
     */
    public function sendDigestAuthentication($username, $password = null)
    {
        $this->client->setDigestAuthentication($username, $password);

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
