<?php

namespace API\Dto;

class Entity
{
    private int $id;

    private ?string $field1 = null;

    private $field2;

    private $safedel;


    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getField1()
    {
        return $this->field1;
    }

    /**
     * @param mixed $field1
     */
    public function setField1($field1)
    {
        $this->field1 = $field1;
    }

    /**
     * @return mixed
     */
    public function getField2()
    {
        return $this->field2;
    }

    /**
     * @param mixed $field2
     */
    public function setField2($field2)
    {
        $this->field2 = $field2;
    }

    /**
     * @return mixed
     */
    public function getSafedel()
    {
        return $this->safedel;
    }

    /**
     * @param mixed $safedel
     */
    public function setSafedel($safedel)
    {
        $this->safedel = $safedel;
    }


}