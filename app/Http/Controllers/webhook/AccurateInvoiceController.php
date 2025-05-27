<?php

namespace App\Http\Controllers\webhook;

use App\Http\Controllers\Controller;
use App\Models\AccurateInvoiceWebhook;
use App\Models\AccurateToken;
use App\Models\WebhookLog;
use App\Models\AccurateSession;
use App\Services\AccurateHelperService;
use App\Services\AccurateInvoice as ServicesAccurateInvoice;
use Error;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccurateInvoiceController extends Controller
{
    public function apiGetDataAnnual()
    {
        try {

            $getDBAccessToken = AccurateToken::orderBy('id', 'desc')->first();

            if (null === $getDBAccessToken) {
                throw new Error('No access token found');
            }

            $accessToken = $getDBAccessToken->access_token;

            $getDBSession = AccurateSession::orderBy('id', 'desc')->first();

            if (null === $getDBSession) {
                throw new Error('No session found');
            }

            $xSessionId = $getDBSession->session_id;

            $currentYear = date('Y');

            for ($i = 0; $i < 5; $i++) {
                $listYear[] = $currentYear--;
            }

            foreach ($listYear as $year) {

                $listAnnual = $this->listAnnual($year, $accessToken, $xSessionId);

                if (isset($listAnnual['error'])) {
                    throw new Error($listAnnual['error'], $listAnnual['code']);
                }

                $saveToDB = $this->saveListAnnual($listAnnual, $year, $xSessionId, $accessToken);

                if (isset($saveToDB['error'])) {
                    throw new Error($saveToDB['error'], $saveToDB['code']);
                }
            }

            echo 'success';
        } catch (\Throwable $th) {
            Log::error('FAILED TO PROCEED ANNUAL INVOICE', ['error' => $th->getMessage(), 'code' => $th->getCode(), 'trace' => $th->getTraceAsString()]);

            echo $th->getMessage() . ' ' . $th->getLine() . ' ' . $th->getTraceAsString();
        }
    }

    public function listAnnual($year, $accessToken)
    {
        $helper = new AccurateHelperService();
        $invoiceService = new ServicesAccurateInvoice(false);
        $accrueService = new ServicesAccurateInvoice(true);

        $getDBSession = $helper->getDBSession($accessToken);

        if (isset($getDBSession['error'])) {
            Log::error('FAILED TO GET DB SESSION ON INVOICE LIST ANNUAL', ['access_token' => $accessToken]);
            return ['error' => 'Invalid session request', 'code' => 500];
        }

        $xSessionId = $getDBSession['session_id'];
        $host = $getDBSession['accurate_host'];

        $getCurrentAnnualTotalInvoice = $invoiceService->getTotalSalesInvoice($host, $accessToken, $xSessionId, true, 1, null, 0, $year);

        if (isset($getCurrentAnnualTotalInvoice['error'])) {
            $errLog = [
                'webhook_id' => md5(json_encode($getCurrentAnnualTotalInvoice)),
                'request_url' => $getCurrentAnnualTotalInvoice['request_url'],
                'request_body' => json_encode($getCurrentAnnualTotalInvoice['request_body']),
                'request_header' => json_encode([
                    'X-Session-ID' => $xSessionId,
                    'Authorization' => 'Bearer ' . $accessToken
                ]),
                'response_body' => $getCurrentAnnualTotalInvoice['error'],
                'status_code' => $getCurrentAnnualTotalInvoice['code']
            ];

            $this->saveWebhookLog($errLog);

            return ['error' => $getCurrentAnnualTotalInvoice['error'], 'code' => $getCurrentAnnualTotalInvoice['code'], 'webhook_id' => time()];
        }

        $getCurrentAnnualTotalAccrue = $accrueService->getTotalSalesInvoice($host, $accessToken, $xSessionId, true, 1, null, 0, $year);

        if (isset($getCurrentAnnualTotalAccrue['error'])) {
            $errLog = [
                'webhook_id' => md5(json_encode($getCurrentAnnualTotalAccrue)),
                'request_url' => $getCurrentAnnualTotalAccrue['request_url'],
                'request_body' => json_encode($getCurrentAnnualTotalAccrue['request_body']),
                'request_header' => json_encode([
                    'X-Session-ID' => $xSessionId,
                    'Authorization' => 'Bearer ' . $accessToken
                ]),
                'response_body' => $getCurrentAnnualTotalAccrue['error'],
                'status_code' => $getCurrentAnnualTotalAccrue['code']
            ];

            $this->saveWebhookLog($errLog);

            return ['error' => $getCurrentAnnualTotalAccrue['error'], 'code' => $getCurrentAnnualTotalAccrue['code'], 'webhook_id' => time()];
        }

        $revenue = $getCurrentAnnualTotalInvoice + $getCurrentAnnualTotalAccrue;

        $data = [
            'current_annual_invoice' => $getCurrentAnnualTotalInvoice,
            'current_annual_revenue' => $revenue,
            'current_annual_accrue' => $getCurrentAnnualTotalAccrue
        ];

        return $data;
    }

    public function saveListAnnual($data, $year, $xSessionId, $accessToken)
    {
        DB::beginTransaction();

        try {
            $invoiceData = [
                'current_annual_invoice' => $data['current_annual_invoice'],
                'current_annual_revenue' => $data['current_annual_revenue'],
                'current_annual_accrue'  => $data['current_annual_accrue'],
            ];

            $webhookId = md5(json_encode($invoiceData));

            $saveToDB = AccurateInvoiceWebhook::updateOrCreate(
                ['webhook_id' => $webhookId],
                ['year' => $year, 'data' => json_encode($invoiceData)]
            );

            DB::commit();

            $this->saveWebhookLog([
                'webhook_id' => $webhookId,
                'request_url' => 'https://api.accurate.id/webhook/accurate_invoice_annual',
                'request_body' => json_encode($invoiceData),
                'request_header' => json_encode([
                    'X-Session-ID' => $xSessionId,
                    'Authorization' => 'Bearer ' . $accessToken
                ]),
                'response_body' => json_encode($saveToDB),
                'status_code' => 200
            ]);

            return $saveToDB;
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'error' => 'Failed to save to database',
                'message' => $e->getMessage(),
                'code' => 500,
            ];
        }
    }


    public function saveWebhookLog($data)
    {
        $webhookLog = new WebhookLog();
        $webhookLog->webhook_id = $data['webhook_id'];
        $webhookLog->request_url = $data['request_url'];
        $webhookLog->request_body = $data['request_body'];
        $webhookLog->request_header = $data['request_header'];
        $webhookLog->response_body = $data['response_body'];
        $webhookLog->status_code = $data['status_code'];

        $webhookLog->save();

        return $webhookLog;
    }
}
