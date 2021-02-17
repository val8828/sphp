<?php


namespace API\Controllers\Handlers;


use API\config\Database;
use API\Dto\Entity;
use API\Dto\EntityDao;
use API\Dto\UserDAO;
use PDO;

class EntityHandler
{
    private Database $database;
    private ?PDO $databaseConnect;
    private EntityDao $entityDao;
    private UserDAO $userDao;

    /**
     * EntityHandler constructor.
     */
    public function __construct()
    {
        $this->database = new Database();
        $this->databaseConnect = $this->database->getConnection();
        $this->entityDao = new EntityDao();
        $this->userDao = new UserDAO();
    }

    public function createEntity(string $field1, string $field2): Entity
    {
        return $this->entityDao->createEntity(
            $this->databaseConnect,
            $field1, $field2,
            $this->userDao->getUser($this->databaseConnect, SecurityHandler::extractLoginFromToken()));

    }

    public function deleteEntity(int $id)
    {
        $this->entityDao->deleteEntity($this->databaseConnect, $id);
    }

    public function safeDeleteEntity(int $id)
    {
        $this->entityDao->deleteEntity($this->databaseConnect, $id);
    }

    public function updateEntity(int $id, string $field1, string $field2, int $safedel) :? Entity{
        return $this->entityDao->updateEntity($this->databaseConnect, $id,$field1,$field2, $safedel);
    }

    public function getEntityOwnerId($id)
    {
        return $this->entityDao->getEntityOwner($this->databaseConnect, $id);
    }

    public function deleteUserPrivilegesFromEntity($userId, $entityId){
        $this->entityDao->deleteUserPrivilegesFromEntity($this->databaseConnect, $userId, $entityId);
        //TODO удалять из базы сущности без владельца
    }

    public function getEntitiesByFields($field1, $field2 , $userId) {
        return $this->entityDao->getEntitiesByFields($this->databaseConnect, $field1,$field2,$userId);
    }

    public function getEntityById($id, $userId) {
        return $this->entityDao->getEntityByIdForUser($this->databaseConnect, $id, $userId);
    }
}