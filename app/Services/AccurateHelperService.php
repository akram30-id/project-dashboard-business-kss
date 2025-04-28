<?php

namespace App\Services;

use App\Models\AccurateSession;
use App\Models\AccurateToken;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use function PHPUnit\Framework\isArray;

class AccurateHelperService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    function ouath2Authorization(string $scope) //: RedirectResponse
    {
        $urlAuth = config('accurate.auth_url');
        $clientId = config('accurate.client_id');
        $responseType = config('accurate.response_type');
        $redirectUri = config('accurate.callback_uri');

        return redirect()->away($urlAuth . '?client_id=' . $clientId . '&response_type=' . $responseType . '&redirect_uri=' . $redirectUri . '&scope=' . $scope);
    }

    function getAccessToken(string $authorizationCode, ?string $refreshToken)
    {
        $clientId = config('accurate.client_id');
        $clientSecret = config('accurate.client_secret');

        $sign = 'Basic ' . base64_encode($clientId . ':' . $clientSecret);

        $urlToken = config('accurate.token_url');

        if (empty($refreshToken)) { // jika bukan get refresh token
            $data = [
                'code' => $authorizationCode,
                'grant_type' => 'authorization_code',
                'redirect_uri' => config('accurate.callback_uri')
            ];

            $accessToken = Http::asForm()->withHeaders([
                'Authorization' => $sign
            ])->post($urlToken, $data);

            Log::debug('GET ACCESS TOKEN : ', ['url' => $urlToken, 'data' => $data]);
        } else { // jika get refresh token
            $accessToken = $this->refreshToken($urlToken, $refreshToken, $sign);
        }

        if ($accessToken->successful()) {
            $response = $accessToken->json();

            $saveToken = $this->saveToken($response);

            if (isset($saveToken['error'])) {
                Log::debug('[FAIL] GET ACCESS TOKEN : ', ['error' => $saveToken['error']]);
                return false;
            }

            session(['accurate_token' => $saveToken['access_token']]);

            $getDBSession = $this->getDBSession($saveToken['access_token']);

            if (isset($getDBSession['error'])) {
                Log::debug('[FAIL] GET DB SESSION : ', ['url' => $urlToken, 'data' => $data]);
                return ['error' => $getDBSession['error']];
            }

            return $saveToken;
        } else {
            Log::debug('[FAIL] GET ACCESS TOKEN : ', ['error' => $accessToken->body()]);
            return ['error' => $accessToken->body()];
        }
    }

    function refreshToken(string $refreshToken)
    {
        $clientId = config('accurate.client_id');
        $clientSecret = config('accurate.client_secret');

        $sign = 'Basic ' . base64_encode($clientId . ':' . $clientSecret);

        $urlToken = config('accurate.token_url');

        $data = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken
        ];

        $hitRefreshAPI = Http::asForm()->withHeaders([
            'Authorization' => $sign
        ])->post($urlToken, $data);

        Log::debug('GET REFRESH TOKEN : ', ['url' => $urlToken, 'data' => $data]);

        return $hitRefreshAPI;
    }

    function saveToken(array $responseToken)
    {
        $userSession = session('email');

        session(['accurate_user' => $userSession]);

        $accurateToken = AccurateToken::where('access_token', $responseToken['access_token'])
            ->orderBy('expired_at', 'DESC')
            ->first();

        if (empty($accurateToken)) { // kalo usernya baru generate token

            $crateToken = $this->createToken($responseToken);
            if ($crateToken == false) {
                return ['error' => 'error when create new token'];
            }

            return $responseToken;
        }

        // kalo access token nya ada dan belum expired
        if ($accurateToken->expired_at > time()) {
            unset($accurateToken->id);
            return $accurateToken;
        } else { // kalo access token nya ada tapi expired
            $crateToken = $this->createToken($responseToken);
            if ($crateToken == false) {
                return ['error' => 'error when create new token'];
            }

            return $responseToken;
        }
    }

    function createToken($responseToken): bool
    {
        DB::beginTransaction();

        $userSession = session('email');

        $expired = Carbon::createFromTimestamp(time())->addMinutes(1)->timestamp;
        $accessToken = new AccurateToken();
        $accessToken->access_token = $responseToken['access_token'];
        $accessToken->refresh_token = $responseToken['refresh_token'];
        $accessToken->scopes = $responseToken['scope'];
        $accessToken->expired_at = $expired;
        $accessToken->user_request = $userSession;

        $saveToken = $accessToken->save();

        if ($saveToken == false) {
            DB::rollBack();

            return false;
        }

        DB::commit();

        return true;
    }

    function isAccessTokenExist(): array
    {
        $getAccessToken = AccurateToken::orderBy('expired_at', 'DESC')->first();

        if (empty($getAccessToken)) {
            return [];
        }

        if (time() >= $getAccessToken->expired_at) { // jika tokennya expired
            return [];
        }

        if (is_array($getAccessToken)) {
            return $getAccessToken;
        }

        return $getAccessToken->toArray();
    }

    function apiAccurateDBSession(string $accessToken)
    {
        $timestamp = (string) round(microtime(true) * 1000); // set timestamp dalam milidetik
        $secretKey = "sFXoSexM5HNkH1W1n4ULGT7xNDjgp1Uor690Ax1k6tedx3MBloGf6rqL5o4lOLQK";

        // HMAC-SHA256 dan encode ke Base64
        $hash = base64_encode(hash_hmac('sha256', $timestamp, $secretKey, true));

        $companyId = config('accurate.company_id');
        $host = config('accurate.token_url');

        $getDBSession = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'X-Api-Timestamp' => $timestamp,
            'X-Api-Signature' => $hash,
            'Accept' => 'application/json'
        ])->get('https://account.accurate.id/api/open-db.do?id=' . $companyId);

        if ($getDBSession->successful()) {
            return $getDBSession->json();
        } else {
            return ['error' => $getDBSession->body()];
        }
    }


    /**
     * CEK ACCURATE SESSION ADA ATAU TIDAK DI DB.
     *
     * @return array
     */
    function getDBSession(string $accessToken): array
    {
        $user = session('email');

        $getSessionFromDB = AccurateSession::where('access_token', $accessToken)
            ->orderBy('id', 'DESC')
            ->first();

        // cek apakah session nya masih ada di db
        if (empty($getSessionFromDB)) { // jika sessionnya ga ada di db
            $hitAPI = $this->apiAccurateDBSession($accessToken); // ambil session dari API accurate

            if (isset($hitAPI['error'])) {
                Log::debug('[FAIL] GET ACCURATE DB SESSION', $hitAPI);
                return ['error' => $hitAPI];
            }

            session(['accurate_session' => $hitAPI['session']]);
            session(['accurate_host' => $hitAPI['host']]);

            $result = [
                'session_id' => $hitAPI['session'],
                'user_request' => $user,
                'accurate_host' => $hitAPI['host'],
                'access_token' => $accessToken,
                'created_at' => date('Y-m-d H:i:s')
            ];

            // simpen ke db
            $save = $this->saveDBSession($result);
            if (isset($save['error'])) {
                return $save;
            }

            return $result;
        }

        $arrayResult = $getSessionFromDB->toArray();

        session(['accurate_session' => $getSessionFromDB['session']]);
        session(['accurate_host' => $getSessionFromDB['host']]);

        return $arrayResult;
    }

    function saveDBSession(array $data): array
    {
        DB::beginTransaction();

        $accurateSession = new AccurateSession();
        $accurateSession->session_id = $data['session_id'];
        $accurateSession->user_request = $data['user_request'];
        $accurateSession->accurate_host = $data['accurate_host'];
        $accurateSession->access_token = $data['access_token'];
        $accurateSession->created_at = $data['created_at'];

        $saveSession = $accurateSession->save();

        if ($saveSession == false) {
            DB::rollBack();

            return ['error' => 'failed to create new DB session'];
        }

        DB::commit();

        return [];
    }
}
