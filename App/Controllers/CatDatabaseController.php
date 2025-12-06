<?php

namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

/**
 * Class CatDatabaseController
 * Controller for the Cat Database page.
 *
 * @package App\Controllers
 */
class CatDatabaseController extends BaseController
{
    /**
     * Allow all users to access this page for now.
     */
    public function authorize(Request $request, string $action): bool
    {
        return true;
    }

    /**
     * Shows the cat database index page.
     */
    public function index(Request $request): Response
    {
        return $this->html();
    }
}

