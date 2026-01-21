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

class UserController extends BaseController
{
    /**
     * Všetky akcie sú verejné; view bude zobrazovať UI pre prihlásenie/registráciu podľa potreby.
     */
    public function authorize(Request $request, string $action): bool
    {
        return true;
    }

    /**
     * Požadovaná predvolená akcia - presmerovanie na profil
     */
    public function index(Request $request): Response
    {
        return $this->redirect($this->url('profile.index'));
    }

    /**
     * Zobraziť registračný formulár - používame modály, tak presmerujeme na profil
     */
    public function registerGET(Request $request): Response
    {
        return $this->redirect($this->url('profile.index'));
    }

    /**
     * Spracovanie registrácie (POST)
     */
    public function registerPOST(Request $request): Response
    {
        $username = trim($request->value('username') ?? '');
        $email = trim($request->value('email') ?? '');
        $password = $request->value('password') ?? '';
        $passwordConfirm = $request->value('password_confirm') ?? '';

        $errors = [];

        // 1) Validácia vstupu (žiadne volania DB tu)
        if ($username === '') {
            $errors[] = 'Používateľské meno je povinné.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Je potrebný platný email.';
        }
        if (strlen($password) < 6) {
            $errors[] = 'Heslo musí mať aspoň 6 znakov.';
        }
        if ($password !== $passwordConfirm) {
            $errors[] = 'Heslá sa nezhodujú.';
        }

        // Ak validácia zlyhala, ani sa nedotýkame databázy
        if (count($errors) > 0) {
            if ($request->hasValue('submit')) {
                // modal flow -> presmerovať späť na profil s chybovými parametrami
                return $this->redirect($this->url('profile.index', ['signupError' => 1, 'signupMessage' => implode(' | ', $errors)]));
            }
            return $this->html(['errors' => $errors, 'old' => ['username' => $username, 'email' => $email]], 'register');
        }

        // Odtiaľto môžeme volať DB, tak pripravíme pripojenie a použijeme transakciu pre bezpečnosť
        $conn = null;
        try {
            $conn = Connection::getInstance();

            // Zabezpečiť, aby tabuľka používateľov existovala (bezpečné DDL). Toto nezapíše údaje používateľa.
            $createSql = "CREATE TABLE IF NOT EXISTS `users` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `username` VARCHAR(100) NOT NULL UNIQUE,
                `email` VARCHAR(255) NOT NULL UNIQUE,
                `password_hash` VARCHAR(255) NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            $conn->prepare($createSql)->execute();

            // Načítať aktuálne stĺpce, aby sme sa rozhodli o úpravách schémy (iba na čítanie)
            $colsRaw = FrameworkModel::executeRawSQL('DESCRIBE users');
            $cols = array_column($colsRaw, 'Field');

            // Ak chýba stĺpec email (zriedkavé), pridajte ho PRED kontrolou jedinečnosti, pretože ho budeme dotazovať
            if (!in_array('email', $cols, true)) {
                $conn->prepare("ALTER TABLE users ADD COLUMN `email` VARCHAR(255) NULL")->execute();
                $cols[] = 'email';
            }

            // 2) Skontrolovať existujúceho používateľa (jedinečnosť) PRED akýmkoľvek VLOŽENÍM
            $existingRows = FrameworkModel::executeRawSQL('SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1', [$email, $username]);
            $existing = $existingRows[0] ?? null;
            if ($existing) {
                $errors[] = 'Používateľ s týmto emailom alebo používateľským menom už existuje.';
                if ($request->hasValue('submit')) {
                    return $this->redirect($this->url('profile.index', ['signupError' => 1, 'signupMessage' => implode(' | ', $errors)]));
                }
                return $this->html(['errors' => $errors, 'old' => ['username' => $username, 'email' => $email]], 'register');
            }

            // Určte stĺpec hesla, ktorý sa má použiť (po kontrolách)
            if (in_array('password_hash', $cols, true)) {
                $pwCol = 'password_hash';
            } elseif (in_array('password', $cols, true)) {
                $pwCol = 'password';
            } else {
                // Pridajte stĺpec password_hash ako zálohu teraz, keď ideme zapisovať
                $conn->prepare("ALTER TABLE users ADD COLUMN `password_hash` VARCHAR(255) NULL")->execute();
                $pwCol = 'password_hash';
            }

            // Povoliť iba názov stĺpca hesla, aby sa predišlo SQL injekciám cez názov stĺpca
            $allowedPwCols = ['password_hash', 'password'];
            if (!in_array($pwCol, $allowedPwCols, true)) {
                $pwCol = 'password_hash';
            }

            // Všetky kontroly a kontroly jedinečnosti prešli; teraz zahashuj heslo
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Začať transakciu pre VLOŽENIE, aby sa predišlo čiastočným zápisom
            if (!$conn->inTransaction()) {
                $conn->beginTransaction();
            }

            // Vložiť pomocou zisteného stĺpca hesla
            $insSql = "INSERT INTO users (username, email, `" . $pwCol . "`) VALUES (?, ?, ?)";
            $ins = $conn->prepare($insSql);
            $ins->execute([$username, $email, $hash]);

            // načítať ID vloženého používateľa
            $id = $conn->lastInsertId();

            // Potvrdiť transakciu
            if ($conn->inTransaction()) {
                $conn->commit();
            }

            // prihlásiť používateľa nastavením identity rámca v relácii pomocou DbUser
            $this->app->getSession()->set(Configuration::IDENTITY_SESSION_KEY, new \Framework\Auth\DbUser((int)$id, $username, $email));

            if ($request->hasValue('submit')) {
                return $this->redirect($this->url('profile.index'));
            }
            return $this->redirect($this->url('profile.index'));
        } catch (Exception $e) {
            // Vrátiť späť, ak sme začali transakciu
            try {
                if ($conn && $conn->inTransaction()) {
                    $conn->rollBack();
                }
            } catch (Exception $rollbackEx) {
                // ignorovať výnimku rollbacku, vrátime pôvodnú chybu
            }

            $errors[] = 'Registrácia zlyhala: ' . $e->getMessage();
            if ($request->hasValue('submit')) {
                return $this->redirect($this->url('profile.index', ['signupError' => 1, 'signupMessage' => $e->getMessage()]));
            }
            return $this->html(['errors' => $errors, 'old' => ['username' => $username, 'email' => $email]], 'register');
        }
    }

