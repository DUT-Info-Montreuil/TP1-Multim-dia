<?php
class csrf {

    private $token;
    private $expiration;

    public function __construct() {
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_expiration']) || $_SESSION['csrf_expiration'] < time()) {
            $this->generateToken();
        } else {
            $this->token = $_SESSION['csrf_token'];
            $this->expiration = $_SESSION['csrf_expiration'];
        }
    }

    private function generateToken() {
        $this->token = bin2hex(random_bytes(32));
        $this->expiration = time() + 1200;

        $_SESSION['csrf_token'] = $this->token;
        $_SESSION['csrf_expiration'] = $this->expiration;
    }

    public function getToken() {
        return $this->token;
    }

    public function validate($token) {
        if ($this->expiration < time()) {
            return false;
        }
        return hash_equals($this->token, $token);
    }
}