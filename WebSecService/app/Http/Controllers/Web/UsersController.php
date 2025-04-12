<?php
namespace App\Http\Controllers\Web;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use DB;
use Artisan;

use App\Http\Controllers\Controller;
use App\Models\User;

class UsersController extends Controller {

	use ValidatesRequests;

    public function list(Request $request) {
        if(!auth()->user()->hasPermissionTo('show_users'))abort(401);
        
        $query = User::select('*');
        
        // If user is Employee, only show Customer role users
        if (auth()->user()->hasRole('Employee')) {
            $query->whereHas('roles', function($q) {
                $q->where('name', 'Customer');
            });
        }
        
        $query->when($request->keywords, 
        fn($q)=> $q->where("name", "like", "%$request->keywords%"));
        $users = $query->get();
        
        // Load roles for each user
        foreach ($users as $user) {
            $user->load('roles');
        }
        
        return view('users.list', compact('users'));
    }

	public function register(Request $request) {
        return view('users.register');
    }

// Added Now 
// Added Now 
    public function buy(Request $request, Product $product)
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        if (!$product->in_stock) {
            return redirect()->back()->with('error', 'This product is currently out of stock.');
        }

        // Mark product as out of stock after purchase
        $product->in_stock = false;
        $product->save();

        return redirect()->back()->with('success', 'Thank you for your purchase!');
    }
// Added Now 
// Added Now 

    private function ensureCustomerRoleExists()
    {
        // Check if Customer role exists
        $customerRole = Role::where('name', 'Customer')->first();
        
        // If not, create it
        if (!$customerRole) {
            $customerRole = Role::create([
                'name' => 'Customer',
                'guard_name' => 'web'
            ]);
            
            // Clear cache to ensure role is recognized
            Artisan::call('cache:clear');
        }
        
        return $customerRole;
    }

    public function doRegister(Request $request) {

    	try {
    		$this->validate($request, [
	        'name' => ['required', 'string', 'min:5'],
	        'email' => ['required', 'email', 'unique:users'],
	        'password' => ['required', 'confirmed', Password::min(8)->numbers()->letters()->mixedCase()->symbols()],
	    	]);
    	}
    	catch(\Exception $e) {
    		return redirect()->back()->withInput($request->input())->withErrors('Invalid registration information.');
    	}

        DB::beginTransaction();
        try {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password); //Secure
            $user->save();

            // Make sure Customer role exists
            $customerRole = Role::where('name', 'Customer')->first();
            if (!$customerRole) {
                $customerRole = Role::create([
                    'name' => 'Customer',
                    'guard_name' => 'web'
                ]);
            }
            
            // Make sure buy item permission exists
            $buyItemPermission = Permission::where('name', 'buy item')->first();
            if (!$buyItemPermission) {
                $buyItemPermission = Permission::create([
                    'name' => 'buy item',
                    'guard_name' => 'web',
                    'display_name' => 'Buy Item'
                ]);
            }
            
            // Assign the Customer role to the user
            $user->assignRole($customerRole);
            
            // Assign the buy item permission to the user
            $user->givePermissionTo($buyItemPermission);
            
            // Clear cache to ensure role and permission are recognized
            Artisan::call('cache:clear');
            
            DB::commit();
            return redirect('/');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Registration error: ' . $e->getMessage());
            return redirect()->back()->withInput($request->input())->withErrors('Error during registration. Please try again.');
        }
    }

    public function login(Request $request) {
        return view('users.login');
    }

    public function doLogin(Request $request) {
    	
    	if(!Auth::attempt(['email' => $request->email, 'password' => $request->password]))
            return redirect()->back()->withInput($request->input())->withErrors('Invalid login information.');

        $user = User::where('email', $request->email)->first();
        Auth::setUser($user);

        return redirect('/');
    }

    public function doLogout(Request $request) {
    	
    	Auth::logout();

        return redirect('/');
    }

    public function profile(Request $request, User $user = null) {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = $user??auth()->user();
        if(auth()->id()!=$user->id) {
            if(!auth()->user()->hasPermissionTo('show_users')) abort(401);
        }

        $permissions = [];
        foreach($user->permissions as $permission) {
            $permissions[] = $permission;
        }
        foreach($user->roles as $role) {
            foreach($role->permissions as $permission) {
                $permissions[] = $permission;
            }
        }

        // Load purchased products
        $purchasedProducts = DB::table('purchases')
            ->join('products', 'purchases.product_id', '=', 'products.id')
            ->where('purchases.user_id', $user->id)
            ->select('products.*', 'purchases.created_at as purchase_date')
            ->get();

        return view('users.profile', compact('user', 'permissions', 'purchasedProducts'));
    }

    public function edit(Request $request, User $user = null) {
   
        $user = $user??auth()->user();
        if(auth()->id()!=$user?->id) {
            if(!auth()->user()->hasPermissionTo('edit_users')) abort(401);
        }
    
        $roles = [];
        foreach(Role::all() as $role) {
            $role->taken = ($user->hasRole($role->name));
            $roles[] = $role;
        }

        $permissions = [];
        $directPermissionsIds = $user->permissions()->pluck('id')->toArray();
        foreach(Permission::all() as $permission) {
            $permission->taken = in_array($permission->id, $directPermissionsIds);
            $permissions[] = $permission;
        }      

        return view('users.edit', compact('user', 'roles', 'permissions'));
    }

    public function save(Request $request, User $user) {

        if(auth()->id()!=$user->id) {
            if(!auth()->user()->hasPermissionTo('show_users')) abort(401);
        }

        $user->name = $request->name;
        $user->save();

        if(auth()->user()->hasPermissionTo('admin_users')) {

            $user->syncRoles($request->roles);
            $user->syncPermissions($request->permissions);

            Artisan::call('cache:clear');
        }

        //$user->syncRoles([1]);
        //Artisan::call('cache:clear');

        return redirect(route('profile', ['user'=>$user->id]));
    }

    public function delete(Request $request, User $user) {

        if(!auth()->user()->hasPermissionTo('delete_users')) abort(401);

        $user->delete();

        return redirect()->route('users');
    }

    public function editPassword(Request $request, User $user = null) {

        $user = $user??auth()->user();
        if(auth()->id()!=$user?->id) {
            if(!auth()->user()->hasPermissionTo('edit_users')) abort(401);
        }

        return view('users.edit_password', compact('user'));
    }

    public function savePassword(Request $request, User $user) {

        if(auth()->id()==$user?->id) {
            
            $this->validate($request, [
                'password' => ['required', 'confirmed', Password::min(8)->numbers()->letters()->mixedCase()->symbols()],
            ]);

            if(!Auth::attempt(['email' => $user->email, 'password' => $request->old_password])) {
                
                Auth::logout();
                return redirect('/');
            }
        }
        else if(!auth()->user()->hasPermissionTo('edit_users')) {

            abort(401);
        }

        $user->password = bcrypt($request->password); //Secure
        $user->save();

        return redirect(route('profile', ['user'=>$user->id]));
    }
}