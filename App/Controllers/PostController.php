<?php

namespace App\Controllers;

use App\Configuration;
use Exception;
use Framework\Core\BaseController;
use Framework\DB\Connection;
use Framework\Core\Model as FrameworkModel;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use Framework\Http\Responses\JsonResponse;
use Framework\Http\HttpException;

class PostController extends BaseController
{
    public function authorize(Request $request, string $action): bool
    {
        // Posts are public to view; create/update/delete require login which we'll check in methods
        return true;
    }

    public function index(Request $request): Response
    {
        try {
            $sql = "SELECT p.*, u.username AS author, c.meno AS cat_name
                    FROM posts p
                    LEFT JOIN users u ON p.user_id = u.id
                    LEFT JOIN cats c ON p.cat_id = c.id
                    ORDER BY p.created_at DESC";
            $posts = FrameworkModel::executeRawSQL($sql);
            $cats = FrameworkModel::executeRawSQL('SELECT id, meno FROM cats ORDER BY meno');
            return $this->html(['posts' => $posts, 'cats' => $cats]);
        } catch (Exception $e) {
            throw new HttpException(500, 'DB Chyba: ' . $e->getMessage());
        }
    }

    // AJAX create
    public function save(Request $request): Response
    {
        if (!$request->isPost()) {
            throw new HttpException(405);
        }

        $identity = $this->app->getSession()->get(Configuration::IDENTITY_SESSION_KEY);
        if (!$identity) {
            // return JSON error for JS
            return $this->json(['error' => 'Not logged in']);
        }

        $title = trim($request->value('title') ?? '');
        $content = trim($request->value('content') ?? '');
        $catId = (int)($request->value('cat_id') ?? 0);

        if ($title === '' || $content === '' || $catId <= 0) {
            return $this->json(['error' => 'Missing required fields']);
        }

        try {
            $conn = Connection::getInstance();
            $ins = $conn->prepare('INSERT INTO posts (title, content, user_id, cat_id, created_at) VALUES (?, ?, ?, ?, ?)');
            $ins->execute([$title, $content, $identity->getId(), $catId, date('Y-m-d H:i:s')]);
            $id = $conn->lastInsertId();

            $post = FrameworkModel::executeRawSQL('SELECT p.*, u.username AS author, c.meno AS cat_name FROM posts p LEFT JOIN users u ON p.user_id = u.id LEFT JOIN cats c ON p.cat_id = c.id WHERE p.id = ? LIMIT 1', [$id]);
            return new JsonResponse(['success' => true, 'post' => $post[0] ?? null]);
        } catch (Exception $e) {
            return new JsonResponse(['error' => 'DB error: ' . $e->getMessage()]);
        }
    }

    // Edit form (normal GET)
    public function edit(Request $request): Response
    {
        $id = (int)$request->value('id');
        if ($id <= 0) {
            throw new HttpException(400);
        }
        try {
            $postRows = FrameworkModel::executeRawSQL('SELECT * FROM posts WHERE id = ? LIMIT 1', [$id]);
            if (empty($postRows)) {
                throw new HttpException(404);
            }
            $post = $postRows[0];
            $cats = FrameworkModel::executeRawSQL('SELECT id, meno FROM cats ORDER BY meno');
            return $this->html(['post' => $post, 'cats' => $cats], 'edit');
        } catch (Exception $e) {
            throw new HttpException(500, 'DB Chyba: ' . $e->getMessage());
        }
    }

    // Update (normal POST)
    public function update(Request $request): Response
    {
        if (!$request->isPost()) {
            return $this->redirect($this->url('post.index'));
        }
        $identity = $this->app->getSession()->get(Configuration::IDENTITY_SESSION_KEY);
        if (!$identity) {
            return $this->redirect($this->url('profile.index'));
        }
        $id = (int)$request->value('id');
        if ($id <= 0) {
            return $this->redirect($this->url('post.index'));
        }
        $title = trim($request->value('title') ?? '');
        $content = trim($request->value('content') ?? '');
        $catId = (int)($request->value('cat_id') ?? 0);

        if ($title === '' || $content === '' || $catId <= 0) {
            return $this->redirect($this->url('post.edit', ['id' => $id]));
        }

        try {
            $conn = Connection::getInstance();
            // check ownership
            $owner = FrameworkModel::executeRawSQL('SELECT user_id FROM posts WHERE id = ? LIMIT 1', [$id]);
            if (empty($owner) || $owner[0]['user_id'] != $identity->getId()) {
                throw new HttpException(403, 'Not allowed');
            }
            $upd = $conn->prepare('UPDATE posts SET title = ?, content = ?, cat_id = ? WHERE id = ?');
            $upd->execute([$title, $content, $catId, $id]);
            return $this->redirect($this->url('post.index'));
        } catch (Exception $e) {
            throw new HttpException(500, 'DB Chyba: ' . $e->getMessage());
        }
    }

    // AJAX delete
    public function delete(Request $request): Response
    {
        if (!$request->isPost()) {
            throw new HttpException(405);
        }
        $identity = $this->app->getSession()->get(Configuration::IDENTITY_SESSION_KEY);
        if (!$identity) {
            return new JsonResponse(['error' => 'Not logged in']);
        }
        $id = (int)$request->value('id');
        if ($id <= 0) {
            return new JsonResponse(['error' => 'Invalid id']);
        }
        try {
            $owner = FrameworkModel::executeRawSQL('SELECT user_id FROM posts WHERE id = ? LIMIT 1', [$id]);
            if (empty($owner)) {
                return new JsonResponse(['error' => 'Not found']);
            }
            if ($owner[0]['user_id'] != $identity->getId()) {
                return new JsonResponse(['error' => 'Forbidden']);
            }
            $conn = Connection::getInstance();
            $del = $conn->prepare('DELETE FROM posts WHERE id = ?');
            $del->execute([$id]);
            return new JsonResponse(['success' => true]);
        } catch (Exception $e) {
            return new JsonResponse(['error' => 'DB error: ' . $e->getMessage()]);
        }
    }
}

