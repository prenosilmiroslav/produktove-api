<?php

declare(strict_types=1);

namespace App\ApiV1Module\Entity;

use Nette\Utils\DateTime;

class ProductEntity
{

    /** @var int|NULL $id */
    private int|NULL $id;

    /** @var string $name */
    private string $name;

    /** @var float $price */
    private float $price;

    /** @var DateTime $createdAt */
    private DateTime $createdAt;

    /** @var DateTime|null $updatedAt */
    private DateTime|null $updatedAt;


    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return $this
     */
    public function setId(?int $id): ProductEntity
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): ProductEntity
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     * @return $this
     */
    public function setPrice(float $price): ProductEntity
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt(DateTime $createdAt): ProductEntity
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTime|null $updatedAt
     * @return $this
     */
    public function setUpdatedAt(?DateTime $updatedAt): ProductEntity
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Přetransformuje entitu na pole
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = [];

        foreach ($this as $key => $value)
        {
            if (in_array($key, ['createdAt', 'updatedAt']))
            {
                $data[$key] = empty($value) ? NULL : $value->format('Y-m-d H:i:s');
            }
            else
            {
                $data[$key] = $value;
            }
        }

        return $data;
    }

}
