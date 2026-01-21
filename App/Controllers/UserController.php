<?php

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
     * All actions are public; view logic will show login/register UI accordingly.
     */
    public function authorize(Request $request, string $action): bool
    {
        return true;
    }

    /**
     * Required default action - redirect to profile
     */
    public function index(Request $request): Response
    {
        return $this->redirect($this->url('profile.index'));
    }

    /**
     * Show registration form - redirect to profile page (we use modals instead of standalone page)
     */
    public function registerGET(Request $request): Response
    {
        return $this->redirect($this->url('profile.index'));
    }

    /**
     * Handle registration
     */
    public function registerPOST(Request $request): Response
    {
        $username = trim($request->value('username') ?? '');
        $email = trim($request->value('email') ?? '');
        $password = $request->value('password') ?? '';
        $passwordConfirm = $request->value('password_confirm') ?? '';

        $errors = [];

        if ($username === '') {
            $errors[] = 'Username is required.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required.';
        }
        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }
        if ($password !== $passwordConfirm) {
            $errors[] = 'Passwords do not match.';
        }

        if (count($errors) > 0) {
            if ($request->hasValue('submit')) {
                // modal flow -> redirect back to profile with error params
                return $this->redirect($this->url('profile.index', ['signupError' => 1, 'signupMessage' => implode(' | ', $errors)]));
            }
            return $this->html(['errors' => $errors, 'old' => ['username' => $username, 'email' => $email]], 'register');
        }

        try {
            $conn = Connection::getInstance();
            // ensure users table
            $createSql = "CREATE TABLE IF NOT EXISTS `users` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `username` VARCHAR(100) NOT NULL UNIQUE,
                `email` VARCHAR(255) NOT NULL UNIQUE,
                `password_hash` VARCHAR(255) NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            $conn->prepare($createSql)->execute();

            // ensure required columns exist; fetch current columns using framework helper
            $colsRaw = FrameworkModel::executeRawSQL('DESCRIBE users');
            $cols = array_column($colsRaw, 'Field');

            // if email column missing, add it
            if (!in_array('email', $cols)) {
                // Add email column if it's missing
                $conn->prepare("ALTER TABLE users ADD COLUMN `email` VARCHAR(255) NULL")->execute();
                $cols[] = 'email';
            }
            // determine password column to use
            if (in_array('password_hash', $cols)) {
                $pwCol = 'password_hash';
            } elseif (in_array('password', $cols)) {
                $pwCol = 'password';
            } else {
                // add password_hash column as fallback
                $conn->prepare("ALTER TABLE users ADD COLUMN `password_hash` VARCHAR(255) NULL")->execute();
                $pwCol = 'password_hash';
            }

            // Whitelist the password column name to avoid SQL injection via column name
            $allowedPwCols = ['password_hash', 'password'];
            if (!in_array($pwCol, $allowedPwCols, true)) {
                $pwCol = 'password_hash';
            }

            // check existing
            $existingRows = FrameworkModel::executeRawSQL('SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1', [$email, $username]);
            $existing = $existingRows[0] ?? null;
            if ($existing) {
                $errors[] = 'User with this email or username already exists.';
                if ($request->hasValue('submit')) {
                    return $this->redirect($this->url('profile.index', ['signupError' => 1, 'signupMessage' => implode(' | ', $errors)]));
                }
                return $this->html(['errors' => $errors, 'old' => ['username' => $username, 'email' => $email]], 'register');
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            // Insert using the detected password column
            $insSql = "INSERT INTO users (username, email, `" . $pwCol . "`) VALUES (?, ?, ?)";
            $ins = $conn->prepare($insSql);
            $ins->execute([$username, $email, $hash]);

            // fetch inserted user id
            $id = $conn->lastInsertId();

            // login the user by setting framework identity in session using DbUser
            $this->app->getSession()->set(Configuration::IDENTITY_SESSION_KEY, new \Framework\Auth\DbUser((int)$id, $username, $email));

            if ($request->hasValue('submit')) {
                return $this->redirect($this->url('profile.index'));
            }
            return $this->redirect($this->url('profile.index'));
        } catch (Exception $e) {
            $errors[] = 'Registration failed: ' . $e->getMessage();
            if ($request->hasValue('submit')) {
                return $this->redirect($this->url('profile.index', ['signupError' => 1, 'signupMessage' => $e->getMessage()]));
            }
            return $this->html(['errors' => $errors, 'old' => ['username' => $username, 'email' => $email]], 'register');
        }
    }

    /**
     * Action wrapper for register (routes to GET/POST handler)
     */
    public function register(Request $request): Response
    {
        if ($request->isPost()) {
            return $this->registerPOST($request);
        }
        return $this->registerGET($request);
    }

    /**
     * Show login form - redirect to profile page (we use modals instead of standalone page)
     */
    public function loginGET(Request $request): Response
    {
        return $this->redirect($this->url('profile.index'));
    }

    /**
     * Handle login
     */
    public function loginPOST(Request $request): Response
    {
        $email = trim($request->value('email') ?? '');
        $password = $request->value('password') ?? '';

        $errors = [];

        if ($email === '' || $password === '') {
            $errors[] = 'Email and password are required.';
            if ($request->hasValue('submit')) {
                return $this->redirect($this->url('profile.index', ['loginError' => 1, 'loginMessage' => implode(' | ', $errors)]));
            }
            return $this->html(['errors' => $errors, 'old' => ['email' => $email]], 'login');
        }

        try {
            $conn = Connection::getInstance();

            // detect password column with framework helper
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

            // Whitelist the password column name to avoid SQL injection via column name
            $allowedPwCols = ['password_hash', 'password'];
            if (!in_array($pwCol, $allowedPwCols, true)) {
                $pwCol = 'password_hash';
            }

            $rows = FrameworkModel::executeRawSQL('SELECT id, username, `' . $pwCol . '` AS pwcol, email FROM users WHERE email = ? LIMIT 1', [$email]);
            $user = $rows[0] ?? null;
            if (!$user || !password_verify($password, $user['pwcol'])) {
                $errors[] = 'Invalid credentials.';
                if ($request->hasValue('submit')) {
                    return $this->redirect($this->url('profile.index', ['loginError' => 1, 'loginMessage' => 'Invalid credentials.']));
                }
                return $this->html(['errors' => $errors, 'old' => ['email' => $email]], 'login');
            }

            // set session identity using DbUser
            $this->app->getSession()->set(Configuration::IDENTITY_SESSION_KEY, new \Framework\Auth\DbUser((int)$user['id'], $user['username'], $user['email'] ?? null));

            if ($request->hasValue('submit')) {
                return $this->redirect($this->url('profile.index'));
            }

            return $this->redirect($this->url('profile.index'));
        } catch (Exception $e) {
            $errors[] = 'Login failed: ' . $e->getMessage();
            if ($request->hasValue('submit')) {
                return $this->redirect($this->url('profile.index', ['loginError' => 1, 'loginMessage' => $e->getMessage()]));
            }
            return $this->html(['errors' => $errors, 'old' => ['email' => $email]], 'login');
        }
    }

    /**
     * Action wrapper for login (routes to GET/POST handler)
     */
    public function login(Request $request): Response
    {
        if ($request->isPost()) {
            return $this->loginPOST($request);
        }
        return $this->loginGET($request);
    }

    /**
     * Logout
     */
    public function logout(Request $request): Response
    {
        // Remove identity from session and destroy session to log out user
        $this->app->getSession()->remove(Configuration::IDENTITY_SESSION_KEY);
        $this->app->getSession()->destroy();
        return $this->redirect($this->url('home.index'));
    }

    /**
     * Profile page
     */
    public function profile(Request $request): Response
    {
        try {
            $conn = Connection::getInstance();
            $identity = $this->user ?? null; // AppUser instance from BaseController::setApp
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
            throw new \Framework\Http\HttpException(500, 'DB error: ' . $e->getMessage());
        }
    }
}
