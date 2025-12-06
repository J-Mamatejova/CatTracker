<?php

namespace App\Controllers;

use App\Configuration;
use Exception;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use Framework\Http\Responses\ViewResponse;

/**
 * Class AuthController
 *
 * This controller handles authentication actions such as login, logout, and redirection to the login page. It manages
 * user sessions and interactions with the authentication system.
 *
 * @package App\Controllers
 */
class AuthController extends BaseController
{
    /**
     * Redirects to the login page.
     *
     * This action serves as the default landing point for the authentication section of the application, directing
     * users to the login URL specified in the configuration.
     *
     * @return Response The response object for the redirection to the login page.
     */
    public function index(Request $request): Response
    {
        return $this->redirect(Configuration::LOGIN_URL);
    }

    /**
     * Authenticates a user and processes the login request.
     *
     * This action handles user login attempts. If the login form is submitted, it attempts to authenticate the user
     * with the provided credentials. Upon successful login, the user is redirected to the admin dashboard.
     * If authentication fails, an error message is displayed on the login page.
     *
     * @return Response The response object which can either redirect on success or render the login view with
     *                  an error message on failure.
     * @throws Exception If the parameter for the URL generator is invalid throws an exception.
     */
    public function login(Request $request): Response
    {
        $logged = null;
        if ($request->hasValue('submit')) {
            $logged = $this->app->getAuthenticator()->login($request->value('username'), $request->value('password'));
            if ($logged) {
                return $this->redirect($this->url("admin.index"));
            }
        }

        $message = $logged === false ? 'Bad username or password' : null;
        // If request came from modal, redirect back to profile with error params so modal can show it
        if ($request->hasValue('submit')) {
            $params = $message ? ['loginError' => 1, 'loginMessage' => $message] : [];
            return $this->redirect($this->url('profile.index', $params));
        }

        return $this->html(compact("message"));
    }

    /**
     * Handles user signup (registration).
     * - validates input
     * - creates `users` table if needed
     * - inserts new user with password hash
     * - logs user in by storing identity in session
     */
    public function signup(Request $request): Response
    {
        // Only handle POST from the modal form
        if (!$request->hasValue('submit')) {
            return $this->redirect($this->url('profile.index'));
        }

        $username = trim($request->value('username') ?? '');
        $password = $request->value('password') ?? '';
        $passwordConfirm = $request->value('password_confirm') ?? '';

        if ($username === '' || $password === '') {
            $msg = 'Username and password are required';
            return $this->redirect($this->url('profile.index', ['signupError' => 1, 'signupMessage' => $msg]));
        }
        if ($password !== $passwordConfirm) {
            $msg = 'Passwords do not match';
            return $this->redirect($this->url('profile.index', ['signupError' => 1, 'signupMessage' => $msg]));
        }

        // Ensure users table exists and insert new user
        try {
            $conn = \Framework\DB\Connection::getInstance();

            $createSql = "CREATE TABLE IF NOT EXISTS `users` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `username` VARCHAR(100) NOT NULL UNIQUE,
                `password_hash` VARCHAR(255) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            $conn->prepare($createSql)->execute();

            // Check existing username
            $stmt = $conn->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            $existing = $stmt->fetch();
            if ($existing) {
                $msg = 'Username already taken';
                return $this->redirect($this->url('profile.index', ['signupError' => 1, 'signupMessage' => $msg]));
            }

            // Insert user
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $conn->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
            $ins->execute([$username, $hash]);

            // Log the user in by setting session identity directly (use DummyUser for simplicity)
            $this->app->getSession()->set(\App\Configuration::IDENTITY_SESSION_KEY, new \Framework\Auth\DummyUser($username));

            // Redirect to profile (user logged in)
            return $this->redirect($this->url('profile.index'));
        } catch (\Throwable $e) {
            $msg = 'Signup failed: ' . $e->getMessage();
            return $this->redirect($this->url('profile.index', ['signupError' => 1, 'signupMessage' => $msg]));
        }
    }

    /**
     * Logs out the current user.
     *
     * This action terminates the user's session and redirects them to a view. It effectively clears any authentication
     * tokens or session data associated with the user.
     *
     * @return ViewResponse The response object that renders the logout view.
     */
    public function logout(Request $request): Response
    {
        $this->app->getAuthenticator()->logout();
        return $this->html();
    }
}
