<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    // Ensure that the employee role can perform these actions
    public function __construct()
    {
        $this->middleware('role:Employee'); // Only allow employees to access these actions
    }

    // Display a list of all customers
    public function index()
    {
        // Authorize that the user has permission to view customers
        $this->authorize('viewAny', User::class);

        // Get all customers (or use a more refined query to get specific customers)
        $customers = User::where('role', 'Customer')->get();

        return view('customers.index', compact('customers')); // Make sure you have this view
    }

    // Show a form for adding credit to a customer account
    public function showAddCreditForm($customerId)
    {
        $customer = User::findOrFail($customerId);
        return view('customers.add_credit', compact('customer')); // Make sure you have this view
    }

    // Add credit to a customer's account
    public function addCredit(Request $request, $customerId)
    {
        $customer = User::findOrFail($customerId);

        // Validate the credit amount
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        // Add the credit to the customer's account
        $customer->credit += $request->amount;
        $customer->save();

        return redirect()->route('customers.index')->with('success', 'Credit added successfully.');
    }

    // Show customer details (optional)
    public function show($customerId)
    {
        $customer = User::findOrFail($customerId);

        return view('customers.show', compact('customer')); // You can create a detailed customer view here
    }

    // Add a new customer (admin or employee functionality)
    public function create()
    {
        return view('customers.create'); // Create a form to add new customers
    }

    // Store a newly created customer
    public function store(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create a new customer
        $customer = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'Customer', // Set the role as 'Customer'
            'credit' => 0, // Default credit balance is 0
        ]);

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    // Update the details of an existing customer (Admin/Employee only)
    public function edit($customerId)
    {
        $customer = User::findOrFail($customerId);

        return view('customers.edit', compact('customer')); // Create a form to edit customer details
    }

    // Update the customer's information
    public function update(Request $request, $customerId)
    {
        $customer = User::findOrFail($customerId);

        // Validate the updated customer data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $customer->id,
        ]);

        // Update customer details
        $customer->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->route('customers.index')->with('success', 'Customer details updated successfully.');
    }

    // Delete a customer (Admin/Employee only)
    public function destroy($customerId)
    {
        $customer = User::findOrFail($customerId);

        // Ensure this is only an admin/employee action
        $this->authorize('delete', $customer);

        // Delete the customer
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }
}
