<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\Salesperson;
use App\Models\User;
use App\Helpers\Helper;
use App\Exports\ReceiptExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ReceiptController extends Controller
{
    public function index()
    {
        $salespersons = Salesperson::query()->where('status', 'active');
        Helper::applyUserScope($salespersons, 'salespersons');
        $salespersons = $salespersons->orderBy('name')->get(['id', 'name']);

        $customers = Customer::query()->where('status', 'active');
        Helper::applyUserScope($customers, 'customers');
        $customers = $customers->orderBy('firm_name')->get(['id', 'firm_name']);

        $users = Helper::isSuperAdmin()
            ? User::where('role', 'admin')->orderBy('full_name')->get(['id', 'full_name', 'email'])
            : collect();

        return view('admin.receipt.index', compact('salespersons', 'customers', 'users'));
    }

    public function getall(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:100',
            'receipt_no' => 'nullable|string|max:100',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'mode' => 'nullable|in:cash,upi,bank,card',
            'manager_status' => 'nullable|in:pending,accpet,rejected',
            'status' => 'nullable|in:pending,accpet,rejected',
            'discount_type' => 'nullable|in:cd,disc',
            'salesperson_id' => 'nullable|exists:salespersons,id',
            'firm_id' => 'nullable|exists:customers,id',
        ]);

        $query = Receipt::query()->with([
            'firm:id,firm_name',
            'invoice:id,invoice_no',
            'user:id,full_name,email',
        ]);

        Helper::applyUserScope($query, 'receipts');

        if (!empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search) {
                $q->where('receipt_no', 'like', '%' . $search . '%')
                  ->orWhere('amount', 'like', '%' . $search . '%')
                  ->orWhere('given_amount', 'like', '%' . $search . '%')
                  ->orWhereHas('invoice', function ($inv) use ($search) {
                      $inv->where('invoice_no', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('firm', function ($f) use ($search) {
                      $f->where('firm_name', 'like', '%' . $search . '%');
                  });
            });
        }

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

        if (!empty($validated['discount_type'])) {
            $query->where('discount_type', $validated['discount_type']);
        }

        if (!empty($validated['firm_id'])) {
            $query->where('firm_id', $validated['firm_id']);
        }

        if (!empty($validated['salesperson_id'])) {
            $query->whereHas('invoice', function ($invoiceQuery) use ($validated) {
                $invoiceQuery->where('salesperson_id', $validated['salesperson_id']);
            });
        }

        $totalQuery = Receipt::query();
        Helper::applyUserScope($totalQuery, 'receipts');
        $totalRecords = $totalQuery->count();
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

    public function exportExcel(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:100',
            'receipt_no' => 'nullable|string|max:100',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'mode' => 'nullable|in:cash,upi,bank,card',
            'status' => 'nullable|in:pending,accpet,rejected',
            'discount_type' => 'nullable|in:cd,disc',
            'salesperson_id' => 'nullable|exists:salespersons,id',
            'firm_id' => 'nullable|exists:customers,id',
        ]);

        $query = Receipt::query()->with([
            'firm:id,firm_name',
            'invoice:id,invoice_no',
        ]);

        Helper::applyUserScope($query, 'receipts');

        if (!empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search) {
                $q->where('receipt_no', 'like', '%' . $search . '%')
                  ->orWhere('amount', 'like', '%' . $search . '%')
                  ->orWhere('given_amount', 'like', '%' . $search . '%')
                  ->orWhereHas('invoice', function ($inv) use ($search) {
                      $inv->where('invoice_no', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('firm', function ($f) use ($search) {
                      $f->where('firm_name', 'like', '%' . $search . '%');
                  });
            });
        }

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

        if (!empty($validated['discount_type'])) {
            $query->where('discount_type', $validated['discount_type']);
        }

        if (!empty($validated['firm_id'])) {
            $query->where('firm_id', $validated['firm_id']);
        }

        if (!empty($validated['salesperson_id'])) {
            $query->whereHas('invoice', function ($invoiceQuery) use ($validated) {
                $invoiceQuery->where('salesperson_id', $validated['salesperson_id']);
            });
        }

        $receipts = $query->orderBy('id', 'desc')->get();

        $receipts = $receipts->map(function ($item) {
            $item->firm_name = optional($item->firm)->firm_name;
            $item->invoice_no = optional($item->invoice)->invoice_no;
            $item->date = $item->date
                ? \Carbon\Carbon::parse($item->date)->format('d/m/Y')
                : null;
            return $item;
        });

        return Excel::download(new ReceiptExport($receipts), 'receipts.xlsx');
    }

    public function create()
    {
        $customers = Customer::where('status', 'active');
        Helper::applyUserScope($customers, 'customers');
        $customers = $customers->orderBy('firm_name')->get(['id', 'firm_name']);

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
        $receipt = Receipt::findOrFail($id);

        $customers = Customer::where('status', 'active');
        Helper::applyUserScope($customers, 'customers');
        $customers = $customers->orderBy('firm_name')->get(['id', 'firm_name']);

        $invoices = Invoice::with('salesperson:id,name');
        Helper::applyUserScope($invoices, 'invoices');
        $invoices = $invoices->withSum('receipts as paid_amount', 'given_amount')
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


    public function updateDiscountType(Request $request, $id)
    {
        $request->validate([
            'discount_type' => 'nullable|in:cd,disc',
            'discount' => 'nullable|numeric|min:0',
        ]);

        $receipt = Receipt::findOrFail($id);
        $receipt->discount_type = $request->discount_type;
        if ($request->has('discount')) {
            $receipt->discount = $request->discount;
        }
        $receipt->save();

        return response()->json([
            'status' => true,
            'message' => 'Discount updated successfully',
        ]);
    }

    public function changeStatus(Request $request, $id)
    {
        if (!Helper::isSuperAdmin()) {
            return response()->json([
                'status' => false,
                'message' => 'Only super admin can approve/reject receipts'
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,accpet,rejected',
            'approval_remark' => 'nullable|string|max:500',
        ]);

        $receipt = Receipt::findOrFail($id);

        $receipt->status = $request->status;
        $receipt->manager_status = $request->status;
        $receipt->approval_remark = $request->approval_remark;
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
