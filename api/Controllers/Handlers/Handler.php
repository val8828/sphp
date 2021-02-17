<?php


namespace API\Controllers\Handlers;


use API\config\Database;
use API\Controllers\Handlers\Traits\EntityTrait;
use API\Controllers\Handlers\Traits\SecurityTrait;
use API\Dto\EntityDao;
use API\Dto\UserDAO;

class Handler
{
    use EntityTrait, SecurityTrait;

    public function __construct()
    {
        $this->database = new Database();
        $this->databaseConnect = $this->database->getConnection();
        $this->entityDao = new EntityDao();
        $this->userDao = new UserDAO();
    }
}