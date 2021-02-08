<?php
////require "vendor/autoload.php";
//
//use \Firebase\JWT\JWT;
//
//class AuthController
//{
//    private $database;
//
//    private $userDao;
//
//    public function __construct($database)
//    {
//        $this->database = $database->getConnection();
//        $this->userDao = new UserDAO();
//    }
//
//
//    private function generateToken($login)
//    {
//        $secret_key = "SECRET_KEY";//TODO вынести  константы и поместить в .conf
//        $issuer_claim = "THE_ISSUER"; //TODO вынести  константы и поместить в .conf
//        $audience_claim = "THE_AUDIENCE";//TODO вынести константы и поместить в .conf
//        $issuedat_claim = time(); // issued at
//        $notbefore_claim = $issuedat_claim + 10; //TODO вынести  константы и поместить в .conf
//        $expire_claim = $issuedat_claim + 300; //TODO вынести  константы и поместить в .conf
//        $token = array(
//            "iss" => $issuer_claim,
//            "aud" => $audience_claim,
//            "iat" => $issuedat_claim,
//            "nbf" => $notbefore_claim,
//            "exp" => $expire_claim,
//            "data" => array(
//                "login" => $login
//            ));
//
//        return JWT::encode($token, $secret_key);
//    }
//
//    private function isPasswordCorrect($login, $password)
//    {
//        $userFromBase = $this->userDao->getUser($this->database, $login);
//        return $this->encodePassword($password) == $userFromBase->getPassword();
//    }
//
//    private function checkUserExists($login)
//    {
//        return ($this->userDao->getUser($this->database, $login)) > 0;
//    }
//
//    private function encodePassword($password)
//    {
//
//        return password_hash($password, PASSWORD_BCRYPT);
//    }
//
//    private function addUserToBase($login, $password, $token)
//    {
//        $user = new User();
//
//        $user->setPassword($this->encodePassword($password));
//        $user->setLogin($login);
//        $user->setToken($token);
//
//        $this->userDao->createUser($this->database, $user);
//    }
//
//    private function updateUserTokenInBase($login, $token)
//    {
//        $this->userDao->updateUserToken($this->database, $login, $token);
//    }
//
//    function register()
//    {
//        $postData = file_get_contents('php://input');
//        $data = json_decode($postData, true);
//        $userLogin = $data["login"];
//        $userPassword = $data["password"];
//
//        $token = $this->generateToken($userLogin);
//
//        if ($this->checkUserExists($userLogin)) {
//            $userPasswordInBase = ($this->userDao->getUserPassword($this->database, $userLogin))['password'];
//            if (password_verify($userPassword, $userPasswordInBase)) {
//
//                $this->updateUserTokenInBase($userLogin, $token);
//                echo json_encode(
//                    array(
//                        "token" => $token,
//                    )
//                );
//            } else {
//                http_response_code(401);
//                echo json_encode(array("message" => "Login failed."));
//            }
//        } else {
//            $this->addUserToBase($userLogin, $userPassword, $token);
//            echo json_encode(
//                array(
//                    "token" => $token,
//                )
//            );
//        }
//
//    }
//
//
//}
