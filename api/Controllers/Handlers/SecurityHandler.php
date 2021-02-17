<?php


namespace API\Controllers\Handlers;


use API\config\Database;
use API\Dto\User;
use API\Dto\UserDAO;
use Exception;
use Firebase\JWT\JWT;
use PDO;

class SecurityHandler
{
    private Database $database;
    private ?PDO $databaseConnect;

    private UserDAO $userDao;

    /**
     * Security constructor.
     */
    public function __construct()
    {
        $this->database = new Database();
        $this->databaseConnect = $this->database->getConnection();
    }

    public static function encodePassword($password): ?string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function extractLoginFromToken(): ?string
    {
        $secret_key = SECRET_KEY;
        $jwt = null;

        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];

        $arr = explode(" ", $authHeader);

        $jwt = $arr[1];

        if ($jwt) {

            try {

                JWT::$leeway = 60; // $leeway in seconds

                $decodeData = ((array)(JWT::decode($jwt, $secret_key, array('HS256'))))["data"];

                return ((array)$decodeData)['login'];

            } catch (Exception $e) {
                return false;
            }
        }
        return false;
    }

    public function addUserToBase($login, $password, $token)
    {
        $user = new User();
        $this->userDao = new UserDAO();

        $user->setPassword($this->encodePassword($password));
        $user->setLogin($login);
        $user->setToken($token);

        $this->userDao->createUser($this->databaseConnect, $user);
    }

    public function is_userExists($login): bool
    {
        $this->userDao = new UserDAO();
        return ($this->userDao->getUser($this->databaseConnect, $login)) > 0;
    }

    public function checkPassword(string $userLogin, string $userPassword): bool
    {
        $userPasswordInBase =
            ($this->userDao->getUserPassword($this->databaseConnect, $userLogin));
        return password_verify($userPassword, $userPasswordInBase);
    }


    public function generateToken($login): string
    {
        $secret_key = "SECRET_KEY";//TODO вынести  константы и поместить в .conf
        $issuer_claim = "THE_ISSUER"; //TODO вынести  константы и поместить в .conf
        $audience_claim = "THE_AUDIENCE";//TODO вынести константы и поместить в .conf
        $issuedat_claim = time(); // issued at
        $notbefore_claim = $issuedat_claim + 10; //TODO вынести  константы и поместить в .conf
        $expire_claim = $issuedat_claim + 300; //TODO вынести  константы и поместить в .conf
        $token = array(
            "iss" => $issuer_claim,
            "aud" => $audience_claim,
            "iat" => $issuedat_claim,
            "nbf" => $notbefore_claim,
            "exp" => $expire_claim,
            "data" => array(
                "login" => $login
            ));

        return JWT::encode($token, $secret_key);
    }
    public function isTokenValid(): bool
    {
        $secret_key = SECRET_KEY;
        $jwt = null;

        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];

        $arr = explode(" ", $authHeader);

        $jwt = $arr[1];

        if ($jwt) {

            try {

                JWT::$leeway = 60; // $leeway in seconds

                JWT::decode($jwt, $secret_key, array('HS256'));//[data[login]]

                return true;

            } catch (Exception $e) {
                return false;
            }
        }
        return false;
    }

    public function updateUserTokenInBase($login, $token)
    {
        $this->userDao = new UserDAO();
        $this->userDao->updateUserToken($this->databaseConnect, $login, $token);
    }

    public function getUserId($login) :?int{
        $this->userDao = new UserDAO();
        $user = $this->userDao->getUser($this->databaseConnect, $login);
        if(is_null($user)){
            return null;
        }else{
            return $user->getId();
        }
    }
}