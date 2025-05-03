<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\AccurateHelperService;
use App\Services\AccurateInvoice;
use App\Services\AccurateRevenue;
use Error;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        try {
            $this->authorize('read Report');

            $year = date('Y');
            $listYear = [$year];
            for ($i = 1; $i <= 4; $i++) {
                $year = $year - 1;
                $listYear[] = $year;
            }

            $accessToken = session('accurate_token');

            $data = [
                'list_year' => $listYear,
                'url_get_list_annual' => config('accurate.base_url') . '/api/report_annual?access_token=' . $accessToken,
            ];

            return view('pages.report.index', compact('data'));
        } catch (\Error $th) {
            Log::error('FAILED TO SERVE REPORT DASHBOARD', ['error' => $th->getMessage(), 'code' => ($th->getCode() == 0) ? 500 : $th->getCode()]);
        }
    }

    function apiListAnnual(Request $request)
    {
        try {
            $requestYear = $request->get('year');

            if (null === $request->get('year')) {
                $requestYear = date('Y');
            }

            $accessToken = $request->get('access_token');

            if (null === $accessToken) {
                throw new Error('Invalid access token', 401);
            }

            $getAnnualInvoiceRevenue = $this->listAnnual($requestYear, $accessToken);

            return response(['data' => $getAnnualInvoiceRevenue]);
        } catch (\Throwable $th) {
            $response = ['error' => $th->getMessage()];
            Log::error('FAILED TO GET LIST REQUESTED YEAR ANNUAL', $response);
            return response($response, ($th->getCode() == 0) ? 500 : $th->getCode());
        }
    }

    public function listAnnual($year, $accessToken)
    {
        $helper = new AccurateHelperService();
        $invoiceService = new AccurateInvoice();
        $revenueService = new AccurateRevenue();

        $getDBSession = $helper->getDBSession($accessToken);

        if (isset($getDBSession['error'])) {
            Log::error('FAILED TO GET DB SESSION ON INVOICE LIST ANNUAL', ['access_token' => $accessToken]);
            return ['error' => 'Invalid session request', 'code' => 500];
        }

        $xSessionId = $getDBSession['session_id'];
        $host = $getDBSession['accurate_host'];

        $getCurrentAnnualTotalInvoice = $invoiceService->getTotalInvoice($host, $accessToken, $xSessionId, true, 1, null, 0, $year);

        $getCurrentAnnualTotalRevenue = $revenueService->getTotalRevenue($host, $accessToken, $xSessionId, true, 1, null, 0, $year);

        $accrue = $getCurrentAnnualTotalInvoice - $getCurrentAnnualTotalRevenue;

        $data = [
            'current_annual_invoice' => $getCurrentAnnualTotalInvoice,
            'current_annual_revenue' => $getCurrentAnnualTotalRevenue,
            'current_annual_accrue' => $accrue,
            'year' => $year
        ];

        return $data;
    }
}
