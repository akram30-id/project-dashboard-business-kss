<?php


namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Services\AccurateHelperService;
use App\Services\AccurateInvoice;
use App\Services\AccurateRevenue;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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
        $invoiceService = new AccurateInvoice();
        $revenueService = new AccurateRevenue();

        $isTokenExist = $helper->isAccessTokenExist();

        if (empty($isTokenExist)) { // jika belum pernah generate access token sama sekali
            $scope = config('accurate.scope');
            return $helper->ouath2Authorization($scope);
        }

        $accessToken = $isTokenExist['access_token'];

        $getDBSession = $helper->getDBSession($accessToken);

        if (isset($getDBSession['error'])) {
            view('error', ['message' => 'ERROR WHEN CONNECTING TO ACCURATE DB SESSION']);
            die();
        }

        $xSessionId = $getDBSession['session_id'];
        $host = $getDBSession['accurate_host'];

        $totalInvoice = $invoiceService->getTotalInvoice($host, $accessToken, $xSessionId);
        $totalRevenue = $revenueService->getTotalRevenue($host, $accessToken, $xSessionId);

        $totalAccrue = $totalInvoice - $totalRevenue;

        $menus = Menu::orderBy('order')->get();
        return view('pages.index', compact('menus', 'totalInvoice', 'totalRevenue', 'totalAccrue'));
    }

    public function show(Menu $menu)
    {
        $this->authorize("read {$menu->name}");
        return view('pages.show', compact('menu'));
    }
}
