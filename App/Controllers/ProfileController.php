<?php

namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

/**
 * Class ProfileController
 * Simple controller to display the profile page.
 */
class ProfileController extends BaseController
{
    /**
     * Allow access to profile page (view handles auth-specific UI).
     */
    public function authorize(Request $request, string $action): bool
    {
        return true;
    }

    /**
     * Show profile index view.
     */
    public function index(Request $request): Response
    {
        return $this->html();
    }
}

