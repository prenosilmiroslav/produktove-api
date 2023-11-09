<?php

declare(strict_types=1);

namespace App\ApiV1Module\Model;

use App\ApiV1Module\Entity\ProductEntity;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\SmartObject;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;

class ProductModel
{

    use SmartObject;

    /** @var Explorer $database */
    private Explorer $database;

    /** @var string $tableName */
    private string $tableName = 'product';

    /** @var string[] $allowSort Povolené řazení */
    private array $allowSort = ['ASC', 'DESC'];

    /** @var array $config */
    private array $config;


    public function __construct(Explorer $database, array $config)
    {
        $this->database = $database;
        $this->config = $config;
    }

    /**
     * Vrací limit na stránku z parametrů
     *
     * @return int|null
     */
    public function getLimit(): int|null
    {
        return empty($this->config['limit']) ? NULL : $this->config['limit'];
    }

    /**
     * Vrátí seznam všech produktů podle zadaných parametrů a podle zadaného stránkování
     *
     * @param array $options
     * @param int|NULL $length
     * @param int|NULL $offset
     * @return array
     * @throws \Exception
     */
    public function getAll(array $options = [], int $length = NULL, int $offset = NULL): array
    {
        $rows = $this->database->table($this->tableName);

        // Filtrace
        if (!empty($options['name']))
        {
            $filterName = Strings::lower($options['name']);
            $rows->where('LOWER(name) LIKE ?', "%{$filterName}%");
        }
        if (!empty($options['priceFrom']))
        {
            $rows->where('price >= ?', $options['priceFrom']);
        }
        if (!empty($options['priceTo']))
        {
            $rows->where('price <= ?', $options['priceTo']);
        }
        if (!empty($options['createdFrom']))
        {
            $rows->where('DATE(created_at) >= ?', (new DateTime($options['createdFrom']))->format('Y-m-d'));
        }
        if (!empty($options['createdTo']))
        {
            $rows->where('DATE(created_at) <= ?', (new DateTime($options['createdTo']))->format('Y-m-d'));
        }
        if (!empty($options['updatedFrom']))
        {
            $rows->where('DATE(updated_at) >= ?', (new DateTime($options['updatedFrom']))->format('Y-m-d'));
        }
        if (!empty($options['updatedTo']))
        {
            $rows->where('DATE(updated_at) <= ?', (new DateTime($options['updatedTo']))->format('Y-m-d'));
        }

        // Řazení
        if (!empty($options['order']))
        {
            // Ošetření vstupu pro sortování výsledků
            $sort = !empty($options['by']) && in_array(Strings::upper($options['by']), $this->allowSort) ? Strings::upper($options['by']) : $this->allowSort[0];

            switch ($options['order'])
            {
                case 'name':
                    $order = "name {$sort}";
                    break;

                case 'price':
                    $order = "price {$sort}";
                    break;

                case 'createdAt':
                    $order = "created_at {$sort}";
                    break;

                case 'updatedAt':
                    $order = "updated_at {$sort}";
                    break;

                default:
                    $order = 'id ASC';
            }

            $rows->order($order);
        }
        else
        {
            $rows->order('id ASC');
        }

        // Stránkování
        if (!empty($length))
        {
            $rows->limit($length, $offset);
        }

        $fetch = $rows->fetchAll();

        $list = [];

        foreach ($fetch as $row)
        {
            $list[] = $this->mapping($row);
        }

        return $list;
    }

    /**
     * Vrátí celkový počet produktů podle parametrů
     *
     * @param array $options
     * @return int
     */
    public function getAllCount(array $options = []): int
    {
        $rows = $this->getAll($options);
        return count($rows);
    }

    /**
     * Vrátí konkrétní produkt podle ID
     *
     * @param int $id
     * @return ProductEntity|null
     */
    public function get(int $id): ?ProductEntity
    {
        $row = $this->database->table($this->tableName)
            ->where('id = ?', $id)
            ->fetch();

        $product = NULL;

        if ($row)
        {
            $product = $this->mapping($row);
        }

        return $product;
    }

    /**
     * Vloží nový produkt
     *
     * @param ProductEntity $entity
     * @return ProductEntity|null
     */
    public function insert(ProductEntity $entity): ?ProductEntity
    {
        $row = $this->database->table($this->tableName)
            ->insert([
                'name' => $entity->getName(),
                'price' => $entity->getPrice(),
                'created_at' => $entity->getCreatedAt(),
            ]);

        return empty($row) ? NULL : $this->mapping($row);
    }

    /**
     * Uloží produkt
     *
     * @param ProductEntity $entity
     * @return ProductEntity|null
     */
    public function save(ProductEntity $entity): ?ProductEntity
    {
        $productId = $this->database->table($this->tableName)
            ->where('id = ?', $entity->getId())
            ->update([
                'name' => $entity->getName(),
                'price' => $entity->getPrice(),
                'updated_at' => $entity->getUpdatedAt(),
            ]);

        $row = $this->get($productId);

        return empty($row) ? NULL : $row;
    }

    /**
     * Smaže produkt podle zadaného ID
     *
     * @param int $id
     * @return void
     */
    public function delete(int $id): void
    {
        $this->database->table($this->tableName)
            ->where('id = ?', $id)
            ->delete();
    }

    /**
     * Transformace pole s entitami na pole polí
     *
     * @param array $list
     * @return array
     */
    public static function toArray(array $list): array
    {
        $outList = [];

        foreach ($list as $item)
        {
            $outList[] = $item->toArray();
        }

        return $outList;
    }

    /**
     * Namapuje data z databáze do entity
     *
     * @param ActiveRow $row
     * @return ProductEntity
     */
    public function mapping(ActiveRow $row): ProductEntity
    {
        return (new ProductEntity())
            ->setId($row->id)
            ->setName($row->name)
            ->setPrice($row->price)
            ->setCreatedAt($row->created_at)
            ->setUpdatedAt($row->updated_at);
    }

}
