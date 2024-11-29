<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DepositLogResource;
use App\Models\DepositRequest;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepositRequestController extends Controller
{
    use HttpResponses;

    public function deposit(Request $request)
    {
        $data = $request->validate([
            'agent_payment_type_id' => ['required', 'integer'],
            'amount' => ['required', 'integer', 'min: 3000'],
            'refrence_no' => ['required', 'digits:6'],
        ]);
        $player = Auth::user();

        $deposit = DepositRequest::create([
            'agent_payment_type_id' => $request->agent_payment_type_id,
            'user_id' => $player->id,
            'agent_id' => $player->agent_id,
            'amount' => $request->amount,
            'refrence_no' => $request->refrence_no,
        ]);

        return $this->success($deposit, 'Deposit Request Success');

    }

    public function log()
    {
        $deposit = DepositRequest::with('bank')->where('user_id', Auth::id())->get();

        return $this->success(DepositLogResource::collection($deposit));
    }
}
