<?php

class KioskApiClient {

    private $host;

    private $oauth_client_id;
    private $oauth_client_secret;

    private $bearer_token = null;
    private $bearer_token_expires;

    public function __construct($oauth_client_id=null, $oauth_client_secret=null, $host="https://api.smartrfi.kiosk.tm") {
        $this->setOauthClientId($oauth_client_id);
        $this->setOauthClientSecret($oauth_client_secret);
        $this->setHost($host);

        $this->bearer_token_expires = time();
    }

    public function setOauthClientId($oauth_client_id) { $this->oauth_client_id = $oauth_client_id; }
    public function getOauthClientId() { return $this->oauth_client_id; }

    public function setOauthClientSecret($oauth_client_secret) { $this->oauth_client_secret = $oauth_client_secret; }
    public function getOauthClientSecret() { return $this->oauth_client_secret; }

    public function setHost($host) { $this->host = $host; }
    public function getHost() { return $this->host; }

    public function authenticate($refresh=false) {
        if(!$refresh && $this->bearer_token != null && time() <= $this->bearer_token_expires)
            return $this->bearer_token;

        $ch = curl_init();

        $http_headers = array(
            'Content-Type: application/json', 
            'Accept: application/json' 
        );

        $payload = array(
            "grant_type"    => "client_credentials", 
            "client_id"     => $this->getOauthClientId(), 
            "client_secret" => $this->getOauthClientSecret()
        );

        curl_setopt($ch, CURLOPT_URL, $this->getHost() . '/oauth');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        if($result === null)
            return null;

        $result = json_decode($result, true);

        if(array_key_exists('access_token', $result)) {
            $this->bearer_token = $result['access_token'];
            $this->bearer_token_expires = time() + $result['expires_in'];
        } else {
            return null;
        }

        return $this->bearer_token;
    }

    public function submitProspect(array $prospect_fields) {
        $bearer_token = $this->authenticate();

        if($bearer_token == null)
            throw new \Exception("Authentication Failed");

        $http_headers = array(
            'Content-Type: application/json', 
            'Accept: application/json', 
            "Authorization: Bearer {$bearer_token}"
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->getHost() . '/prospect');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($prospect_fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if($response == null)
            return null;

        $result = json_decode($response, true);

        if(array_key_exists('status', $result) && $result['status'] == 'ok')
            return array(
                'status'      => 'ok', 
                'id'          => $result['id'], 
                'prospect_id' => $result['prospect_id']
            );
        else
            return array(
                'status'     => 'error', 
                'error'      => 'InvalidProspect', 
                'message'    => 'The Prospect you submitted had invalid field data.  Please check the "validation" element to see which fields were invalid.', 
                'validation' => $result['detail']['validation']
            );
    }
}

