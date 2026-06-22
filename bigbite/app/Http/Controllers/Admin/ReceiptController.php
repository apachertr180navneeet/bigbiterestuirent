<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\Salesperson;
use Illuminate\Support\Facades\Auth;

class ReceiptController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $salespersons = Salesperson::query()
            ->where('status', 'active')
            ->where('user_id',$userId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.receipt.index', compact('salespersons'));
    }

    public function getall(Request $request)
    {
        $userId = Auth::id();

        $validated = $request->validate([
            'receipt_no' => 'nullable|string|max:100',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'mode' => 'nullable|in:cash,upi,bank,card',
            'manager_status' => 'nullable|in:pending,accpet,rejected',
            'status' => 'nullable|in:pending,accpet,rejected',
            'salesperson_id' => 'nullable|exists:salespersons,id',
        ]);

        $query = Receipt::query()->with([
            'firm:id,firm_name',
            'invoice:id,invoice_no',
        ]);

        $query->where('user_id',$userId);

        if (!empty($validated['receipt_no'])) {
            $query->where('receipt_no', 'like', '%' . $validated['receipt_no'] . '%');
        }

        if (!empty($validated['date_from'])) {
            $query->whereDate('date', '>=', $validated['date_from']);
        }

        if (!empty($validated['date_to'])) {
            $query->whereDate('date', '<=', $validated['date_to']);
        }

        if (!empty($validated['mode'])) {
            $query->where('mode', $validated['mode']);
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (!empty($validated['salesperson_id'])) {
            $query->whereHas('invoice', function ($invoiceQuery) use ($validated) {
                $invoiceQuery->where('salesperson_id', $validated['salesperson_id']);
            });
        }

        $totalRecords = Receipt::count();
        $filteredRecords = $query->count();

        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $receipts = $query->orderBy('id', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        $receipts = $receipts->map(function ($item) {
            $item->firm_name = optional($item->firm)->firm_name;
            $item->invoice_no = optional($item->invoice)->invoice_no;
            
            // Format date to dd/mm/yyyy
            $item->date = $item->date 
                ? \Carbon\Carbon::parse($item->date)->format('d/m/Y') 
                : null;
            return $item;
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $receipts,
        ]);
    }

    public function create()
    {
        $userId = Auth::id();
        $customers = Customer::where('status', 'active')
            ->where('user_id',$userId)
            ->orderBy('firm_name')
            ->get(['id', 'firm_name']);

        $generatedReceiptNo = $this->generateReceiptNo();

        return view('admin.receipt.create', compact('customers', 'generatedReceiptNo'));
    }

    public function store(Request $request)
    {
        $userId = Auth::id();
        
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
            $request->invoice_id
        );

        $invoiceAmount = (float) $invoice->amount;

        $totalPaidBefore = (float) Receipt::where('invoice_id', $invoice->id)
            ->sum('given_amount');

        $newTotalPaid = $totalPaidBefore + (float) $request->given_amount;

        if ($newTotalPaid > $invoiceAmount) {
            throw ValidationException::withMessages([
                'given_amount' => 'Payment exceeds invoice amount.',
            ]);
        }

        $receipt = Receipt::create([
            'date' => $request->date,
            'receipt_no' => $request->receipt_no,
            'firm_id' => $customer->id,
            'invoice_id' => $invoice->id,
            'amount' => $invoiceAmount,
            'given_amount' => $request->given_amount,
            'final_amount' => $invoiceAmount,
            'sales_person' => optional($invoice->salesperson)->name,
            'mode' => $request->mode,
            'manager_status' => 'pending',
            'status' => 'pending',
            'remark' => $request->remark,
            'user_id' => $userId
        ]);

        $this->updateInvoiceStatus($invoice->id);

        return response()->json([
            'status' => true,
            'message' => 'Receipt added successfully',
            'data' => $receipt,
        ]);
    }

    public function edit($id)
    {
        $userId = Auth::id();
        $receipt = Receipt::findOrFail($id);

        $customers = Customer::where('status', 'active')
            ->where('user_id',$userId)
            ->orderBy('firm_name')
            ->get(['id', 'firm_name']);

        $invoices = Invoice::with('salesperson:id,name')
            ->where('user_id',$userId)
            ->withSum('receipts as paid_amount', 'given_amount')
            ->orderBy('invoice_no')
            ->get(['id', 'firm_id', 'invoice_no', 'amount', 'status', 'salesperson_id','remark']);

        return view('admin.receipt.edit', compact('receipt', 'customers', 'invoices'));
    }

    public function update(Request $request, $id)
    {
        $receipt = Receipt::findOrFail($id);

        $request->validate([
            'date' => 'required|date',
            'receipt_no' => 'required|string|max:100|unique:receipts,receipt_no,' . $id,
            'firm_id' => 'required|exists:customers,id',
            'invoice_id' => 'required|exists:invoices,id',
            'given_amount' => 'required|numeric|min:0.01',
            'remark' => 'nullable|string|max:100',
        ]);

        [$customer, $invoice] = $this->resolveCustomerAndInvoice(
            $request->firm_id,
            $request->invoice_id
        );

        $invoiceAmount = (float) $invoice->amount;

        $totalPaidOthers = Receipt::where('invoice_id', $invoice->id)
            ->where('id', '!=', $receipt->id)
            ->sum('given_amount');

        $newTotalPaid = $totalPaidOthers + $request->given_amount;

        if ($newTotalPaid > $invoiceAmount) {
            throw ValidationException::withMessages([
                'given_amount' => 'Payment exceeds invoice amount.',
            ]);
        }

        $receipt->update([
            'date' => $request->date,
            'receipt_no' => $request->receipt_no,
            'firm_id' => $customer->id,
            'invoice_id' => $invoice->id,
            'amount' => $invoiceAmount,
            'given_amount' => $request->given_amount,
            'final_amount' => $invoiceAmount,
            'sales_person' => optional($invoice->salesperson)->name,
            'mode' => $request->mode,
            'remark' => $request->remark,
        ]);

        $this->updateInvoiceStatus($invoice->id);

        return response()->json([
            'status' => true,
            'message' => 'Receipt updated successfully',
        ]);
    }

    public function delete($id)
    {
        $receipt = Receipt::findOrFail($id);
        $invoiceId = $receipt->invoice_id;

        $receipt->forceDelete();

        $this->updateInvoiceStatus($invoiceId);

        return response()->json([
            'status' => true,
            'message' => 'Receipt deleted successfully',
        ]);
    }

    private function resolveCustomerAndInvoice($firmId, $invoiceId)
    {
        $customer = Customer::findOrFail($firmId);

        $invoice = Invoice::with('salesperson:id,name')
            ->where('id', $invoiceId)
            ->where('firm_id', $firmId)
            ->first();

        if (!$invoice) {
            throw ValidationException::withMessages([
                'invoice_id' => 'Invoice does not belong to selected firm.',
            ]);
        }

        return [$customer, $invoice];
    }

    private function updateInvoiceStatus($invoiceId)
    {
        $invoice = Invoice::find($invoiceId);

        if (!$invoice) return;

        $invoiceAmount = (float) $invoice->amount;

        $paid = (float) Receipt::where('invoice_id', $invoice->id)
            ->sum('given_amount');

        $invoice->status = $paid >= $invoiceAmount
            ? 'full_paid'
            : 'pending';

        $invoice->save();
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


    public function getPendingInvoices($firm_id)
    {
        $invoices = Invoice::with('salesperson:id,name')
            ->withSum('receipts as paid_amount', 'given_amount')
            ->where('firm_id', $firm_id)
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


    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,accpet,rejected',
        ]);

        $receipt = Receipt::findOrFail($id);

        $receipt->status = $request->status;
        $receipt->manager_status = $request->status;
        $receipt->save();

        /*
        =============================
        CHECK INVOICE PAYMENT STATUS
        =============================
        */

        $invoice = Invoice::find($receipt->invoice_id);

        if ($invoice) {

            // Total paid from receipts
            $totalPaid = Receipt::where('invoice_id', $invoice->id)
                ->where('status', 'accpet') // only accepted payments
                ->sum('given_amount');

            $payableAmount = $invoice->payable_amount;

            if ($totalPaid >= $payableAmount) {

                $invoice->status = 'full_paid';

            } else {

                $invoice->status = 'pending';

            }

            $invoice->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'Receipt status updated successfully'
        ]);
    }
}