    /**
     * Wrapper pre register (rozhoduje GET vs POST)
     */
    public function register(Request $request): Response
    {
        if ($request->isPost()) {
            return $this->registerPOST($request);
        }
        return $this->registerGET($request);
    }

    /**
     * Zobraziť prihlásenie (modál) - presmerovanie na profil
     */
    public function loginGET(Request $request): Response
    {
        return $this->redirect($this->url('profile.index'));
    }

    /**
     * Spracovanie prihlásenia (POST)
     */
    public function loginPOST(Request $request): Response
    {
        $email = trim($request->value('email') ?? '');
        $password = $request->value('password') ?? '';

        $errors = [];

        if ($email === '' || $password === '') {
            $errors[] = 'Email a heslo sú povinné.';
            if ($request->hasValue('submit')) {
                return $this->redirect($this->url('profile.index', ['loginError' => 1, 'loginMessage' => implode(' | ', $errors)]));
            }
            return $this->html(['errors' => $errors, 'old' => ['email' => $email]], 'login');
        }

        try {
            $conn = Connection::getInstance();

            // zistiť stĺpec hesla pomocou pomocníka rámca
            $colsRaw = FrameworkModel::executeRawSQL('DESCRIBE users');
            $cols = array_column($colsRaw, 'Field');
            if (in_array('password_hash', $cols)) {
                $pwCol = 'password_hash';
            } elseif (in_array('password', $cols)) {
                $pwCol = 'password';
            } else {
                $conn->prepare("ALTER TABLE users ADD COLUMN `password_hash` VARCHAR(255) NULL")->execute();
                $pwCol = 'password_hash';
            }

            // Povoliť iba názov stĺpca hesla, aby sa predišlo SQL injekciám cez názov stĺpca
            $allowedPwCols = ['password_hash', 'password'];
            if (!in_array($pwCol, $allowedPwCols, true)) {
                $pwCol = 'password_hash';
            }

            $rows = FrameworkModel::executeRawSQL('SELECT id, username, `' . $pwCol . '` AS pwcol, email FROM users WHERE email = ? LIMIT 1', [$email]);
            $user = $rows[0] ?? null;
            if (!$user || !password_verify($password, $user['pwcol'])) {
                $errors[] = 'Neplatné prihlasovacie údaje.';
                if ($request->hasValue('submit')) {
                    return $this->redirect($this->url('profile.index', ['loginError' => 1, 'loginMessage' => 'Neplatné prihlasovacie údaje.']));
                }
                return $this->html(['errors' => $errors, 'old' => ['email' => $email]], 'login');
            }

            // nastaviť identitu relácie pomocou DbUser
            $this->app->getSession()->set(Configuration::IDENTITY_SESSION_KEY, new \Framework\Auth\DbUser((int)$user['id'], $user['username'], $user['email'] ?? null));

            if ($request->hasValue('submit')) {
                return $this->redirect($this->url('profile.index'));
            }

            return $this->redirect($this->url('profile.index'));
        } catch (Exception $e) {
            $errors[] = 'Prihlásenie zlyhalo: ' . $e->getMessage();
            if ($request->hasValue('submit')) {
                return $this->redirect($this->url('profile.index', ['loginError' => 1, 'loginMessage' => $e->getMessage()]));
            }
            return $this->html(['errors' => $errors, 'old' => ['email' => $email]], 'login');
        }
    }

    /**
     * Wrapper pre login
     */
    public function login(Request $request): Response
    {
        if ($request->isPost()) {
            return $this->loginPOST($request);
        }
        return $this->loginGET($request);
    }

    /**
     * Odhlásenie: odstránenie identity zo session a zničenie session
     */
    public function logout(Request $request): Response
    {
        // Odstrániť identitu zo session a zničiť reláciu na odhlásenie používateľa
        $this->app->getSession()->remove(Configuration::IDENTITY_SESSION_KEY);
        $this->app->getSession()->destroy();
        return $this->redirect($this->url('home.index'));
    }

    /**
     * Zobrazenie profilu
     */
    public function profile(Request $request): Response
    {
        try {
            $conn = Connection::getInstance();
            $identity = $this->user ?? null; // Inštancia AppUser z BaseController::setApp
            $userData = null;
            $postsCount = 0;

            if ($identity && $identity->isLoggedIn()) {
                $username = $identity->getName();

                $rows = FrameworkModel::executeRawSQL('SELECT id, username, email, created_at FROM users WHERE username = ? LIMIT 1', [$username]);
                $userData = $rows[0] ?? null;

                if ($userData) {
                    $cntRows = FrameworkModel::executeRawSQL('SELECT COUNT(*) AS cnt FROM posts WHERE user_id = ?', [$userData['id']]);
                    $cnt = $cntRows[0] ?? null;
                    $postsCount = $cnt ? (int)$cnt['cnt'] : 0;
                }
            }

            return $this->html(['user' => $userData, 'posts_count' => $postsCount]);
        } catch (Exception $e) {
            throw new \Framework\Http\HttpException(500, 'Chyba DB: ' . $e->getMessage());
        }
    }
}
