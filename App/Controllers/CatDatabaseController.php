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
     * Save new cat (called by AJAX/form POST)
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

        // Create model and save
        try {
            $cat = new Cats();
            $cat->setMeno($name);
            $cat->setText($text);
            $cat->setStatus($status);
            $cat->setKastrovana((int)$kastrovana);
            if ($fileName !== '') {
                $cat->setFotka($fileName);
            }
            $cat->save();

            // Store location referencing this cat (latitude/longitude/city required)
            $catId = $cat->getId();
            $latitude = $request->value('latitude');
            $longitude = $request->value('longitude');
            $city = $request->value('city') ?? '';

            if ($latitude != '' && $longitude != '') {
                try {
                    $locStmt = Connection::getInstance()->prepare('INSERT INTO locations (cat_id, city, latitude, longitude) VALUES (?, ?, ?, ?)');
                    $locStmt->execute([$catId, $city, $latitude, $longitude]);
                } catch (Exception $lex) {
                    // If location insert fails, delete cat and uploaded file to keep consistency
                    try {
                        $cat->delete();
                    } catch (Exception $inner) {
                        // ignore
                    }
                    if ($fileName !== '' && is_file(Configuration::UPLOAD_DIR . $fileName)) {
                        @unlink(Configuration::UPLOAD_DIR . $fileName);
                    }
                    throw new HttpException(500, 'Failed to save location: ' . $lex->getMessage());
                }
            } else {
                // Address required; this should not happen because frontend geocodes, but validate server-side
                // Cleanup and error
                try { $cat->delete(); } catch (Exception $inner) {}
                if ($fileName !== '' && is_file(Configuration::UPLOAD_DIR . $fileName)) { @unlink(Configuration::UPLOAD_DIR . $fileName); }
                throw new HttpException(400, 'Latitude and longitude required');
            }

            // Return success (200) so fetch() sees resp.ok
            return new EmptyResponse();
        } catch (Exception $e) {
            // cleanup file if it was stored
            if ($fileName !== '' && is_file(Configuration::UPLOAD_DIR . $fileName)) {
                @unlink(Configuration::UPLOAD_DIR . $fileName);
            }
            throw new HttpException(500, 'DB Chyba: ' . $e->getMessage());
        }
    }
}
