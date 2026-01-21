<?php
/**
 * Tento súbor bol upravený za pomoci generatívnej umelej inteligencie (AI).
 *
 */

namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use Framework\Core\Model as FrameworkModel;
use Framework\Http\HttpException;
use Exception;

/**
 * Trieda MapController
 * Zobrazuje interaktívnu mapu (Slovensko)
 */
class MapController extends BaseController
{
    public function authorize(Request $request, string $action): bool
    {
        return true; // verejné pre teraz
    }

    public function index(Request $request): Response
    {
        try {
            $sql = "SELECT l.id, l.cat_id, l.city, l.latitude, l.longitude, c.meno AS cat_name
                    FROM locations l
                    JOIN cats c ON l.cat_id = c.id";

            $locations = FrameworkModel::executeRawSQL($sql);

            return $this->html(['locations' => $locations]);
        } catch (Exception $e) {
            throw new HttpException(500, 'DB Chyba: ' . $e->getMessage());
        }
    }

}
