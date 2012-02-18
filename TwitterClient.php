<?php
/*
    200  OK                     Success!
    304  Not Modified           There was no new data to return.
    400  Bad Request            The request was invalid. An accompanying error message will explain why.
                                This is the status code will be returned during rate limiting.
    401  Unauthorized           Authentication credentials were missing or incorrect.
    403  Forbidden              The request is understood, but it has been refused.
                                An accompanying error message will explain why.
                                This code is used when requests are being denied due to update limits.
    404  Not Found              The URI requested is invalid or the resource requested, such as a user, does not exists.
    406  Not Acceptable         Returned by the Search API when an invalid format is specified in the request.
    420  Enhance Your Calm      Returned by the Search and Trends API when you are being rate limited.
    500  Internal Server Error  Something is broken. Please post to the group so the Twitter team can investigate.
    502  Bad Gateway            Twitter is down or being upgraded.
    503  Service Unavailable    The Twitter servers are up, but overloaded with requests. Try again later.
*/

class TwitterException extends Exception {
}

class TwitterClient {
    const API_URL = 'https://api.twitter.com/1/';

    const JSON = 'json';
    const XML  = 'xml';

    private $response_format = self::JSON;
    private $response_formats = array(
        self::JSON,
        self::XML,
    );
    private $oauth;

    public function __construct($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret) {
        $this->connect($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret);
    }

    public function setResponseFormat($format) {
        if (in_array($format, $this->response_formats)) {
            $this->response_format = $format;
        } else {
            throw new InvalidArgumentException('Unsupported response format');
        }
    }

    public function get($path, array $params, $expected_status=200) {
        return $this->request($path, $params, $expected_status, OAUTH_HTTP_METHOD_GET);
    }

    public function post($path, array $params, $expected_status=200) {
        return $this->request($path, $params, $expected_status, OAUTH_HTTP_METHOD_POST);
    }

    private function connect($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret) {
        $this->oauth = new OAuth($consumer_key, $consumer_secret, OAUTH_SIG_METHOD_HMACSHA1);
        $this->oauth->setToken($oauth_token, $oauth_token_secret);
        $this->oauth->enableDebug();
    }

    private function request($path, array $params, $expected_status, $method) {
        $response = null;
        try {
            $url      = self::API_URL . $path . '.'. $this->response_format;
            $data     = $this->oauth->fetch($url, $params, $method);
            $response = $this->oauth->getLastResponse();
            $info     = $this->oauth->getLastResponseInfo();
            $status   = (int)$info['http_code'];
            if ($status != $expected_status) {
                throw new RuntimeException("$url: expected HTTP $expected_status; got $status ($response)");
            }
        } catch (OAuthException $e) {
            $message  = $e->getMessage();
            $response = $this->oauth->getLastResponse();
            $info     = $this->oauth->getLastResponseInfo();
            error_log($message);
            error_log(print_r($response, true));
            error_log(print_r($info, true));
            throw new TwitterException($response, (int)$info['http_code']);
        }
        return $response;
    }
}
