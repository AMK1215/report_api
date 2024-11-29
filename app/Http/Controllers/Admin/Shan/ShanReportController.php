<?php

namespace App\Http\Controllers\Admin\Shan;

use App\Http\Controllers\Controller;
use App\Models\Admin\ReportTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShanReportController extends Controller
{
    public function index(Request $request)
    {
        $authUser = auth()->user(); // Get the authenticated admin

        // Fetch transactions of players related to agents managed by the authenticated admin
        $reportTransactions = ReportTransaction::select(
            'report_transactions.user_id',
            'users.name as player_name', // The player's name
            'agents.name as agent_name', // The agent's name
            DB::raw('COUNT(report_transactions.id) AS transaction_count'),
            DB::raw('SUM(report_transactions.transaction_amount) AS total_transaction_amount'),
            DB::raw('MAX(report_transactions.created_at) AS latest_transaction_date') // Use MAX or MIN for created_at
        )
            ->join('users', 'report_transactions.user_id', '=', 'users.id') // Join users to get player data
            ->join('users as agents', 'users.agent_id', '=', 'agents.id') // Join to get the agent data
            ->where('agents.agent_id', $authUser->id) // Filter agents by the authenticated admin's ID
            ->groupBy('report_transactions.user_id', 'users.name', 'agents.name') // Group by player and agent names
            ->orderByDesc('latest_transaction_date') // Order by latest transaction date
            ->when(isset($request->start_date) && isset($request->end_date), function ($query) use ($request) {
                $query->whereBetween('report_transactions.created_at', [$request->start_date.' 00:00:00', $request->end_date.' 23:59:59']);
            })
            ->get();

        return view('admin.shan.reports.index', compact('reportTransactions'));
    }

    public function show($user_id)
    {
        // Query to get all report transactions for a specific user
        $userTransactions = ReportTransaction::where('user_id', $user_id)
            ->orderByDesc('created_at')
            ->get();

        // Pass the transactions and the user_id to the view
        return view('admin.shan.reports.show', compact('userTransactions', 'user_id'));
    }

    public function ShanAgentReportIndex(Request $request)
    {
        $authUser = auth()->user(); // Get the authenticated agent

        // Fetch report data for users (players) related to the authenticated agent
        $reportTransactions = ReportTransaction::select(
            'report_transactions.user_id',
            'users.name',
            DB::raw('COUNT(report_transactions.id) AS transaction_count'),
            DB::raw('SUM(report_transactions.transaction_amount) AS total_transaction_amount'),
            DB::raw('MAX(report_transactions.created_at) AS latest_transaction_date') // Use MAX or MIN for created_at
        )
            ->join('users', 'report_transactions.user_id', '=', 'users.id')
            ->where('users.agent_id', $authUser->id) // Filter users by the agent's ID
            ->groupBy('report_transactions.user_id', 'users.name')
            ->orderByDesc('latest_transaction_date') // Now ordering by the alias of the aggregate function
            ->when(isset($request->start_date) && isset($request->end_date), function ($query) use ($request) {
                $query->whereBetween('report_transactions.created_at', [$request->start_date.' 00:00:00', $request->end_date.' 23:59:59']);
            })
            ->get();

        return view('admin.shan.reports.agentindex', compact('reportTransactions'));
    }
}
