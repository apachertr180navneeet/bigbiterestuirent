<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Salesperson;
use App\Models\User;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;

class SalespersonController extends Controller
{
    /**
     * Sales Person index page
     *
     * @return void
     */
    public function index(){
        $users = Helper::isSuperAdmin()
            ? User::where('role', 'admin')->orderBy('full_name')->get(['id', 'full_name', 'email'])
            : collect();
        return view("admin.salesperson.index", compact('users'));
    }

    /**
     * ---------------------------------------------------------
     * Fetch All Salesperson Data (With Search + Pagination)
     * ---------------------------------------------------------
     */
    public function getall(Request $request)
    {
        $query = Salesperson::query()->with('user:id,full_name,email');

        Helper::applyUserScope($query, 'salespersons');

        /**
         * ---------------------------------------------------------
         * Global Search (Name, Code, Email Only)
         * ---------------------------------------------------------
         */
        if ($request->has('search') && !empty($request->search['value'])) {

            $search = $request->search['value'];

            $query->where(function ($q) use ($search) {
                $q->where('salesperson_code', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        /**
         * ---------------------------------------------------------
         * Total Records Count (Before Filtering)
         * ---------------------------------------------------------
         */
        $totalQuery = Salesperson::query();
        Helper::applyUserScope($totalQuery, 'salespersons');
        $totalRecords = $totalQuery->count();

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
        $salesperson = $query
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
            "data" => $salesperson,
        ]);
    }


    /**
     *  Sale Person add page
     * 
     * @return void 
     * 
     */

    public function create(){
       return view("admin.salesperson.create");
    }


    /**
     * Store a newly created Salesperson in database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $userId = Auth::id();
        /*--------------------------------------------------------------------
        | Step 1: Validate Incoming Request Data
        |--------------------------------------------------------------------
        | - Ensures required fields are present
        | - Validates format and length
        | - Prevents duplicate mobile and email entries
        | - Automatically returns 422 response if validation fails
        --------------------------------------------------------------------*/
        $request->validate([
            'name' => 'required|min:3|max:50',
            'salesperson_code' => 'required|unique:salespersons,salesperson_code',
            'mobile' => 'required|digits_between:10,15|unique:salespersons,mobile',
            'email' => 'nullable|email|unique:salespersons,email',
            'password' => 'required|min:6',
        ]);


        /*--------------------------------------------------------------------
        | Step 2: Create New Salesperson Record
        |--------------------------------------------------------------------
        | - Store validated data into database
        | - Password is encrypted using bcrypt for security
        | - Optional fields are stored if provided
        --------------------------------------------------------------------*/
        $salesperson = Salesperson::create([
            'name'              => $request->name,
            'mobile'            => $request->mobile,
            'email'             => $request->email,
            'password'          => bcrypt($request->password), // Secure password hashing
            'address'           => $request->address,
            'dob'               => $request->dob,
            'alternative_phone' => $request->alternative_phone,
            'salesperson_code'  => $request->salesperson_code,
            'user_id'           => $userId
        ]);


        /*--------------------------------------------------------------------
        | Step 3: Return JSON Response (For AJAX Request)
        |--------------------------------------------------------------------
        | - status: Boolean response indicator
        | - message: Success message for toastr notification
        --------------------------------------------------------------------*/
        return response()->json([
            'status'  => true,
            'message' => 'Salesperson added successfully',
            'data'    => $salesperson // Optional: return created record
        ]);
    }

    /**
     * Delete Salesperson
     */
    public function delete($id)
    {
        $salesperson = Salesperson::findOrFail($id);
        $salesperson->delete();

        return response()->json([
            'status' => true,
            'message' => 'Salesperson deleted successfully'
        ]);
    }


    /**
     * Change Salesperson Status
     */
    public function changeStatus(Request $request, $id)
    {
        $salesperson = Salesperson::findOrFail($id);

        $salesperson->status = $request->status;
        $salesperson->save();

        return response()->json([
            'status' => true,
            'message' => 'Status updated successfully'
        ]);
    }


    /**
     * ---------------------------------------------------------
     * Show Edit Salesperson Page
     * ---------------------------------------------------------
     * 1. Get salesperson by ID
     * 2. Pass data to create view (reuse form)
     * ---------------------------------------------------------
     */
    public function edit($id)
    {
        // Fetch salesperson record or fail if not found
        $salesperson = Salesperson::findOrFail($id);

        // Pass data to view
        return view("admin.salesperson.edit", compact('salesperson'));
    }


    /**
     * ---------------------------------------------------------
     * Update Salesperson
     * ---------------------------------------------------------
     * 1. Validate request
     * 2. Find salesperson
     * 3. Update data
     * 4. Handle optional password update
     * 5. Return JSON response
     * ---------------------------------------------------------
     */
    public function update(Request $request, $id)
    {
        // Find existing record
        $salesperson = Salesperson::findOrFail($id);

        /**
         * -----------------------------------------------------
         * Validation Rules
         * -----------------------------------------------------
         * Unique email & mobile should ignore current record
         */
        $request->validate([
            'salesperson_code' => 'required',
            'name'             => 'required|min:3|max:50',
            'mobile'           => 'required|digits_between:10,15|unique:salespersons,mobile,' . $id,
            'email'            => 'nullable|email|unique:salespersons,email,' . $id,
            'password'         => 'nullable|min:6',
            'address'          => 'nullable',
            'dob'              => 'nullable|date',
            'alternative_phone'=> 'nullable|digits_between:10,15',
        ]);

        /**
         * -----------------------------------------------------
         * Prepare Data for Update
         * -----------------------------------------------------
         */
        $data = $request->except('password');

        // Update password only if provided
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        /**
         * -----------------------------------------------------
         * Update Record
         * -----------------------------------------------------
         */
        $salesperson->update($data);

        /**
         * -----------------------------------------------------
         * Return Response
         * -----------------------------------------------------
         */
        return response()->json([
            'status'  => true,
            'message' => 'Salesperson updated successfully.'
        ]);
    }



}
