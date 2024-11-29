<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Webhook\Result;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function index()
    {
        $adminId = auth()->id();

        $report = Result::select(
            DB::raw('SUM(total_bet_amount) as total_bet_amount'),
            DB::raw('SUM(win_amount) as total_win_amount'),
            DB::raw('SUM(net_win) as total_net_win'),
            DB::raw('COUNT(*) as total_games'),
            'players.name as player_name',
            'agents.name as agent_name',
            'players.id as user_id'
        )
            ->join('users as players', 'results.user_id', '=', 'players.id')
            ->join('users as agents', 'players.agent_id', '=', 'agents.id')
            ->groupBy('players.name', 'agents.name', 'players.id')
            ->paginate(10)
            ->withQueryString();;
        
        return view('admin.reports.index', compact('report'));
    }

    public function getReportDetails($player_id)
    {
        $details = Result::where('user_id', $player_id)
            ->join('users', 'results.user_id', '=', 'users.id')
            ->select('results.*', 'users.name as user_name')
            ->get();

        return view('admin.reports.detail', compact('details'));
    }

    public function getTransactionDetails($tranId)
    {
        $operatorId = 'delightMMK';

        $url = 'https://api.sm-sspi-prod.com/api/opgateway/v1/op/GetTransactionDetails';

        // Generate the RequestDateTime in UTC
        $requestDateTime = Carbon::now('UTC')->format('Y-m-d H:i:s');

        // Generate the signature using MD5 hashing
        $secretKey = '1OMJXOf88RHKpcuT';
        $functionName = 'GetTransactionDetails';
        $signatureString = $functionName.$requestDateTime.$operatorId.$secretKey;
        $signature = md5($signatureString);

        // Prepare request payload
        $payload = [
            'OperatorId' => $operatorId,
            'RequestDateTime' => $requestDateTime,
            'Signature' => $signature,
            'TranId' => $tranId,
        ];

        try {
            // Make the POST request to the API endpoint
            $response = Http::post($url, $payload);

            // Check if the response is successful
            if ($response->successful()) {
                return $response->json(); // Return the response data as JSON
            } else {
                Log::error('Failed to get transaction details', ['response' => $response->body()]);

                return response()->json(['error' => 'Failed to get transaction details'], 500);
            }
        } catch (\Exception $e) {
            Log::error('API request error', ['message' => $e->getMessage()]);

            return response()->json(['error' => 'API request error'], 500);
        }
    }
}
