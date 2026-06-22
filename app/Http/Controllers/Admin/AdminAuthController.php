<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Salesperson;
use App\Models\Receipt;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AdminAuthController extends Controller
{
    
    public function index()
    {
        try{
            if(Auth::user()) {
                $user = Auth::user();
                if($user->role == "admin") {
                    return redirect()->route('admin.dashboard');
                }else{
                    return back()->with("error","Opps! You do not have access this");
                }
            }else{
                return redirect()->route('admin.login');
            }

        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    }

    public function login()
    {
        return view("admin.auth.login");
    }

    public function registration()
    {
        return view("admin.auth.registration");
    }

    public function postLogin(Request $request)
    {
        try{
            $request->validate([
                "email" => "required",
                "password" => "required",
            ]);
            $user = User::where('role','admin')->where('email',$request->email)->first();
            if($user){
                $credentials = $request->only("email", "password");
                if(Auth::attempt([
                        'email' => $request->email,
                        'password' => $request->password,
                        'role' => function ($query) {
                            $query->where('role','admin');
                        }
                    ]))
                {
                    return redirect()->route("admin.dashboard")->with("success", "Welcome to your dashboard.");
                }
                return back()->with("error","Invalid credentials");
            }else{
                return back()->with("error","Invalid credentials");
            }

        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    }

    public function postRegistration(Request $request)
    {
        $request->validate([
            "name" => "required",
            "email" => "required|email|unique:users",
            "password" => "required|min:6",
        ]);

        $data = $request->all();
        $check = $this->create($data);

        return redirect("admin.dashboard")->with("success","Great! You have Successfully loggedin");
    }

    public function create(array $data)
    {
        return User::create([
            "name" => $data["name"],
            "email" => $data["email"],
            "password" => Hash::make($data["password"]),
        ]);
    }

    public function showForgetPasswordForm()
    {
        return view("admin.auth.forgot-password");
    }

    public function submitForgetPasswordForm(Request $request)
    {
        try{
            $request->validate([
                "email" => "required|email|exists:users",
            ]);

            $token = Str::random(64);

            DB::table("password_resets")->insert([
                "email" => $request->email,
                "token" => $token,
                "created_at" => Carbon::now(),
            ]);

            $new_link_token = url("admin/reset-password/" . $token);
            Mail::send("admin.email.forgot-password",["token" => $new_link_token, "email" => $request->email],
                function ($message) use ($request) {
                    $message->to($request->email);
                    $message->subject("Reset Password");
                }
            );
            return redirect()->route("admin.login")->with("success","We have e-mailed your password reset link!");
        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    
    }

    public function showResetPasswordForm($token)
    {
        try{    
            $user = DB::table("password_resets")->where("token", $token)->first();
            $email = $user->email;
            return view("admin.auth.reset-password", ["token" => $token,"email" => $email,]);
        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    }

    public function submitResetPasswordForm(Request $request)
    {
        try{
            $request->validate([
                "email" => "required|email|exists:users",
                "password" => "required|string|min:6|confirmed",
                "password_confirmation" => "required",
            ]);

            $updatePassword = DB::table("password_resets")->where(["email" => $request->email,"token" => $request->token])->first();

            if (!$updatePassword) {
                return back()->withInput()->with("error", "Invalid token!");
            }

            $user = User::where("email", $request->email)->update(["password" => Hash::make($request->password)]);

            DB::table("password_resets")->where(["email" => $request->email])->delete();

            return redirect()->route("admin.login")->with("success","Your password has been changed successfully!");
        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    }

    public function changePassword()
    {
        return view("admin.auth.change-password");
    }

    public function updatePassword(Request $request)
    {
        try{
            $request->validate([
                "old_password" => "required",
                "new_password" => "required|confirmed",
            ]);
            #Match The Old Password
            if (!Hash::check($request->old_password, auth()->user()->password)) {
                return back()->with("error", "Old Password Doesn't match!");
            }
            #Update the new Password
            User::whereId(auth()->user()->id)->update([
                "password" => Hash::make($request->new_password),
            ]);
            return back()->with("success", "Password changed successfully!");
        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    }

    

    public function logout()
    {
        try{
            Session::flush();
            Auth::logout();
            return redirect()->route("admin.login")->withSuccess('Logout Successful!');
        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    }

    public function adminProfile()
    {
        try{
            $user = Auth::user();
            return view("admin.auth.profile", compact("user"));

        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    }

    public function updateAdminProfile(Request $request)
    {
        try
        {
            $user = Auth::user();
            $data = $request->all();
            $validator = Validator::make($data,[
                "first_name" => "required",
                "last_name" => "required",
                "phone" => "required|min:9|unique:users,phone," .$user->id,
                "email" => "required|email|unique:users,email," . $user->id,
                "avatar" => "sometimes|image|mimes:jpeg,jpg,png|max:5000"
            ]);
            
            if($validator->fails()) {
                return redirect()->back()->withInput($request->all())->withErrors($validator->errors());
            }
            
            if($request->file("avatar")) {
                $file = $request->file("avatar");
                $filename = time() . $file->getClientOriginalName();
                $folder = "uploads/user/";
                $path = public_path($folder);
                if (!File::exists($path)) {
                    File::makeDirectory($path, $mode = 0777, true, true);
                }
                $file->move($path, $filename);
                $user->avatar = $folder . $filename;
            }
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->full_name = $request->first_name . " " . $request->last_name;
            $user->phone = $request->phone;
            $user->email = $request->email;
            $user->save();
            return redirect()->back()->with("success", "Profile update successfully!");
        }
        catch (Exception $e) {
            return redirect()->back()->with("error", $e->getMessage());
        }
    }

    /**
     * Admin Dashboard Data
     * Fetch summary statistics for dashboard cards
     */
    // public function adminDashboard()
    // {
    //     $acceptedReceiptTotals = Receipt::select(
    //             'invoice_id',
    //             DB::raw('SUM(given_amount) as total_paid')
    //         )
    //         ->where('status', 'accpet')
    //         ->groupBy('invoice_id');

    //     /* ------------------------------
    //     | Invoice Statistics
    //     ------------------------------*/

    //    // Total number of pending invoices
    //    $pendingInvoiceCount = Invoice::where('status', 'pending')->count();

    //    // Remaining due amount of pending invoices after accepted receipts
    //    $totalPendingBillAmount = Invoice::query()
    //         ->leftJoinSub($acceptedReceiptTotals, 'accepted_receipts', function ($join) {
    //             $join->on('accepted_receipts.invoice_id', '=', 'invoices.id');
    //         })
    //         ->where('invoices.status', 'pending')
    //         ->sum(DB::raw('GREATEST(invoices.payable_amount - COALESCE(accepted_receipts.total_paid, 0), 0)'));


    //    // Rejected / Unapproved receipts count
    //     $unapprovedReceiptCount = Receipt::where('status', 'pending')->count();

    //     // Total received amount
    //     $unapprovedReceivedAmount = Receipt::where('status', 'pending')->sum('given_amount');


    //     /* ------------------------------
    //     | Oldest Pending Invoices
    //     ------------------------------*/

    //     $pendingInvoices = Invoice::select(
    //         'invoices.*',
    //         'customers.firm_name as firm_name',
    //         'salespersons.name as salesman_name',
    //         DB::raw('DATEDIFF(NOW(), invoices.date) as pending_days')
    //         )
    //         ->leftJoin('customers', 'customers.id', '=', 'invoices.firm_id')
    //         ->leftJoin('salespersons', 'salespersons.id', '=', 'invoices.salesperson_id')
    //         ->where('invoices.status', 'pending')
    //         ->orderBy('invoices.date', 'asc') // oldest first
    //         ->limit(20)
    //         ->get();


    //     /* ------------------------------
    //     | Return Dashboard View
    //     ------------------------------*/
    //     return view('admin.dashboard.index', compact(
    //         'unapprovedReceiptCount',
    //         'pendingInvoices',
    //         'pendingInvoiceCount',
    //         'totalPendingBillAmount',
    //         'unapprovedReceivedAmount'
    //     ));
    // }


    public function adminDashboard(Request $request)
    {
        $companies = collect();
        $selectedCompanyId = Helper::getSessionCompanyId();

        // Handle company filter change
        if ($request->has('company_id')) {
            $selectedCompanyId = $request->company_id;
            if ($selectedCompanyId) {
                Helper::setSessionCompanyId($selectedCompanyId);
            } else {
                Helper::clearSessionCompanyId();
                $selectedCompanyId = null;
            }
        }

        // Fetch companies dropdown for super admin
        if (Helper::isSuperAdmin()) {
            $companies = User::where('role', 'admin')
                ->where('id', '!=', Helper::SUPER_ADMIN_ID)
                ->orderBy('full_name')
                ->get(['id', 'full_name', 'email']);
        }

        $acceptedReceiptTotals = Receipt::select(
                'invoice_id',
                DB::raw('SUM(given_amount) as total_paid')
            )
            ->where('status', 'accpet');
        Helper::applyUserScope($acceptedReceiptTotals, 'receipts');
        $acceptedReceiptTotals = $acceptedReceiptTotals->groupBy('invoice_id');

        /* ------------------------------
        | Invoice Statistics
        ------------------------------*/

        // Total number of pending invoices
        $pendingInvoiceCountQuery = Invoice::where('status', 'pending');
        Helper::applyUserScope($pendingInvoiceCountQuery, 'invoices');
        $pendingInvoiceCount = $pendingInvoiceCountQuery->count();

        // Total remaining pending amount after accepted receipts
        $totalPendingBillAmountQuery = Invoice::query()
            ->leftJoinSub($acceptedReceiptTotals, 'accepted_receipts', function ($join) {
                $join->on('accepted_receipts.invoice_id', '=', 'invoices.id');
            })
            ->where('invoices.status', 'pending');
        Helper::applyUserScope($totalPendingBillAmountQuery, 'invoices');
        $totalPendingBillAmount = $totalPendingBillAmountQuery
            ->sum(DB::raw('GREATEST(invoices.payable_amount - COALESCE(accepted_receipts.total_paid, 0), 0)'));

        // Pending receipts count
        $unapprovedReceiptCountQuery = Receipt::where('status', 'pending');
        Helper::applyUserScope($unapprovedReceiptCountQuery, 'receipts');
        $unapprovedReceiptCount = $unapprovedReceiptCountQuery->count();

        // Pending receipts total amount
        $unapprovedReceivedAmountQuery = Receipt::where('status', 'pending');
        Helper::applyUserScope($unapprovedReceivedAmountQuery, 'receipts');
        $unapprovedReceivedAmount = $unapprovedReceivedAmountQuery->sum('given_amount');

        /* ------------------------------
        | Oldest Pending Invoices
        ------------------------------*/

        $pendingInvoicesQuery = Invoice::select(
            'invoices.*',
            'customers.firm_name as firm_name',
            'salespersons.name as salesman_name',
            'users.full_name as company_name',
            DB::raw('DATEDIFF(NOW(), invoices.date) as pending_days')
        )
        ->leftJoin('customers', 'customers.id', '=', 'invoices.firm_id')
        ->leftJoin('salespersons', 'salespersons.id', '=', 'invoices.salesperson_id')
        ->leftJoin('users', 'users.id', '=', 'invoices.user_id')
        ->where('invoices.status', 'pending');
        Helper::applyUserScope($pendingInvoicesQuery, 'invoices');
        $pendingInvoices = $pendingInvoicesQuery
            ->orderBy('invoices.date', 'asc')
            ->paginate(20);

        /* ------------------------------
        | Return Dashboard View
        ------------------------------*/
        return view('admin.dashboard.index', compact(
            'unapprovedReceiptCount',
            'pendingInvoices',
            'pendingInvoiceCount',
            'totalPendingBillAmount',
            'unapprovedReceivedAmount',
            'companies',
            'selectedCompanyId'
        ));
    }


}
