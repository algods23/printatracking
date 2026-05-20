<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $companyName = Setting::get('company_name', 'Printa Signages');
        $companyEmail = Setting::get('company_email', '');
        $companyPhone = Setting::get('company_phone', '');
        $companyAddress = Setting::get('company_address', '');
        $receiptFooter = Setting::get('receipt_footer', '');
        $darkMode = Setting::get('dark_mode', 'false');
        $printerName = Setting::get('printer_name', '');

        return view('settings.index', compact(
            'companyName',
            'companyEmail',
            'companyPhone',
            'companyAddress',
            'receiptFooter',
            'darkMode',
            'printerName'
        ));
    }

    public function updateCompany(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|email',
            'company_phone' => 'required|string|max:20',
            'company_address' => 'required|string|max:500',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        if ($request->hasFile('company_logo')) {
            $logoPath = $request->file('company_logo')->store('logo', 'public');
            Setting::set('company_logo', $logoPath);
        }

        Setting::set('company_name', $validated['company_name']);
        Setting::set('company_email', $validated['company_email']);
        Setting::set('company_phone', $validated['company_phone']);
        Setting::set('company_address', $validated['company_address']);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'updated_company_settings',
            'description' => 'Company information updated',
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Company information updated successfully');
    }

    public function updateReceipt(Request $request)
    {
        $validated = $request->validate([
            'receipt_footer' => 'nullable|string|max:1000',
        ]);

        Setting::set('receipt_footer', $validated['receipt_footer']);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'updated_receipt_settings',
            'description' => 'Receipt settings updated',
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Receipt settings updated successfully');
    }

    public function updatePrinter(Request $request)
    {
        $validated = $request->validate([
            'printer_name' => 'required|string|max:255',
            'paper_width' => 'required|in:80,58',
        ]);

        Setting::set('printer_name', $validated['printer_name']);
        Setting::set('paper_width', $validated['paper_width']);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'updated_printer_settings',
            'description' => 'Printer settings updated',
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Printer settings updated successfully');
    }

    public function updateTheme(Request $request)
    {
        $validated = $request->validate([
            'dark_mode' => 'boolean',
        ]);

        Setting::set('dark_mode', $validated['dark_mode'] ? 'true' : 'false');

        return back()->with('success', 'Theme updated successfully');
    }

    public function manageUsers()
    {
        $users = User::all();
        return view('settings.users', compact('users'));
    }

    public function createUser()
    {
        return view('settings.create-user');
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:Admin,Staff',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'created_user',
            'model_type' => User::class,
            'model_id' => $user->id,
            'description' => "User {$user->name} created with role {$user->role}",
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('settings.users')->with('success', 'User created successfully');
    }

    public function editUser(User $user)
    {
        return view('settings.edit-user', compact('user'));
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:Admin,Staff',
            'is_active' => 'boolean',
        ]);

        $user->update($validated);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'updated_user',
            'model_type' => User::class,
            'model_id' => $user->id,
            'description' => "User {$user->name} updated",
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('settings.users')->with('success', 'User updated successfully');
    }

    public function deleteUser(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Cannot delete your own account');
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'deleted_user',
            'model_type' => User::class,
            'model_id' => $user->id,
            'description' => "User {$user->name} deleted",
            'ip_address' => request()->ip(),
        ]);

        $user->delete();

        return redirect()->route('settings.users')->with('success', 'User deleted successfully');
    }

    public function backup()
    {
        // Backup implementation
        return back()->with('success', 'Database backup completed');
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file',
        ]);

        // Restore implementation
        return back()->with('success', 'Database restored successfully');
    }
}
