<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use App\Models\Salesperson;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Receipt;

class AuthController extends Controller
{
    
    public function index()
    {
        try{
            if (Auth::guard('sales')->check()) {
                    return redirect()->route('user.dashboard');
            }else{
                return redirect()->route('user.login');
            }

        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    }

    public function login()
    {
        return view("user.auth.login");
    }

    public function postLogin(Request $request)
    {
        try{
            $request->validate([
                "mobile" => "required",
                "password" => "required",
            ]);
            $user = Salesperson::where('mobile', $request->mobile)->first();
            if($user){
                if (Auth::guard('sales')->attempt($request->only('mobile', 'password')))
                {
                    $request->session()->regenerate();
                    return redirect()->route("user.dashboard")->with("success", "Welcome to your dashboard.");
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

    public function updatePassword(Request $request)
    {
        try{
            $request->validate([
                "old_password" => "required",
                "new_password" => "required|confirmed",
            ]);
            $salesperson = Auth::guard('sales')->user();

            if (!Hash::check($request->old_password, $salesperson->password)) {
                return back()->with("error", "Old Password Doesn't match!");
            }

            Salesperson::whereKey($salesperson->id)->update([
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
            Auth::guard('sales')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return redirect()->route("user.login")->withSuccess('Logout Successful!');
        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    }

    public function profile()
    {
        try{
            $user = Auth::guard('sales')->user();
            return view("user.auth.profile", compact("user"));

        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    }

    public function updateProfile(Request $request)
    {
        try
        {
            $user = Auth::guard('sales')->user();
            $data = $request->all();
            $validator = Validator::make($data,[
                "name" => "required",
                "mobile" => "required|min:9|unique:salespersons,mobile," .$user->id,
            ]);
            
            if($validator->fails()) {
                return redirect()->back()->withInput($request->all())->withErrors($validator->errors());
            }
        
            $user->name = $request->name;
            $user->mobile = $request->mobile;
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
    public function dashboard(Request $request)
    {   
        $salesperson = Auth::guard('sales')->user();
        $firmId = $request->query('firm_id');

        $firms = Customer::query()
            ->select('customers.id', 'customers.firm_name')
            ->join('invoices', 'invoices.firm_id', '=', 'customers.id')
            ->where('invoices.salesperson_id', $salesperson->id)
            ->where('invoices.status', 'pending')
            ->distinct()
            ->orderBy('customers.firm_name')
            ->get();

        $pendingInvoices = Invoice::with('firm:id,firm_name')
            ->withSum('receipts as paid_amount', 'given_amount')
            ->where('salesperson_id', $salesperson->id)
            ->where('status','pending')
            ->when($firmId, function ($query) use ($firmId) {
                $query->where('firm_id', $firmId);
            })
            ->orderBy('invoices.date', 'asc')
            ->orderBy(
                Customer::select('firm_name')
                    ->whereColumn('customers.id', 'invoices.firm_id')
                    ->limit(1),
                'asc'
            )
            ->get()
            ->map(function ($invoice) {
                $paidAmount = (float) ($invoice->paid_amount ?? 0);
                $payableAmount = (float) $invoice->payable_amount;
                $invoice->remaining_amount = max($payableAmount - $paidAmount, 0);
                return $invoice;
            })
            ->filter(function ($invoice) {
                return $invoice->remaining_amount > 0;
            })
            ->values();
        return view('user.dashboard.index', compact('pendingInvoices', 'firms', 'firmId'));
    }

    public function firmLedgerReport(Request $request)
    {
        $salesperson = Auth::guard('sales')->user();
        $firmId = $request->firm_id;
        $selectedFirm = null;
        $ledgerEntries = collect();
        $totalBillAmount = 0;
        $totalReceiptAmount = 0;
        $totalDiscountAmount = 0;
        $totalPendingAmount = 0;

        $firms = Customer::query()
            ->whereIn('id', function ($query) use ($salesperson) {
                $query->select('firm_id')
                    ->from('invoices')
                    ->where('salesperson_id', $salesperson->id)
                    ->whereNull('deleted_at');
            })
            ->orderBy('firm_name')
            ->get(['id', 'firm_name']);

        $invoiceTotals = Invoice::query()
            ->select(
                'firm_id',
                \DB::raw('SUM(COALESCE(payable_amount, amount)) as total_debit')
            )
            ->where('salesperson_id', $salesperson->id)
            ->groupBy('firm_id');

        $receiptTotals = Receipt::query()
            ->join('invoices', 'invoices.id', '=', 'receipts.invoice_id')
            ->select(
                'receipts.firm_id',
                \DB::raw('SUM(receipts.given_amount) as total_credit')
            )
            ->where('invoices.salesperson_id', $salesperson->id)
            ->where('receipts.status', 'accpet')
            ->groupBy('receipts.firm_id');

        $reports = Customer::query()
            ->select(
                'customers.id',
                'customers.firm_name',
                \DB::raw('COALESCE(invoice_totals.total_debit, 0) as total_debit'),
                \DB::raw('COALESCE(receipt_totals.total_credit, 0) as total_credit'),
                \DB::raw('COALESCE(invoice_totals.total_debit, 0) - COALESCE(receipt_totals.total_credit, 0) as balance')
            )
            ->joinSub($invoiceTotals, 'invoice_totals', function ($join) {
                $join->on('invoice_totals.firm_id', '=', 'customers.id');
            })
            ->leftJoinSub($receiptTotals, 'receipt_totals', function ($join) {
                $join->on('receipt_totals.firm_id', '=', 'customers.id');
            })
            ->when($firmId, function ($query) use ($firmId) {
                $query->where('customers.id', $firmId);
            })
            ->orderBy('customers.firm_name')
            ->get();

        if ($firmId) {
            $selectedFirm = Customer::query()
                ->where('id', $firmId)
                ->whereIn('id', $firms->pluck('id'))
                ->first(['id', 'firm_name', 'phone']);

            if ($selectedFirm) {
                $invoiceEntries = Invoice::query()
                    ->select(
                        'id',
                        'date',
                        'invoice_no as reference_no',
                        \DB::raw("'invoice' as entry_type"),
                        \DB::raw('COALESCE(payable_amount, amount) as debit'),
                        \DB::raw('0 as credit'),
                        \DB::raw('COALESCE(discount_amount, 0) as discount')
                    )
                    ->where('salesperson_id', $salesperson->id)
                    ->where('firm_id', $firmId)
                    ->get();

                $receiptEntries = Receipt::query()
                    ->join('invoices', 'invoices.id', '=', 'receipts.invoice_id')
                    ->select(
                        'receipts.id',
                        'receipts.date',
                        'receipts.receipt_no as reference_no',
                        \DB::raw("'receipt' as entry_type"),
                        \DB::raw('0 as debit'),
                        'receipts.given_amount as credit',
                        \DB::raw('COALESCE(receipts.discount, 0) as discount')
                    )
                    ->where('invoices.salesperson_id', $salesperson->id)
                    ->where('receipts.firm_id', $firmId)
                    ->where('receipts.status', 'accpet')
                    ->get();

                $ledgerEntries = $invoiceEntries
                    ->concat($receiptEntries)
                    ->sortBy([['date', 'asc'], ['id', 'asc']])
                    ->values();

                $totalBillAmount = $invoiceEntries->sum('debit');
                $totalReceiptAmount = $receiptEntries->sum('credit');
                $totalDiscountAmount = $invoiceEntries->sum('discount') + $receiptEntries->sum('discount');
                $totalPendingAmount = $totalBillAmount - $totalReceiptAmount;
            }
        }

        return view('user.dashboard.firm-ledger', [
            'reports' => $reports,
            'firms' => $firms,
            'firmId' => $firmId,
            'totalDebit' => $reports->sum('total_debit'),
            'totalCredit' => $reports->sum('total_credit'),
            'totalBalance' => $reports->sum('balance'),
            'selectedFirm' => $selectedFirm,
            'ledgerEntries' => $ledgerEntries,
            'totalBillAmount' => $totalBillAmount,
            'totalReceiptAmount' => $totalReceiptAmount,
            'totalDiscountAmount' => $totalDiscountAmount,
            'totalPendingAmount' => $totalPendingAmount,
        ]);
    }


}
