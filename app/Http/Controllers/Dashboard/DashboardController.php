<?php


namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AccurateInvoiceWebhook;
use App\Models\Menu;
use App\Services\AccurateHelperService;
use App\Services\AccurateInvoice;
use App\Services\AccurateRevenue;
use Error;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        // dd(Auth::user());
        // $this->authorize('read Dashboard');

        // echo 'dashboard';
        // die();

        $helper = new AccurateHelperService();

        $isTokenExist = $helper->isAccessTokenExist();

        if (empty($isTokenExist)) { // jika belum pernah generate access token sama sekali
            $scope = config('accurate.scope');
            return $helper->ouath2Authorization($scope);
        }

        if (isset($isTokenExist['error'])) {
            view('error', ['message' => $isTokenExist['error']]);
            die();
        }

        $accessToken = $isTokenExist['access_token'];

        if (empty(Auth::user()->email)) {
            return redirect()->to('/login');
        }

        $menus = Menu::orderBy('order')->get();

        $baseUrl = config('accurate.base_url');

        $data = [
            'menus' => $menus,
            'url_total_invoice' => $baseUrl . '/api/dashboard_accurate/annual_invoice?access_token=' . $accessToken,
            'url_total_accrue' => $baseUrl . '/api/dashboard_accurate/annual_accrue?access_token=' . $accessToken,
        ];

        return view('pages.index', $data);
    }

    public function getAnnualInvoice(Request $request)
    {
        try {
            $accessToken = $request->get('access_token');
            if (empty($accessToken)) {
                throw new Error('Access token is empty', 401);
            }

            $helper = new AccurateHelperService();

            $getDBSession = $helper->getDBSession($accessToken);

            if (isset($getDBSession['error'])) {
                throw new Error('Failed to get db session', 401);
            }

            $getCurrentAnnualTotalInvoice = AccurateInvoiceWebhook::where('year', date('Y'))->orderBy('id', 'desc')->first();

            if (empty($getCurrentAnnualTotalInvoice)) {
                throw new Error('No data found', 404);
            }

            $data = json_decode($getCurrentAnnualTotalInvoice->data);

            return response([
                'data' => $data->current_annual_invoice
            ], 200);
        } catch (\Error $th) {
            Log::debug('ERROR WHEN GETTING TOTAL ANNUAL INVOICE', ['throw' => $th->getMessage(), 'line' => $th->getLine()]);

            return response([
                'error' => $th->getMessage()
            ], (empty($th->getCode()) || $th->getCode() == 0) ? 500 : $th->getCode());
        }
    }

    public function getAnnualAccrue(Request $request)
    {
        try {
            $accessToken = $request->get('access_token');
            if (empty($accessToken)) {
                throw new Error('Access token is empty', 401);
            }

            $helper = new AccurateHelperService();

            $getDBSession = $helper->getDBSession($accessToken);

            if (isset($getDBSession['error'])) {
                throw new Error('Failed to get db session', 401);
            }

            $getWebhookData = AccurateInvoiceWebhook::where('year', date('Y'))->first();

            if (empty($getWebhookData)) {
                throw new Error('No data found', 404);
            }

            $data = json_decode($getWebhookData->data);

            return response([
                'data' => $data->current_annual_accrue
            ], 200);
        } catch (\Error $th) {
            Log::debug('ERROR WHEN GETTING TOTAL ANNUAL INVOICE', ['throw' => $th->getMessage(), 'line' => $th->getLine()]);

            return response([
                'error' => $th->getMessage()
            ], (empty($th->getCode()) || $th->getCode() == 0) ? 500 : $th->getCode());
        }
    }

    public function getAnnualSales(Request $request)
    {
        try {
            $accessToken = $request->get('access_token');
            if (empty($accessToken)) {
                throw new Error('Access token is empty', 401);
            }

            $helper = new AccurateHelperService();

            $revenueService = new AccurateRevenue();

            $getDBSession = $helper->getDBSession($accessToken);

            if (isset($getDBSession['error'])) {
                throw new Error('Failed to get db session', 401);
            }

            $xSessionId = $getDBSession['session_id'];
            $host = $getDBSession['accurate_host'];

            $totalRevenue = $revenueService->getTotalRevenue($host, $accessToken, $xSessionId);

            return response([
                'data' => $totalRevenue
            ], 200);
        } catch (\Error $th) {
            Log::debug('ERROR WHEN GETTING TOTAL ANNUAL REVENUE', ['throw' => $th->getMessage(), 'line' => $th->getLine()]);

            return response([
                'error' => $th->getMessage()
            ], (empty($th->getCode()) || $th->getCode() == 0) ? 500 : $th->getCode());
        }
    }

    public function show(Menu $menu)
    {
        $this->authorize("read {$menu->name}");
        return view('pages.show', compact('menu'));
    }
}
