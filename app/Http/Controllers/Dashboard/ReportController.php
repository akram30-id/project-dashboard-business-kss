<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AccurateInvoiceWebhook;
use App\Services\AccurateHelperService;
use App\Services\AccurateInvoice;
use App\Services\AccurateRevenue;
use Error;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        try {
            // $this->authorize('read Report');

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
                'url_get_list_monthly' => config('accurate.base_url') . '/api/report_annual/monthly?access_token=' . $accessToken,
                'url_get_invoice_detail_monthly' => config('accurate.base_url') . '/api/invoice_monthly_detail?access_token=' . $accessToken,
                'url_get_invoice_detail_annualy' => config('accurate.base_url') . '/api/invoice_monthly_detail/annual?access_token=' . $accessToken,
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

            $getWebhookData = AccurateInvoiceWebhook::where('year', $requestYear)->orderBy('id', 'desc')->first();

            if (empty($getWebhookData)) {
                throw new Error('No data found', 404);
            }

            $data = json_decode($getWebhookData->data);

            $getAnnualInvoiceRevenue = [
                'current_annual_invoice' => $data->current_annual_invoice,
                'current_annual_revenue' => $data->current_annual_revenue,
                'current_annual_accrue' => $data->current_annual_accrue,
                'year' => $requestYear
            ];

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
        $invoiceService = new AccurateInvoice(false);
        $accrueService = new AccurateInvoice(true);

        $getDBSession = $helper->getDBSession($accessToken);

        if (isset($getDBSession['error'])) {
            Log::error('FAILED TO GET DB SESSION ON INVOICE LIST ANNUAL', ['access_token' => $accessToken]);
            return ['error' => 'Invalid session request', 'code' => 500];
        }

        $xSessionId = $getDBSession['session_id'];
        $host = $getDBSession['accurate_host'];

        $getCurrentAnnualTotalInvoice = $invoiceService->getTotalInvoice($host, $accessToken, $xSessionId, true, 1, null, 0, $year);

        $getCurrentAnnualTotalAccrue = $accrueService->getTotalInvoice($host, $accessToken, $xSessionId, true, 1, null, 0, $year);

        $revenue = $getCurrentAnnualTotalInvoice + $getCurrentAnnualTotalAccrue;

        $data = [
            'current_annual_invoice' => $getCurrentAnnualTotalInvoice,
            'current_annual_revenue' => $revenue,
            'current_annual_accrue' => $getCurrentAnnualTotalAccrue,
            'year' => $year
        ];

        return $data;
    }

    public function apiListMonthly(Request $request)
    {
        try {
            $month = $request->get('month');
            $year = $request->get('year');
            $accessToken = $request->get('access_token');

            if (null === $year) {
                throw new Error('E_401003', 401);
            }

            if (null === $accessToken) {
                throw new Error('E_401004', 401);
            }

            $helper = new AccurateHelperService();
            $invoiceService = new AccurateInvoice(false);
            $accrueService = new AccurateInvoice(true);

            $getDBSession = $helper->getDBSession($accessToken);

            if (isset($getDBSession['error']) || empty($getDBSession)) {
                throw new Error('E_401005', 401);
            }

            $xSessionId = $getDBSession['session_id'];
            $host = $getDBSession['accurate_host'];

            $cacheKey = "invoice_revenue_{$year}_" . ($month ?? 'all') . "_" . md5($accessToken);

            $data = Cache::remember($cacheKey, 300, function () use ($month, $year, $host, $accessToken, $xSessionId, $invoiceService, $accrueService) {
                $data = [];
                $data[$year]['invoice'] = [];
                $data[$year]['revenue'] = [];
                $data[$year]['accrue'] = [];

                if ($month === null) {
                    for ($i = 1; $i <= 12; $i++) {
                        $totalInvoice = $invoiceService->getTotalInvoiceAnnual($year, $i, $host, $accessToken, $xSessionId);
                        $totalAccrue = $accrueService->getTotalInvoiceAnnual($year, $i, $host, $accessToken, $xSessionId);
                        $revenue = $totalInvoice + $totalAccrue;

                        $data[$year]['invoice'][] = $totalInvoice;
                        $data[$year]['revenue'][] = $revenue;
                        $data[$year]['accrue'][] = $totalAccrue;
                    }
                }

                return $data;
            });

            return response([
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            Log::error('FAILED TO GET API REPORT ANNUALY', ['error' => $th->getMessage()]);
            return response([
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile()
            ], $th->getCode() == 0 ? 500 : $th->getCode());
        }
    }

    public function apiDetailMonthly(Request $request)
    {
        try {
            $month = $request->get('month');
            $year = $request->get('year');
            $accessToken = $request->get('access_token');
            $page = $request->get('page');
            $limit = $request->get('limit');

            if (null === $year) {
                throw new Error('E_401003', 401);
            }

            if (null === $accessToken) {
                throw new Error('E_401004', 401);
            }

            if (null === $page) {
                $page = 1;
            }

            if (null === $limit) {
                $limit = 10;
            }

            $helper = new AccurateHelperService();
            $invoiceService = new AccurateInvoice();

            $getDBSession = $helper->getDBSession($accessToken);

            if (isset($getDBSession['error']) || empty($getDBSession)) {
                throw new Error('E_401005', 401);
            }

            $xSessionId = $getDBSession['session_id'];
            $host = $getDBSession['accurate_host'];

            // $data = $invoiceService->getDetailInvoice($year, $month, $host, $accessToken, $xSessionId, $page, $limit);

            // return $data;

            $cacheKey = "invoice_detail_{$year}_" . ($month ?? 'all') . "_page{$page}_limit{$limit}" . md5($accessToken);

            $data = Cache::remember($cacheKey, 300, function () use ($month, $year, $host, $accessToken, $xSessionId, $invoiceService, $page, $limit) {
                $data = $invoiceService->getDetailInvoice($year, $month, $host, $accessToken, $xSessionId, $page, $limit);

                return $data;
            });

            return response([
                'page' => $page,
                'limit' => $limit,
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            Log::error('FAILED TO GET DETAIL API INVOICE', ['error' => $th->getMessage()]);
            return response([
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile()
            ], $th->getCode() == 0 ? 500 : $th->getCode());
        }
    }

    public function apiDetailAnnualy(Request $request)
    {
        try {
            $year = $request->get('year');
            $accessToken = $request->get('access_token');
            $page = $request->get('page');
            $limit = $request->get('limit');

            if (null === $year) {
                throw new Error('E_401003', 401);
            }

            if (null === $accessToken) {
                throw new Error('E_401004', 401);
            }

            if (null === $page) {
                $page = 1;
            }

            if (null === $limit) {
                $limit = 10;
            }

            $helper = new AccurateHelperService();
            $invoiceService = new AccurateInvoice();

            $getDBSession = $helper->getDBSession($accessToken);

            if (isset($getDBSession['error']) || empty($getDBSession)) {
                throw new Error('E_401005', 401);
            }

            $xSessionId = $getDBSession['session_id'];
            $host = $getDBSession['accurate_host'];

            // $data = $invoiceService->getDetailInvoiceAnnual($year, $host, $accessToken, $xSessionId, $page, $limit);

            $cacheKey = "invoice_detail_{$year}_" . "_page{$page}_limit{$limit}" . md5($accessToken);

            $data = Cache::remember($cacheKey, 300, function () use ($year, $host, $accessToken, $xSessionId, $invoiceService, $page, $limit) {
                $data = $invoiceService->getDetailInvoiceAnnual($year, $host, $accessToken, $xSessionId, $page, $limit);

                return $data;
            });

            return response([
                'page' => $page,
                'limit' => $limit,
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            Log::error('FAILED TO GET DETAIL API INVOICE ANNUALY', ['error' => $th->getMessage()]);
            return response([
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile()
            ], $th->getCode() == 0 ? 500 : $th->getCode());
        }
    }
}
