<?php

namespace App\Http\Controllers\Admin\Owner;

use App\Enums\TransactionName;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\OwnerRequest;
use App\Http\Requests\TransferLogRequest;
use App\Models\Admin\TransferLog;
use App\Models\User;
use App\Services\WalletService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class OwnerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    private const OWNER_ROLE = 2;

    public function index()
    {
        abort_if(
            Gate::denies('owner_index'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );
        //kzt
        $users = User::with('roles')
            ->whereHas('roles', function ($query) {
                $query->where('role_id', self::OWNER_ROLE);
            })
            ->where('agent_id', auth()->id())
            ->orderBy('id', 'desc')
            ->get();

        //kzt
        return view('admin.owner.index', compact('users'));
    }

    public function OwnerPlayerList()
    {
        abort_if(
            Gate::denies('owner_access'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden | You cannot access this page because you do not have permission'
        );

        $adminId = auth()->id(); // Get the authenticated admin's ID

        // Fetch agents and their related players for this admin
        $agents = User::with(['createdAgents', 'createdAgents.players'])
            ->where('id', $adminId) // Only fetch data for the current admin
            ->get();

        return view('admin.player.list', compact('agents'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OwnerRequest $request)
    {
        abort_if(
            Gate::denies('owner_create'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );
        $admin = Auth::user();

        $user_name = session()->get('user_name');

        $inputs = $request->validated();

        $userPrepare = array_merge(
            $inputs,
            [
                'user_name' => $user_name,
                'password' => Hash::make($inputs['password']),
                'agent_id' => Auth()->user()->id,
                'type' => UserType::Owner,
                'site_name' => $inputs['site_name'],
            ]
        );

        if (isset($inputs['amount']) && $inputs['amount'] > $admin->balanceFloat) {
            throw ValidationException::withMessages([
                'amount' => 'Insufficient balance for transfer.',
            ]);
        }
        // image
        if ($request->agent_logo) {
            $image = $request->file('agent_logo');
            $ext = $image->getClientOriginalExtension();
            $filename = uniqid('logo').'.'.$ext; // Generate a unique filename
            $image->move(public_path('assets/img/logo/'), $filename); // Save the file
            $userPrepare['agent_logo'] = $filename;
        }

        $user = User::create($userPrepare);
        $user->roles()->sync(self::OWNER_ROLE);

        if (isset($inputs['amount'])) {
            app(WalletService::class)->transfer($admin, $user, $inputs['amount'], TransactionName::CreditTransfer);
        }
        session()->forget('user_name');

        return redirect()->back()
            ->with('success', 'Owner created successfully')
            ->with('password', $request->password)
            ->with('username', $user->user_name);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(
            Gate::denies('owner_create'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );
        $user_name = $this->generateRandomString();

        session()->put('user_name', $user_name);

        return view('admin.owner.create', compact('user_name', 'user_name'));
    }

    private function generateRandomString()
    {
        $randomNumber = mt_rand(10000000, 99999999);

        return 'O'.$randomNumber;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        abort_if(
            Gate::denies('owner_show'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );

        $master = User::find($id);

        return view('admin.owner.show', compact('master'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        abort_if(
            Gate::denies('owner_edit'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );

        $owner = User::find($id);

        return view('admin.owner.edit', compact('owner'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function getCashIn(string $id)
    {
        abort_if(
            Gate::denies('make_transfer'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );

        $owner = User::find($id);

        return view('admin.owner.cash_in', compact('owner'));
    }

    public function getCashOut(string $id)
    {
        abort_if(
            Gate::denies('make_transfer'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );

        // Assuming $id is the user ID
        $owner = User::findOrFail($id);

        return view('admin.owner.cash_out', compact('owner'));
    }

    public function makeCashIn(TransferLogRequest $request, $id)
    {

        abort_if(
            Gate::denies('make_transfer') || ! $this->ifChildOfParent($request->user()->id, $id),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );

        try {

            $inputs = $request->validated();
            $master = User::findOrFail($id);
            $admin = Auth::user();
            $cashIn = $inputs['amount'];
            if ($cashIn > $admin->balanceFloat) {
                throw new \Exception('You do not have enough balance to transfer!');
            }

            // Transfer money
            app(WalletService::class)->transfer($admin, $master, $request->validated('amount'), TransactionName::CreditTransfer, ['note' => $request->note]);

            return redirect()->back()->with('success', 'Money fill request submitted successfully!');
        } catch (Exception $e) {

            session()->flash('error', $e->getMessage());

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function makeCashOut(TransferLogRequest $request, string $id)
    {

        abort_if(
            Gate::denies('make_transfer') || ! $this->ifChildOfParent($request->user()->id, $id),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );

        try {
            $inputs = $request->validated();

            $master = User::findOrFail($id);
            $admin = Auth::user();
            $cashOut = $inputs['amount'];

            if ($cashOut > $master->balanceFloat) {

                return redirect()->back()->with('error', 'You do not have enough balance to transfer!');
            }

            // Transfer money
            app(WalletService::class)->transfer($master, $admin, $request->validated('amount'), TransactionName::DebitTransfer, ['note' => $request->note]);

            return redirect()->back()->with('success', 'Money fill request submitted successfully!');
        } catch (Exception $e) {

            session()->flash('error', $e->getMessage());

            return redirect()->back()->with('error', $e->getMessage());
        }

        // Redirect back with a success message
        return redirect()->back()->with('success', 'Money fill request submitted successfully!');
    }

    public function getTransferDetail($id)
    {
        abort_if(
            Gate::denies('make_transfer') || ! $this->ifChildOfParent(request()->user()->id, $id),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );
        $transfer_detail = TransferLog::where('from_user_id', $id)
            ->orWhere('to_user_id', $id)
            ->get();

        return view('admin.owner.transfer_detail', compact('transfer_detail'));
    }

    public function banOwner($id)
    {
        abort_if(
            ! $this->ifChildOfParent(request()->user()->id, $id),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );

        $user = User::find($id);
        $user->update(['status' => $user->status == 1 ? 0 : 1]);

        return redirect()->back()->with(
            'success',
            'User '.($user->status == 1 ? 'activate' : 'inactive').' successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, string $id)
    // {
    //     abort_if(
    //         Gate::denies('owner_edit') || ! $this->ifChildOfParent($request->user()->id, $id),
    //         Response::HTTP_FORBIDDEN,
    //         '403 Forbidden |You cannot  Access this page because you do not have permission'
    //     );

    //     $user = User::find($id);

    //     if ($request->file('agent_logo')) {
    //         File::delete(public_path('assets/img/logo/'.$user->agent_logo));
    //         // image
    //         $image = $request->file('agent_logo');
    //         $ext = $image->getClientOriginalExtension();
    //         $filename = uniqid('banner').'.'.$ext; // Generate a unique filename
    //         $image->move(public_path('assets/img/logo/'), $filename); // Save the file
    //         $request->agent_logo = $filename;
    //     }

    //     $user->update([
    //         'name' => $request->name,
    //         'phone' => $request->phone,
    //         'player_name' => $request->player_name,
    //         'agent_logo' => $request->agent_logo,
    //     ]);

    //     return redirect()->back()
    //         ->with('success', 'Owner Updated successfully');
    // }
    // public function update(Request $request, string $id)
    // {
    //     dd($request->all());
    //     abort_if(
    //         Gate::denies('owner_edit') || ! $this->ifChildOfParent($request->user()->id, $id),
    //         Response::HTTP_FORBIDDEN,
    //         '403 Forbidden |You cannot Access this page because you do not have permission'
    //     );

    //     // Find the user to update
    //     $user = User::findOrFail($id);

    //     // Validate the input
    //     $request->validate([
    //         'user_name' => 'nullable|string|max:255',
    //         'name' => 'required|string|max:255',
    //         'phone' => 'required|numeric|digits_between:10,15|unique:users,phone,'.$id,
    //         'agent_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    //     ]);

    //     // Handle the logo file if uploaded
    //     if ($request->file('agent_logo')) {
    //         // Delete the old logo if it exists
    //         if ($user->agent_logo && File::exists(public_path('assets/img/logo/'.$user->agent_logo))) {
    //             File::delete(public_path('assets/img/logo/'.$user->agent_logo));
    //         }

    //         // Upload the new logo
    //         $image = $request->file('agent_logo');
    //         $ext = $image->getClientOriginalExtension();
    //         $filename = uniqid('logo').'.'.$ext;
    //         $image->move(public_path('assets/img/logo/'), $filename);

    //         // Update the logo field
    //         $user->agent_logo = $filename;
    //     }

    //     // Update other user fields
    //     $user->update([
    //         'user_name' => $request->player_name,
    //         'name' => $request->name,
    //         'phone' => $request->phone,
    //         'agent_logo' => $user->agent_logo, // Set the updated logo
    //     ]);

    //     // Redirect back with a success message
    //     return redirect()->back()
    //         ->with('success', 'Owner updated successfully!');
    // }

    public function update(Request $request, string $id)
    {
        //Log::info('Update method called.', ['request_data' => $request->all()]);

        // Check permissions
        abort_if(
            Gate::denies('owner_edit') || ! $this->ifChildOfParent($request->user()->id, $id),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden | You cannot access this page because you do not have permission'
        );

        // Log permission passed
        //Log::info('Permission granted.');

        // Find the user
        $user = User::findOrFail($id);

        // Validate input
        //Log::info('Validating request data.');
        $request->validate([
            'user_name' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'phone' => 'required|numeric|digits_between:10,15|unique:users,phone,'.$id,
            'agent_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        //Log::info('Validation passed.');

        // Handle file upload
        if ($request->file('agent_logo')) {
            Log::info('File uploaded.', [
                'original_name' => $request->file('agent_logo')->getClientOriginalName(),
                'size' => $request->file('agent_logo')->getSize(),
            ]);

            // Delete old logo if exists
            if ($user->agent_logo && File::exists(public_path('assets/img/logo/'.$user->agent_logo))) {
                File::delete(public_path('assets/img/logo/'.$user->agent_logo));
            }

            // Upload new logo
            $image = $request->file('agent_logo');
            $filename = uniqid('logo').'.'.$image->getClientOriginalExtension();
            $image->move(public_path('assets/img/logo/'), $filename);
            $user->agent_logo = $filename;
        } else {
            Log::info('No file uploaded for agent_logo.');
        }

        // Update fields
        $user->update([
            'user_name' => $request->user_name ?? $user->user_name,
            'name' => $request->name,
            'phone' => $request->phone,
            'agent_logo' => $user->agent_logo, // Updated logo
        ]);

        //Log::info('Owner updated successfully.', ['user' => $user]);

        return redirect()->back()
            ->with('success', 'Owner updated successfully!');
    }

    public function getChangePassword($id)
    {
        $owner = User::find($id);

        return view('admin.owner.change_password', compact('owner'));
    }

    public function makeChangePassword($id, Request $request)
    {
        abort_if(
            Gate::denies('make_transfer') || ! $this->ifChildOfParent(request()->user()->id, $id),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot  Access this page because you do not have permission'
        );

        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        $master = User::find($id);
        $master->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()
            ->with('success', 'Owner Change Password successfully')
            ->with('password', $request->password)
            ->with('username', $master->user_name);
    }
}
