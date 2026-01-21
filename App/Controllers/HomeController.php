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
 * Trieda HomeController
 * Obsluhuje akcie súvisiace s úvodnou stránkou a verejnými akciami.
 */
class HomeController extends BaseController
{
    /**
     * Autorizuje akcie kontroléra podľa mena akcie.
     * V tejto implementácii sú všetky akcie povolené.
     */
    public function authorize(Request $request, string $action): bool
    {
        return true;
    }

    /**
     * Zobrazenie hlavnej domovskej stránky.
     */
    public function index(Request $request): Response
    {
        return $this->html();
    }

    /**
     * Zobrazenie kontaktného formulára/stránky.
     */
    public function contact(Request $request): Response
    {
        return $this->html();
    }
}
