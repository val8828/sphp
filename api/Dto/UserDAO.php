<?php

namespace API\Dto;

use Exception;
use PDO;

class UserDAO
{

    public function getUser($conn, $login)
    {

        $stmt = $conn->prepare("SELECT id, login, password, token FROM users WHERE login =:login LIMIT 1");

        $stmt->execute(['login' => $login]);

        if($stmt->setFetchMode(PDO::FETCH_CLASS, 'API\Dto\User')){
            return $stmt->fetch();
        }else{
            return null;
        }
    }

    public function createUser($conn, User $user) : User
    {
        //Check if user already exist
        $oldUser = $this->getUser($conn,$user->getLogin());

        if($oldUser == false) {

            $stmt = $conn->prepare("INSERT INTO users(login, password, token) VALUES(:login, :password, :token)");

            $stmt->execute(['login' => $user->getLogin(), 'password' => $user->getPassword(), 'token' => $user->getToken()]);

            return $this->getUser($conn,$user->getLogin());
        }else{
            return $oldUser;
        }
    }

    public function removeUser($conn,$user)
    {
        $stmt = $conn->prepare("DELETE FROM users WHERE login = :login");

        $stmt->execute(['login' => $user->getLogin()]);
    }

    public function updateUserPassword($conn,$user)
    {
        $stmt = $conn->prepare("UPDATE users SET password = :password WHERE login = :login");

        $stmt->execute(['login' => $user->getLogin(), 'password' => $user->getPassword()]);
    }

    public function updateUserToken($conn, $login, $token)
    {
        $stmt = $conn->prepare("UPDATE users SET token = :token WHERE login = :login");

        $stmt->execute(['login' => $login, 'token' => $token]);
    }

    public function getUserPassword($conn, $login): ?string
    {
        $stmt = $conn->prepare("SELECT password FROM users WHERE login = :login");

        $stmt->execute(['login' => $login]);

        return $stmt->fetch()['password'];
    }
}
