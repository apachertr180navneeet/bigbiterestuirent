<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ReceiptController extends Controller
{
    public function create(Request $request)
    {
        $salesperson = Auth::guard('sales')->user();

        $customers = Customer::active()
            ->whereIn('id', function ($query) use ($salesperson) {
                $query->select('firm_id')
                    ->from('invoices')
                    ->where('salesperson_id', $salesperson->id)
                    ->where('status', 'pending')
                    ->whereNull('deleted_at');
            })
            ->orderBy('firm_name')
            ->get(['id', 'firm_name', 'discount']);

        $selectedInvoice = null;

        if ($request->filled('invoice_id')) {
            $selectedInvoice = Invoice::with([
                    'firm:id,firm_name',
                    'salesperson:id,name',
                ])
                ->withSum('receipts as paid_amount', 'given_amount')
                ->where('id', $request->invoice_id)
                ->where('salesperson_id', $salesperson->id)
                ->where('status', 'pending')
                ->firstOrFail([
                    'id',
                    'firm_id',
                    'invoice_no',
                    'amount',
                    'payable_amount',
                    'status',
                    'salesperson_id'
                ]);
        }

        $generatedReceiptNo = $this->generateReceiptNo();

        return view('user.receipt.create', compact('customers', 'generatedReceiptNo', 'selectedInvoice'));
    }

    public function store(Request $request)
    {
        $salesperson = Auth::guard('sales')->user();

        $request->validate([
            'date' => 'required|date',
            'receipt_no' => 'required|string|max:100|unique:receipts,receipt_no',
            'firm_id' => 'required|exists:customers,id',
            'invoice_id' => 'required|exists:invoices,id',
            'given_amount' => 'required|numeric|min:0.01',
            'mode' => 'nullable|string|max:100',
            'remark' => 'nullable|string|max:100',
        ]);

        [$customer, $invoice] = $this->resolveCustomerAndInvoice(
            $request->firm_id,
            $request->invoice_id,
            $salesperson->id
        );

        $invoiceAmount = (float) $invoice->amount;
        $payableAmount = (float) $invoice->payable_amount;

        $totalPaidBefore = (float) Receipt::where('invoice_id', $invoice->id)
            ->sum('given_amount');

        $newTotalPaid = $totalPaidBefore + (float) $request->given_amount;

        if ($newTotalPaid > $payableAmount) {
            throw ValidationException::withMessages([
                'given_amount' => 'Payment exceeds remaining payable amount.',
            ]);
        }

        $receipt = Receipt::create([
            'date' => $request->date,
            'receipt_no' => $request->receipt_no,
            'firm_id' => $customer->id,
            'invoice_id' => $invoice->id,
            'amount' => $invoiceAmount,
            'given_amount' => $request->given_amount,
            'final_amount' => $payableAmount,
            'sales_person' => $salesperson->name,
            'mode' => $request->mode,
            'manager_status' => 'pending',
            'status' => 'pending',
            'remark' => $request->remark,
            'user_id' => $invoice->user_id
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Receipt added successfully',
            'data' => $receipt,
        ]);
    }

    public function getPendingInvoices($firmId)
    {
        $salesperson = Auth::guard('sales')->user();

        $invoices = Invoice::with('salesperson:id,name')
            ->withSum('receipts as paid_amount', 'given_amount')
            ->where('firm_id', $firmId)
            ->where('salesperson_id', $salesperson->id)
            ->where('status', 'pending')
            ->get([
                'id',
                'firm_id',
                'invoice_no',
                'amount',
                'payable_amount',
                'status',
                'salesperson_id'
            ]);

        return response()->json($invoices);
    }

    private function resolveCustomerAndInvoice($firmId, $invoiceId, $salespersonId)
    {
        $customer = Customer::active()->findOrFail($firmId);

        $invoice = Invoice::where('id', $invoiceId)
            ->where('firm_id', $firmId)
            ->where('salesperson_id', $salespersonId)
            ->where('status', 'pending')
            ->first();

        if (!$invoice) {
            throw ValidationException::withMessages([
                'invoice_id' => 'Invoice does not belong to your pending invoices.',
            ]);
        }

        return [$customer, $invoice];
    }

    private function generateReceiptNo()
    {
        $lastReceipt = Receipt::where('receipt_no', 'like', 'RCPT-%')
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;

        if ($lastReceipt && preg_match('/^RCPT-(\d+)$/', $lastReceipt->receipt_no, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        return 'RCPT-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}
