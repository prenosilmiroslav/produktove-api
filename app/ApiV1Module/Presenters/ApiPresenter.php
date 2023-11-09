<?php

declare(strict_types=1);

namespace App\ApiV1Module\Presenters;

use App\ApiV1Module\Entity\ProductEntity;
use App\ApiV1Module\Model\AuthModel;
use App\ApiV1Module\Model\ProductModel;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;
use Nette\Http\IResponse;
use Nette\Http\Request;
use Nette\Utils\DateTime;
use Nette\Utils\Paginator;
use Nette\Utils\Strings;

class ApiPresenter extends Presenter
{

    /** @var array $allowAction */
    private array $allowAction = ['insert', 'update', 'delete', 'get'];

    /** @var ProductModel $productModel @inject */
    public ProductModel $productModel;

    /** @var AuthModel $authModel @inject */
    public AuthModel $authModel;


    public function startup()
    {
        parent::startup();

        $token = $this->getHttpRequest()->getHeader('Authorization');
        if (!empty($token) && preg_match('/Bearer (.+)/', $token, $matches))
        {
            // Kontrola secret tokenu
            if (empty($matches[1]) || !$this->authModel->isTokenValid($matches[1]))
            {
                $this->getHttpResponse()->setCode(IResponse::S401_Unauthorized);
                $this->sendJson([
                    'success' => FALSE,
                    'errorMessage' => 'Neplatná autorizace',
                ]);
            }
        }
        else
        {
            $this->getHttpResponse()->setCode(IResponse::S401_Unauthorized);
            $this->sendJson([
                'success' => FALSE,
                'errorMessage' => 'Neplatná autorizace',
            ]);
        }

        if (empty($this->action) || $this->action == 'default' || !in_array($this->action, $this->allowAction))
        {
            $this->sendJson([
                'success' => FALSE,
                'errorMessage' => 'Neplatná operace',
            ]);
        }
    }

    /**
     * Vložení nového produktu
     *
     * @return void
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function actionInsert(): void
    {
        if (!$this->getHttpRequest()->isMethod(Request::POST))
        {
            $this->sendJson([
                'success' => FALSE,
                'errorMessage' => 'Nepovolená metoda',
            ]);
        }

        $data = file_get_contents('php://input');
        $params = json_decode($data, true);

        if (empty($params['name']) || empty($params['price']))
        {
            $this->sendJson([
                'success' => FALSE,
                'errorMessage' => 'Neplatné vstupní parametry',
            ]);
        }

        $productEntity = (new ProductEntity())
            ->setName(Strings::trim((string) $params['name']))
            ->setPrice((float) $params['price'])
            ->setCreatedAt(new DateTime());

        $product = $this->productModel->insert($productEntity);

        $this->sendJson([
            'success' => TRUE,
            'data' => empty($product) ? [] : [$product->toArray()],
        ]);
    }

    public function actionUpdate(int $id): void
    {
        if (!$this->getHttpRequest()->isMethod(Request::Put))
        {
            $this->sendJson([
                'success' => FALSE,
                'errorMessage' => 'Nepovolená metoda',
            ]);
        }

        $dataString = "";
        $putdata = fopen("php://input", "r");
        while ($data = fread($putdata, 1024))
        {
            $dataString .= $data;
        }
        fclose($putdata);

        $params = (array) json_decode($dataString);

        if (empty($params['name']) || empty($params['price']))
        {
            $this->sendJson([
                'success' => FALSE,
                'errorMessage' => 'Neplatné vstupní parametry',
            ]);
        }

        $product = $this->productModel->get((int) $id);

        $product->setName(Strings::trim($params['name']))
            ->setPrice((float) $params['price'])
            ->setUpdatedAt(new DateTime());

        $product = $this->productModel->save($product);

        $this->sendJson([
            'success' => TRUE,
            'data' => empty($product) ? [] : [$product->toArray()],
        ]);
    }

    /**
     * Smaže konkrétní produkt podle zadaného ID
     *
     * @param int $id
     * @return void
     * @throws \Nette\Application\AbortException
     */
    public function actionDelete(int $id): void
    {
        if (!$this->getHttpRequest()->isMethod(Request::Delete))
        {
            $this->sendJson([
                'success' => FALSE,
                'errorMessage' => 'Nepovolená metoda',
            ]);
        }

        if (empty($id))
        {
            $this->sendJson([
                'success' => FALSE,
                'errorMessage' => 'Neplatný vstupní parametr',
            ]);
        }

        $this->productModel->delete((int) $id);
        $this->sendJson(['success' => TRUE]);
    }

    /**
     * Vrací seznam produktů podle filtrace s možností řazení. Při zadání ID vrací konkrétní produkt
     *
     * @param int|NULL $id
     * @return void
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function actionGet(int $id = NULL): void
    {
        if (!$this->getHttpRequest()->isMethod(Request::Get))
        {
            $this->sendJson([
                'success' => FALSE,
                'errorMessage' => 'Nepovolená metoda',
            ]);
        }

        if (empty($id))
        {
            // Seznam produktů
            $params = $this->request->parameters;
            $page = 1;
            $limit = $this->productModel->getLimit();

            if (!empty($params['page']))
            {
                $page = (int) $params['page'];
            }
            if (!empty($params['limit']))
            {
                $limit = (int) $params['limit'];
            }

            try {
                $totalCount = $this->productModel->getAllCount($params);

                $paginator = new Paginator();
                $paginator->setPage($page);
                $paginator->setItemsPerPage($limit);
                $paginator->setItemCount($totalCount);

                $list = $this->productModel->getAll($params, $paginator->getLength(), $paginator->getOffset());

                $output = [
                    'success' => TRUE,
                    'limit' => (int) $limit,
                    'page' => (int) $page,
                    'totalPage' => (int) $paginator->getLastPage(),
                    'totalItem' => $totalCount,
                    'data' => ProductModel::toArray($list),
                ];
            }
            catch (\Exception $exception)
            {
                $this->sendJson([
                    'success' => FALSE,
                    'errorMessage' => $exception->getMessage(),
                ]);
            }
        }
        else
        {
            // Jeden konkrétní produkt
            try
            {
                $product = $this->productModel->get($id);

                $output = [
                    'success' => TRUE,
                    'data' => empty($product) ? [] : [$product->toArray()],
                ];
            }
            catch (\Exception $exception)
            {
                $this->sendJson([
                    'success' => FALSE,
                    'errorMessage' => $exception->getMessage(),
                ]);
            }
        }

        $this->sendJson($output);
    }

}
