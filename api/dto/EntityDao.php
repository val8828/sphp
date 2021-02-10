<?php

namespace API\dto;

class EntityDao
{
    function getEntitiesByFields($conn, $field1, $field2)
    {
        $stmt = $conn->prepare("SELECT * FROM entity WHERE field1 = :field1 AND field2 = :field2");

        $stmt->execute(['field1' => $field1, 'field2' => $field2]);

        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Entity');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function getEntityById($conn, $id)
    {
        $stmt = $conn->prepare("SELECT * FROM entity WHERE id = :id");

        $stmt->execute(['id' => $id]);

        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Entity');

        return $stmt->fetch();
    }

    function createEntity(PDO $conn, $field1, $field2, $user)
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

    function updateEntity($conn, $id, $field1, $field2, $safedel)
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

    function safeDeleteEntity($conn, $entityId)
    {
        $sql = "UPDATE entity SET safedel=? WHERE id=?";

        $conn->prepare($sql)->execute([1, $entityId]);

        return $this->getEntityById($conn, $entityId);
    }

    function getNextId($conn)
    {
        $stmt = $conn->query("SELECT MAX(id) FROM entity");

        $row = $stmt->fetch();

        if ($row) {
            return ($row['MAX(id)']) + 1;
        } else {
            return 1;
        }
    }
}