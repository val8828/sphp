<?php

namespace API\Dto;

use PDO;

class EntityDao
{
    function getEntitiesByFields($conn, $field1, $field2 , $userId)
    {
        $stmt = $conn->prepare("SELECT entity.id, entity.field1, entity.field2, entity.safedel 
                                FROM entity LEFT JOIN users_entities
                                ON entity.id = users_entities.entityid
                                WHERE users_entities.userid = :userId 
                                AND entity.field1 = :field1 
                                AND entity.field2 = :field2");
        $stmt->execute(['userId' => $userId, 'field1' => $field1, 'field2' => $field2]);

        $stmt->setFetchMode(PDO::FETCH_CLASS, 'API\Dto\Entity');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function getEntityById($conn, $id) : ?Entity
    {
        $stmt = $conn->prepare("SELECT * FROM entity WHERE id = :id");

        $stmt->execute(['id' => $id]);

        $stmt->setFetchMode(PDO::FETCH_CLASS, 'API\Dto\Entity');

        return $stmt->fetch();
    }

    function getEntityByIdForUser($conn, $id, $userId)
    {
        $stmt = $conn->prepare("SELECT entity.id, entity.field1, entity.field2, entity.safedel 
                                FROM entity LEFT JOIN users_entities
                                ON entity.id = users_entities.entityid
                                WHERE users_entities.userid = :userId AND entity.id = :entityId");

        $stmt->execute(['entityId' => $id, 'userId' => $userId]);

        $stmt->setFetchMode(PDO::FETCH_CLASS, 'API\Dto\Entity');

        return $stmt->fetch();
    }

    function createEntity(PDO $conn, $field1, $field2, $user) : ?Entity
    {
        $stmt = $conn->prepare("INSERT INTO entity(field1, field2, safedel) VALUES(:field1, :field2, DEFAULT)");

        $stmt->execute(['field1' => $field1, 'field2' => $field2]);

        $entityId = $conn->lastInsertId();

        $stmt = $conn->prepare("INSERT INTO users_entities(userid, entityid) VALUES(:userid,:entityId)") ; /// :userId,:etityId)");

        $userId = $user->getId();

        $stmt->execute(['userid' => $userId, 'entityId' => $entityId]);

        return $this->getEntityById($conn, $entityId);
    }

    function isOwn($conn, $userId, $entityId): bool
    {
        $stmt = $conn->prepare("SELECT * FROM users_entities WHERE userid=:userId AND entityid = :entityId");

        $stmt->execute(['userId' => $userId, 'entityId' => $entityId]);

        return (($stmt->fetch()) > 0);
    }

    function updateEntity($conn, $id, $field1, $field2, $safedel) : ?Entity
    {
        $sql = "UPDATE entity SET field1=?, field2=?, safedel=? WHERE id=?";

        $conn->prepare($sql)->execute([$field1, $field2, $safedel, $id]);

        return $this->getEntityById($conn, $id);
    }

    function deleteEntity($conn, $entityId)
    {
        $stmt = $conn->prepare("DELETE FROM entity WHERE id=:id");

        $stmt->execute(['id' => $entityId]);

        $stmt = $conn->prepare("DELETE FROM users_entities WHERE entityid=:id");

        $stmt->execute(['id' => $entityId]);
    }

    function safeDeleteEntity($conn, $entityId) : ?Entity
    {
        $sql = "UPDATE entity SET safedel=? WHERE id=?";

        $conn->prepare($sql)->execute([1, $entityId]);

        return $this->getEntityById($conn, $entityId);
    }

    function getNextId($conn) : ?int
    {
        $stmt = $conn->query("SELECT MAX(id) FROM entity");

        $row = $stmt->fetch();

        if ($row) {
            return ($row['MAX(id)']) + 1;
        } else {
            return 1;
        }
    }

    public function getEntityOwner($conn, $entityId) {
        $stmt = $conn->prepare("SELECT userid FROM users_entities WHERE entityid=:id");

        $stmt->execute(['id' => $entityId]);

        return $stmt->fetchAll();
    }

    public function deleteUserPrivilegesFromEntity($conn, $userId, $entityId){

        $stmt = $conn->prepare("DELETE FROM users_entities WHERE entityid=:id AND userid=:userid");

        $stmt->execute(['id' => $entityId, 'userid' => $userId]);
    }
}