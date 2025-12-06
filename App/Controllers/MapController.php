<?php

namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

/**
 * Class MapController
 * Shows an interactive map page (Slovakia)
 */
class MapController extends BaseController
{
    public function authorize(Request $request, string $action): bool
    {
        return true; // public for now
    }

    public function index(Request $request): Response
    {
        return $this->html();
    }
}

