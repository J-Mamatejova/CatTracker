<?php
/**
 * Tento súbor bol upravený za pomoci generatívnej umelej inteligencie (AI).
 *
 */

namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

/**
 * Trieda AdminController
 * Spravuje admin akcie v aplikácii.
 */
class AdminController extends BaseController
{
    /**
     * Autorizuje akcie v tomto kontroléri.
     * Kontrola, či je používateľ prihlásený.
     */
    public function authorize(Request $request, string $action): bool
    {
        return $this->user->isLoggedIn();
    }

    /**
     * Zobrazenie indexu administrácie.
     */
    public function index(Request $request): Response
    {
        return $this->html();
    }
}
