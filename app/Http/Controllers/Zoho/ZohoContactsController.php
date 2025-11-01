<?php

namespace App\Http\Controllers\Zoho;

use App\Http\Controllers\Controller;
use ZohoCrmSDK\Api\ZohoCrmApi;
use Throwable;

class ZohoContactsController extends Controller
{
    /**
     * Get list of contacts from Zoho CRM (cleaned response)
     */
    public function index()
    {
        try {
            $resp = ZohoCrmApi::getInstance()
                ->setModule('Contacts')
                ->records()
                ->getRecords()
                ->perPage(10)
                ->page(1)
                ->sortOrder('asc')
                ->sortBy('Modified_Time')
                ->request();

            // Simplify and clean data
            $clean = collect($resp)->map(function ($item) {
                return [
                    'id' => $item['id'] ?? null,
                    'first_name' => $item['First_Name'] ?? null,
                    'last_name' => $item['Last_Name'] ?? null,
                    'email' => $item['Email'] ?? null,
                    'phone' => $item['Phone'] ?? null,
                    'mobile' => $item['Mobile'] ?? null,
                    'city' => $item['Mailing_City'] ?? null,
                    'country' => $item['Mailing_Country'] ?? null,
                    'account' => $item['Account_Name']['name'] ?? null,
                    'title' => $item['Title'] ?? null,
                    'lead_source' => $item['Lead_Source'] ?? null,
                    'modified_time' => $item['Modified_Time'] ?? null,
                ];
            });

            return response()->json([
                'success' => true,
                'count' => $clean->count(),
                'data' => $clean,
            ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
