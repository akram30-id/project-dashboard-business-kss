<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class AccurateInvoice
{
    public $isAccrue;
    /**
     * Create a new class instance.
     */
    public function __construct($isAccrue = FALSE)
    {
        $this->isAccrue = $isAccrue;
    }

    function getTotalInvoice(string $host, string $accessToken, string $dbSession, bool $isAnnual = true, int $page = 1, $totalPage = null, int $totalInvoice = 0, $year = null) //: int
    {

        if ($year == null) {
            $year = date('Y');
        }

        $periode = [];

        if ($isAnnual == true) {
            $periode['start_month'] = 1;
            $periode['end_month'] = 12;
        } else {
            $periode['start_month'] = date('n');
            $periode['end_month'] = date('n');
        }

        $endpoint = '/accurate/api/sales-invoice/list.do';

        // Hit API hanya sekali di awal untuk ambil total halaman
        if ($totalPage === null) {
            $getPageCount = Http::withHeaders([
                'X-Session-ID' => $dbSession,
                'Authorization' => 'Bearer ' . $accessToken
            ])->get($host . $endpoint, [
                'filter.lastPaymentDate.op' => 'BETWEEN',
                'filter.lastPaymentDate.val[0]' => Carbon::createFromDate($year, $periode['start_month'])->startOfMonth()->format('d/m/Y'),
                'filter.lastPaymentDate.val[1]' => Carbon::createFromDate($year, $periode['end_month'])->endOfMonth()->format('d/m/Y'),
                // 'filter.approvalStatus' => 'APPROVED',
                'filter.outstanding' => $this->isAccrue ? 'TRUE' : 'FALSE',
                'sp.pageSize' => 100,
            ]);

            if ($getPageCount->successful()) {
                $resulPageCount = $getPageCount->json();
                $totalPage = $resulPageCount['sp']['pageCount'];
            } else {
                return $totalInvoice; // jika gagal ambil pageCount, return total 0
            }
        }

        // Ambil data dari halaman saat ini
        $hitAPI = Http::withHeaders([
            'X-Session-ID' => $dbSession,
            'Authorization' => 'Bearer ' . $accessToken
        ])->get($host . $endpoint, [
            'filter.lastPaymentDate.op' => 'BETWEEN',
            'filter.lastPaymentDate.val[0]' => Carbon::createFromDate($year, $periode['start_month'])->startOfMonth()->format('d/m/Y'),
            'filter.lastPaymentDate.val[1]' => Carbon::createFromDate($year, $periode['end_month'])->endOfMonth()->format('d/m/Y'),
            // 'filter.approvalStatus' => 'APPROVED',
            'filter.outstanding' => $this->isAccrue ? 'TRUE' : 'FALSE',
            'sp.pageSize' => 100,
            'sp.page' => $page,
            'fields' => 'totalAmount'
        ]);

        if ($hitAPI->successful()) {
            $resultHitAPI = $hitAPI->json();
            foreach ($resultHitAPI['d'] as $valInvoice) {
                $totalInvoice += $valInvoice['totalAmount'];
            }
        }

        // Jika masih ada halaman berikutnya, panggil lagi fungsi ini
        if ($page < $totalPage) {
            return $this->getTotalInvoice($host, $accessToken, $dbSession, $isAnnual, $page + 1, $totalPage, $totalInvoice, $year);
        }

        return $totalInvoice;
    }

    function getTotalInvoiceAnnual($year, int $month, string $host, string $accessToken, string $dbSession, int $page = 1, int $totalPage = null, int $totalInvoice = 0)
    {
        $endpoint = '/accurate/api/sales-invoice/list.do';

        // Hit API hanya sekali di awal untuk ambil total halaman
        if ($totalPage === null) {
            $getPageCount = Http::withHeaders([
                'X-Session-ID' => $dbSession,
                'Authorization' => 'Bearer ' . $accessToken
            ])->get($host . $endpoint, [
                'filter.lastPaymentDate.op' => 'BETWEEN',
                'filter.lastPaymentDate.val[0]' => Carbon::createFromDate($year, $month)->startOfMonth()->format('d/m/Y'),
                'filter.lastPaymentDate.val[1]' => Carbon::createFromDate($year, $month)->endOfMonth()->format('d/m/Y'),
                'filter.approvalStatus' => 'APPROVED',
                'filter.outstanding' => $this->isAccrue ? 'TRUE' : 'FALSE',
                'sp.pageSize' => 100
            ]);

            if ($getPageCount->successful()) {
                $resulPageCount = $getPageCount->json();
                $totalPage = $resulPageCount['sp']['pageCount'];
            } else {
                return $totalInvoice; // jika gagal ambil pageCount, return total 0
            }
        }

        // Ambil data dari halaman saat ini
        $hitAPI = Http::withHeaders([
            'X-Session-ID' => $dbSession,
            'Authorization' => 'Bearer ' . $accessToken
        ])->get($host . $endpoint, [
            'filter.lastPaymentDate.op' => 'BETWEEN',
            'filter.lastPaymentDate.val[0]' => Carbon::createFromDate($year, $month)->startOfMonth()->format('d/m/Y'),
            'filter.lastPaymentDate.val[1]' => Carbon::createFromDate($year, $month)->endOfMonth()->format('d/m/Y'),
            'filter.approvalStatus' => 'APPROVED',
            'filter.outstanding' => $this->isAccrue ? 'TRUE' : 'FALSE',
            'sp.pageSize' => 100,
            'sp.page' => $page,
            'fields' => 'totalAmount'
        ]);

        if ($hitAPI->successful()) {
            $resultHitAPI = $hitAPI->json();
            foreach ($resultHitAPI['d'] as $valInvoice) {
                $totalInvoice += $valInvoice['totalAmount'];
            }
        }

        // Jika masih ada halaman berikutnya, panggil lagi fungsi ini
        if ($page < $totalPage) {
            return $this->getTotalInvoiceAnnual($year, $month, $host, $accessToken, $dbSession, $page + 1, $totalPage, $totalInvoice);
        }

        return $totalInvoice;
    }

    function getDetailInvoice($year, int $month, string $host, string $accessToken, string $dbSession, int $page = 1, $limit = 10)
    {
        $endpoint = '/accurate/api/sales-invoice/list.do';


        // get list id invoice
        $hitAPI = Http::withHeaders([
            'X-Session-ID' => $dbSession,
            'Authorization' => 'Bearer ' . $accessToken
        ])->get($host . $endpoint, [
            'filter.lastPaymentDate.op' => 'BETWEEN',
            'filter.lastPaymentDate.val[0]' => Carbon::createFromDate($year, $month)->startOfMonth()->format('d/m/Y'),
            'filter.lastPaymentDate.val[1]' => Carbon::createFromDate($year, $month)->endOfMonth()->format('d/m/Y'),
            'filter.approvalStatus' => 'APPROVED',
            'sp.pageSize' => $limit,
            'sp.page' => $page,
            'fields' => 'id'
        ]);

        $invoiceId = [];

        if ($hitAPI->successful()) {
            $resultHitAPI = $hitAPI->json();
            foreach ($resultHitAPI['d'] as $valInvoice) {
                $invoiceId[] = $valInvoice['id'];
            }
        }

        $invoiceDetailEndpoint = '/accurate/api/sales-invoice/detail.do';
        $dataDetail = [];
        if (!empty($invoiceId)) {
            foreach ($invoiceId as $id) {
                $fullUrl = $host . $invoiceDetailEndpoint . '?id=' . $id;

                // get detail invoice
                $getDetailAPI = Http::withHeaders([
                    'X-Session-ID' => $dbSession,
                    'Authorization' => 'Bearer ' . $accessToken
                ])->get($fullUrl);

                if ($getDetailAPI->successful()) {
                    $resultDetail = $getDetailAPI->json();

                    $coNo = isset($resultDetail['d']['detailItem'][0]['salesOrder']['number'])
                        ? $resultDetail['d']['detailItem'][0]['salesOrder']['number']
                        : null;

                    $doNo = isset($resultDetail['d']['detailItem'][0]['deliveryOrder']['number'])
                        ? $resultDetail['d']['detailItem'][0]['deliveryOrder']['number']
                        : null;

                    $dataDetail[] = [
                        'work_title' => $resultDetail['d']['description'],
                        'customer_name' => $resultDetail['d']['customer']['name'],
                        'co_no' => $coNo,
                        'co_date' => $resultDetail['d']['transDate'],
                        'do_no' => $doNo,
                        'do_date' => $resultDetail['d']['shipDate'],
                        'amount' => $resultDetail['d']['totalAmount'],
                        'status' => $resultDetail['d']['statusName']
                    ];
                }
            }
        }

        return $dataDetail;
    }
}
