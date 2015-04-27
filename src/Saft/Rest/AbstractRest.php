<?php

namespace Saft\Rest;

use Saft\Store\Store;

/**
 * http://coreymaynard.com/blog/creating-a-restful-api-with-php/
 */
abstract class AbstractRest
{
    /**
     * concrete implementation of StoreInterface.
     *
     * @var Store
     */
    protected $store;
    
    /**
     * The HTTP method this request was made in, either GET, POST or DELETE
     *
     * @var string
     */
    protected $method = '';
    
    /**
     * The Model requested in the URI. eg: /files
     *
     * @var string
     */
    protected $endpoint = '';
    
    /**
     * An optional additional descriptor about the endpoint, used for things that can not be handled by the
     * basic methods. eg: /files/process
     *
     * @var string
     */
    protected $verb = '';
    
    /**
     * Any additional URI components after the endpoint and verb have been removed, in our case, an integer ID
     * for the resource. eg: /<endpoint>/<verb>/<arg0>/<arg1> or /<endpoint>/<arg0>
     */
    protected $args = array();

    /**
     * Allow for CORS, assemble and pre-process the data.
     *
     * @param string        $request
     * @param AbstractStore $store   concrete Store.
     */
    public function __construct($request, Store $store)
    {
        // allow requests from any origin to be processed by this page
        header('Access-Control-Allow-Orgin: *');
        // allow for any HTTP method to be accepted.
        header('Access-Control-Allow-Methods: *');
        header('Content-Type: application/json');

        $this->store = $store;

        $this->args = explode('/', rtrim($request, '/'));

        $this->endpoint = array_shift($this->args);
        if (array_key_exists(0, $this->args) && !is_numeric($this->args[0])) {
            $this->verb = array_shift($this->args);
        }
        
        $this->method = $_SERVER['REQUEST_METHOD'];
        //DELETE and PUT requests are hidden inside a POST request
        if ($this->method == 'POST' && true === isset($_SERVER['HTTP_X_HTTP_METHOD'])) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $this->method = 'DELETE';
            } else {
                throw new \Exception('Unexpected Header');
            }
        }

        switch($this->method) {
            case 'DELETE':
            case 'POST':
                $this->request = $this->cleanInputs($_POST);
                break;
            case 'GET':
                $this->request = $this->cleanInputs($_GET);
                break;
            default:
                $this->response('Invalid Method', 405);
                break;
        }
    }
    
    /**
     * TODO find out its purpose
     */
    public function processAPI()
    {
        if (true === method_exists($this, $this->endpoint)) {
            return $this->response($this->{$this->endpoint}($this->args));
        }
        return $this->response('No Endpoint: '. $this->endpoint, 404);
    }

    /**
     * Gives HTTP response.
     *
     * @param  string  $data
     * @param  integer $status HTTP-Status
     * @return Returns given $data.
     */
    protected function response($data, $status = 200)
    {
        header('HTTP/1.1 '. $status .' '. $this->requestStatus($status));
        
        // TODO the JSON representation of $data.
        return $data;
    }

    /**
     * Remove HTML- and PHP Tags.
     *
     * @param  [type] $data [description]
     * @return string request-method
     */
    protected function cleanInputs($data)
    {
        $clean_input = array();
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = $this->cleanInputs($v);
            }
        } else {
            $clean_input = trim(strip_tags($data));
        }
        return $clean_input;
    }

    /**
     * Return HTTP-Status as String.
     *
     * @param  integer $code HTTP-Status
     * @return string http-status
     */
    protected function requestStatus($code)
    {
        $status = array(
            200 => 'OK',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        );
        return ($status[$code]) ? $status[$code] : $status[500];
    }
}
