<?php

namespace App\Http\Controllers;

use App\Models\CompanyInfo;
use Illuminate\Http\Request;

class CompanyInfoController extends Controller
{
    /**
     * Fetch the single company info record (or null if none set yet).
     */
    public function fetch()
    {
        $info = CompanyInfo::first();

        return response()->json([
            'success' => true,
            'data' => $info, // null if not set up yet
        ]);
    }

    /**
     * Create or update the single company info record.
     */
    public function save(Request $request)
    {
        try {
            $data = json_decode($request->get('data'), true);

            if (!$data || !isset($data['name']) || trim($data['name']) === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Company name is required.',
                ], 422);
            }

            $info = CompanyInfo::first();

            if ($info) {
                $info->update($data);
            } else {
                $info = CompanyInfo::create($data);
            }

            return response()->json([
                'success' => true,
                'data' => $info,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving company info: ' . $e->getMessage(),
            ], 500);
        }
    }
}