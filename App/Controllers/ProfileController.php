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
use Framework\DB\Connection;
use Framework\Core\Model as FrameworkModel;
use Framework\Http\Responses\RedirectResponse;
use Framework\Http\HttpException;

/**
 * Trieda ProfileController
 * Jednoduchý kontrolér na zobrazenie profilovej stránky.
 */
class ProfileController extends BaseController
{
    /**
     * Povoliť prístup k profilovej stránke (view zobrazuje UI podľa autentifikácie).
     */
    public function authorize(Request $request, string $action): bool
    {
        return true;
    }

    /**
     * Zobraziť profil (index) s používateľskými štatistikami
     */
    public function index(Request $request): Response
    {
        try {
            $identity = $this->app->getSession()->get(Configuration::IDENTITY_SESSION_KEY);
            $postsCount = 0;
            $catsCount = 0;

            if ($identity) {
                $uid = $identity->getId();
                $rows = FrameworkModel::executeRawSQL('SELECT COUNT(*) AS cnt FROM posts WHERE user_id = ?', [$uid]);
                $postsCount = isset($rows[0]['cnt']) ? (int)$rows[0]['cnt'] : 0;

                $rows2 = FrameworkModel::executeRawSQL('SELECT COUNT(*) AS cnt FROM cats WHERE user_id = ?', [$uid]);
                $catsCount = isset($rows2[0]['cnt']) ? (int)$rows2[0]['cnt'] : 0;
            }

            return $this->html(['posts_count' => $postsCount, 'cats_count' => $catsCount]);
        } catch (Exception $e) {
            throw new HttpException(500, 'DB error: ' . $e->getMessage());
        }
    }

    /**
     * Zmena hesla (POST)
     */
    public function changePassword(Request $request): Response
    {
        if (!$request->isPost()) {
            throw new HttpException(405);
        }

        $identity = $this->app->getSession()->get(Configuration::IDENTITY_SESSION_KEY);
        if (!$identity) {
            return $this->redirect($this->url('profile.index', ['changePwdError' => 1, 'changePwdMessage' => urlencode('Not logged in')]));
        }

        $old = $request->value('old_password') ?? '';
        $new = $request->value('new_password') ?? '';
        $confirm = $request->value('new_password_confirm') ?? '';

        if ($new === '' || strlen($new) < 6) {
            return $this->redirect($this->url('profile.index', ['changePwdError' => 1, 'changePwdMessage' => urlencode('New password must be at least 6 characters')]));
        }
        if ($new !== $confirm) {
            return $this->redirect($this->url('profile.index', ['changePwdError' => 1, 'changePwdMessage' => urlencode('Passwords do not match')]));
        }

        try {
            $conn = Connection::getInstance();
            // detect password column
            $colsRaw = FrameworkModel::executeRawSQL('DESCRIBE users');
            $cols = array_column($colsRaw, 'Field');
            if (in_array('password_hash', $cols, true)) {
                $pwCol = 'password_hash';
            } elseif (in_array('password', $cols, true)) {
                $pwCol = 'password';
            } else {
                $conn->prepare("ALTER TABLE users ADD COLUMN `password_hash` VARCHAR(255) NULL")->execute();
                $pwCol = 'password_hash';
            }
            // whitelist
            $allowedPwCols = ['password_hash', 'password'];
            if (!in_array($pwCol, $allowedPwCols, true)) $pwCol = 'password_hash';

            // fetch current hash by user id
            $rows = FrameworkModel::executeRawSQL('SELECT id, `' . $pwCol . '` AS pwcol FROM users WHERE id = ? LIMIT 1', [$identity->getId()]);
            $user = $rows[0] ?? null;
            if (!$user || !password_verify($old, $user['pwcol'])) {
                return $this->redirect($this->url('profile.index', ['changePwdError' => 1, 'changePwdMessage' => urlencode('Old password is incorrect')]));
            }

            $hash = password_hash($new, PASSWORD_DEFAULT);
            $upd = $conn->prepare('UPDATE users SET `' . $pwCol . '` = ? WHERE id = ?');
            $upd->execute([$hash, $identity->getId()]);

            return $this->redirect($this->url('profile.index', ['changePwdSuccess' => 1]));
        } catch (Exception $e) {
            return $this->redirect($this->url('profile.index', ['changePwdError' => 1, 'changePwdMessage' => urlencode($e->getMessage())]));
        }
    }
}
