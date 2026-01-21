<?php
/**
 * Tento súbor bol upravený za pomoci generatívnej umelej inteligencie (AI).
 *
 */

namespace App\Controllers;

use App\Configuration;
use Exception;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

/**
 * Trieda AuthController
 * Spracúva autentifikačné akcie (login, logout, signup redirecty).
 */
class AuthController extends BaseController
{
    /**
     * Presmeruje na prihlasovaciu stránku (modál na profile)
     */
    public function index(Request $request): Response
    {
        return $this->redirect($this->url('profile.index'));
    }

    /**
     * Prihlásenie - delegované na flow v Profile (modál)
     */
    public function login(Request $request): Response
    {
        return $this->redirect($this->url('profile.index'));
    }

    /**
     * Registrácia - delegovaná na flow v Profile (modál)
     */
    public function signup(Request $request): Response
    {
        return $this->redirect($this->url('profile.index'));
    }

    /**
     * Odhlásenie: ukončí reláciu používateľa
     */
    public function logout(Request $request): Response
    {
        $this->app->getAuthenticator()?->logout();
        return $this->redirect($this->url('home.index'));
    }
}
