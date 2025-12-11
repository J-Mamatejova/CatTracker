<?php

namespace App\Controllers;

use App\Configuration;
use App\Models\Cats;
use Exception;
use Framework\Core\BaseController;
use Framework\Http\HttpException;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use Framework\Http\Responses\RedirectResponse;
use Framework\Http\Responses\EmptyResponse;
use Framework\DB\Connection;

/**
 * Class CatDatabaseController
 * Controller for the Cat Database page.
 *
 * @package App\Controllers
 */
class CatDatabaseController extends BaseController
{
    /**
     * Authorization method
     *
     * @param Request $request
     * @param string $action
     * @return bool
     * @throws Exception
     */
    public function authorize(Request $request, string $action): bool
    {
        // For now allow all actions; you can customize per-action checks later
        return true;
    }

    /**
     * Shows the cat database index page.
     *
     * @param Request $request
     * @return Response
     * @throws HttpException
     */
    public function index(Request $request): Response
    {
        try {
            return $this->html([
                'cats' => Cats::getAll(),
                'catCount' => Cats::getCount(),
            ]);
        } catch (Exception $e) {
            throw new HttpException(500, 'DB Chyba: ' . $e->getMessage());
        }
    }

    /**
     * Save new cat (called by AJAX/form POST) - also handles updates when id is provided
     */
    public function save(Request $request): Response
    {
        // Only allow POST
        if (!$request->isPost()) {
            throw new HttpException(405);
        }

        // Require logged-in user
        $identity = $this->app->getSession()->get(Configuration::IDENTITY_SESSION_KEY);
        if (!$identity) {
            throw new HttpException(403, 'Authentication required');
        }

        // Ensure cats table has user_id column (best-effort)
        try {
            $cols = \Framework\Core\Model::executeRawSQL('DESCRIBE cats');
            $colNames = array_column($cols, 'Field');
            if (!in_array('user_id', $colNames)) {
                Connection::getInstance()->prepare('ALTER TABLE cats ADD COLUMN user_id INT UNSIGNED NULL')->execute();
            }
        } catch (Exception $e) {
            // ignore - continue
        }

        $id = (int)$request->value('id');
        $name = trim($request->value('meno') ?? '');
        $text = trim($request->value('text') ?? '');
        $status = trim($request->value('status') ?? '');
        $kastrovana = $request->value('kastrovana') ? 1 : 0;

        $errors = [];
        if ($name === '') {
            $errors[] = 'Name is required';
        }
        if ($text === '') {
            $errors[] = 'Text is required';
        }
        if (count($errors) > 0) {
            throw new HttpException(400, implode('; ', $errors));
        }

        // Handle file upload if provided
        $uploaded = $request->file('fotka');
        $fileName = '';
        $oldFileName = '';
        if ($uploaded !== null && $uploaded->getName() != '') {
            // Basic upload health check
            if (!$uploaded->isOk()) {
                throw new HttpException(400, 'Upload failed: ' . ($uploaded->getErrorMessage() ?? 'unknown error'));
            }

            // Validate file size (max 5 MB)
            $maxSize = 5 * 1024 * 1024; // 5MB
            if ($uploaded->getSize() > $maxSize) {
                throw new HttpException(400, 'Uploaded file is too large (max 5 MB)');
            }

            // Validate MIME type
            $allowed = ['image/jpeg', 'image/png'];
            $type = $uploaded->getType();
            if (!in_array($type, $allowed, true)) {
                throw new HttpException(400, 'Invalid file type. Only JPG and PNG images are allowed.');
            }

            if (!is_dir(Configuration::UPLOAD_DIR)) {
                if (!@mkdir(Configuration::UPLOAD_DIR, 0777, true) && !is_dir(Configuration::UPLOAD_DIR)) {
                    throw new HttpException(500, 'Failed to create upload dir');
                }
            }

            // Safe unique filename
            $orig = basename($uploaded->getName());
            $ext = pathinfo($orig, PATHINFO_EXTENSION);
            $unique = time() . '-' . bin2hex(random_bytes(6)) . ($ext ? '.' . $ext : '');
            $target = Configuration::UPLOAD_DIR . $unique;

            if (!$uploaded->store($target)) {
                throw new HttpException(500, 'Failed to store uploaded file');
            }
            $fileName = $unique;
        }

        // Begin transaction
        $conn = Connection::getInstance();
        $inTransaction = false;
        try {
            $conn->beginTransaction();
            $inTransaction = true;

            if ($id > 0) {
                // Editing existing cat - check ownership
                $cat = Cats::getOne($id);
                if ($cat === null) {
                    throw new HttpException(404);
                }
                $ownerId = $cat->getUserId();
                if ($ownerId !== null && $ownerId !== $identity->getId()) {
                    throw new HttpException(403, 'You are not allowed to edit this cat');
                }
                // remember old file to delete only after successful commit
                $oldFileName = $cat->getFotka();
            } else {
                $cat = new Cats();
                $cat->setUserId($identity->getId());
            }

            $cat->setMeno($name);
            $cat->setText($text);
            $cat->setStatus($status);
            $cat->setKastrovana((int)$kastrovana);
            if ($fileName !== '') {
                $cat->setFotka($fileName);
            }

            $cat->save();
            $catId = $cat->getId();

            // Location handling
            $latitude = $request->value('latitude');
            $longitude = $request->value('longitude');
            $city = $request->value('city') ?? '';

            $latProvided = ($latitude !== null && $latitude !== '');
            $lonProvided = ($longitude !== null && $longitude !== '');

            // Validate lat/lon if provided
            if ($latProvided || $lonProvided) {
                if (!is_numeric($latitude) || !is_numeric($longitude)) {
                    throw new HttpException(400, 'Latitude and longitude must be numeric');
                }
                $latitude = (float)$latitude;
                $longitude = (float)$longitude;
                if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
                    throw new HttpException(400, 'Latitude/longitude out of range');
                }

                // check if location exists for this cat
                $existing = \Framework\Core\Model::executeRawSQL('SELECT id FROM locations WHERE cat_id = ? LIMIT 1', [$catId]);
                if (!empty($existing)) {
                    // update existing location
                    $locId = $existing[0]['id'];
                    $upd = $conn->prepare('UPDATE locations SET city = ?, latitude = ?, longitude = ? WHERE id = ?');
                    $upd->execute([$city, $latitude, $longitude, $locId]);
                } else {
                    $ins = $conn->prepare('INSERT INTO locations (cat_id, city, latitude, longitude) VALUES (?, ?, ?, ?)');
                    $ins->execute([$catId, $city, $latitude, $longitude]);
                }
            } else {
                // If creating, require lat/lon. If editing, keep previous location (do nothing).
                if ($id === 0) {
                    throw new HttpException(400, 'Latitude and longitude required when creating a new cat');
                }
            }

            // commit transaction
            $conn->commit();
            $inTransaction = false;

            // After successful commit, remove old uploaded file if a new one was provided
            if ($fileName !== '' && $oldFileName !== '' && is_file(Configuration::UPLOAD_DIR . $oldFileName)) {
                @unlink(Configuration::UPLOAD_DIR . $oldFileName);
            }

            // Return success (200) so fetch() sees resp.ok
            return new EmptyResponse();
        } catch (Exception $e) {
            // rollback if needed
            try {
                if ($inTransaction) {
                    $conn->rollBack();
                }
            } catch (Exception $rb) {
                // ignore rollback errors
            }

            // cleanup new uploaded file if present
            if ($fileName !== '' && is_file(Configuration::UPLOAD_DIR . $fileName)) {
                @unlink(Configuration::UPLOAD_DIR . $fileName);
            }

            // If it was a create and cat exists (but rollback may have undone insert), attempt to delete cat record
            try {
                if (isset($cat) && $id === 0 && method_exists($cat, 'getId') && $cat->getId()) {
                    $cat->delete();
                }
            } catch (Exception $inner) {
                // ignore
            }

            throw new HttpException(500, 'DB Chyba: ' . $e->getMessage());
        }
    }

    /**
     * Show edit form for a cat (GET)
     */
    public function edit(Request $request): Response
    {
        $id = (int)$request->value('id');
        if ($id <= 0) {
            throw new HttpException(400);
        }
        $cat = Cats::getOne($id);
        if ($cat === null) {
            throw new HttpException(404);
        }

        $identity = $this->app->getSession()->get(Configuration::IDENTITY_SESSION_KEY);
        if (!$identity) {
            throw new HttpException(403);
        }
        if ($cat->getUserId() !== null && $cat->getUserId() !== $identity->getId()) {
            throw new HttpException(403, 'You are not allowed to edit this cat');
        }

        // load the cat's location if present
        $locRows = [];
        try {
            $locRows = \Framework\Core\Model::executeRawSQL('SELECT city, latitude, longitude FROM locations WHERE cat_id = ? LIMIT 1', [$id]);
        } catch (Exception $e) {
            // ignore
        }

        return $this->html(['cat' => $cat, 'location' => $locRows[0] ?? null], 'edit');
    }

    /**
     * Delete cat (owner only)
     */
    public function delete(Request $request): Response
    {
        $id = (int)$request->value('id');
        if ($id <= 0) {
            throw new HttpException(400);
        }
        $cat = Cats::getOne($id);
        if ($cat === null) {
            throw new HttpException(404);
        }

        $identity = $this->app->getSession()->get(Configuration::IDENTITY_SESSION_KEY);
        if (!$identity) {
            throw new HttpException(403);
        }
        if ($cat->getUserId() !== null && $cat->getUserId() !== $identity->getId()) {
            throw new HttpException(403, 'You are not allowed to delete this cat');
        }

        try {
            // remove uploaded file if exists
            $fn = $cat->getFotka();
            if ($fn && is_file(Configuration::UPLOAD_DIR . $fn)) {
                @unlink(Configuration::UPLOAD_DIR . $fn);
            }
            $cat->delete();
            return $this->redirect($this->url('catdatabase.index'));
        } catch (Exception $e) {
            throw new HttpException(500, 'DB Chyba: ' . $e->getMessage());
        }
    }
}
