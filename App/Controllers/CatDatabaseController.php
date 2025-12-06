<?php

namespace App\Controllers;

use App\Models\Cats;
use Exception;
use Framework\Core\BaseController;
use Framework\Http\HttpException;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use Framework\DB\Connection;

/**
 * Class CatDatabaseController
 * Controller for the Cat Database page.
 *
 * @package App\Controllers
 */
class CatDatabaseController extends BaseController
{
    /**
     * Authorization method
     *
     * @param Request $request
     * @param string $action
     * @return bool
     * @throws Exception
     */
    public function authorize(Request $request, string $action): bool
    {
        // For now allow all actions; you can customize per-action checks later
        return true;
    }

    /**
     * Shows the cat database index page.
     *
     * @param Request $request
     * @return Response
     * @throws HttpException
     */
    public function index(Request $request): Response
    {
        try {
            return $this->html([
                'cats' => Cats::getAll()
            ]);
        } catch (Exception $e) {
            throw new HttpException(500, 'DB Chyba: ' . $e->getMessage());
        }
    }
}
