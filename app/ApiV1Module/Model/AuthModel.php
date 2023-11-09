<?php

declare(strict_types=1);

namespace App\ApiV1Module\Model;

use Nette\SmartObject;

class AuthModel
{

    use SmartObject;

    /** @var array $config */
    private array $config;


    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $name
     * @return string|int|array|null
     */
    public function get(string $name): string|int|array|null
    {
        return isset($this->config[$name]) ? $this->config[$name] : NULL;
    }

    /**
     * Vrátí token z parametrů
     *
     * @return string
     */
    public function getToken(): string
    {
        return $this->get('secretToken');
    }

    /**
     * Kontrola validního tokenu
     *
     * @param string $token
     * @return bool
     */
    public function isTokenValid(string $token): bool
    {
        return $this->getToken() === $token;
    }

}
