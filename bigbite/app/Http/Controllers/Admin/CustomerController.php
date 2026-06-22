<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    /**
     * customer index page
     *
     * @return void
     */
    public function index(){
       return view("admin.customer.index");
    }

    /**
     * ---------------------------------------------------------
     * Fetch All Customer Data (With Search + Pagination)
     * ---------------------------------------------------------
     */
    public function getall(Request $request)
    {
        $userId = Auth::id();

        $query = Customer::query();


        $query->where('user_id',$userId);


        /**
         * ---------------------------------------------------------
         * Global Search (Name, Code, Email Only)
         * ---------------------------------------------------------
         */
        if ($request->has('search') && !empty($request->search['value'])) {

            $search = $request->search['value'];

            $query->where(function ($q) use ($search) {
                $q->Where('firm_name', 'like', "%{$search}%");
            });
        }

        /**
         * ---------------------------------------------------------
         * Total Records Count (Before Filtering)
         * ---------------------------------------------------------
         */
        $totalRecords = Customer::count();

        /**
         * ---------------------------------------------------------
         * Filtered Records Count
         * ---------------------------------------------------------
         */
        $filteredRecords = $query->count();

        /**
         * ---------------------------------------------------------
         * Pagination
         * ---------------------------------------------------------
         */
        $customerdata = $query
            ->orderBy('id', 'desc')
            ->skip($request->start)
            ->take($request->length)
            ->get();

        /**
         * ---------------------------------------------------------
         * Return JSON for DataTable
         * ---------------------------------------------------------
         */
        return response()->json([
            "draw" => intval($request->draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $filteredRecords,
            "data" => $customerdata,
        ]);
    }


    /**
     *  Customer add page
     * 
     * @return void 
     * 
     */

    public function create(){
       return view("admin.customer.create");
    }


    /**
     * Store a newly created Customer in database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $userId = Auth::id();
        $request->validate([
            'firm_name' => 'required|string|max:100|unique:customers,firm_name',
            'phone' => 'required|digits_between:10,15|unique:customers,phone',
        ]);

        $customer = Customer::create([
            'firm_name' => $request->firm_name,
            'phone' => $request->phone,
            'user_id' => $userId
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Customer added successfully',
            'data'    => $customer
        ]);
    }

    /**
     * Delete customer
     */
    public function delete($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return response()->json([
            'status' => true,
            'message' => 'Customer deleted successfully'
        ]);
    }

    /**
     * Change customer status
     */
    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $customer = Customer::findOrFail($id);
        $customer->status = $request->status;
        $customer->save();

        return response()->json([
            'status' => true,
            'message' => 'Customer status updated successfully'
        ]);
    }

    /**
     * Show edit customer page
     */
    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        return view("admin.customer.edit", compact('customer'));
    }

    /**
     * Update customer
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $request->validate([
            'firm_name' => 'required|string|max:100',
            'phone' => 'required|digits_between:10,15|unique:customers,phone,' . $id,
        ]);

        $customer->update([
            'firm_name' => $request->firm_name,
            'name' => $request->name,
            'phone' => $request->phone,
            'gst_no' => $request->gst_no,
            'discount' => $request->discount ?? 0,
            'status' => $request->status ?? $customer->status,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Customer updated successfully'
        ]);
    }
}
