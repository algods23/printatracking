@extends('layouts.app')

@section('title', 'Users')
@section('page-title', 'User Management')
@section('page-subtitle', 'Manage system accounts and access')

@section('content')
<div class="mb-4 flex items-center justify-between gap-3">
    <a href="{{ route('settings.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        Back to Settings
    </a>

    <a href="{{ route('settings.create-user') }}" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-black font-semibold text-sm rounded-lg transition-colors inline-flex items-center gap-2">
        <i data-lucide="user-plus" class="w-4 h-4"></i>
        New User
    </a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Users</h2>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Name</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Email</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Role</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Status</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $user->email }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="inline-block px-3 py-1 bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-full text-xs font-medium">{{ $user->role }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="inline-block px-3 py-1 {{ $user->is_active ? 'bg-green-500/10 text-green-600 dark:text-green-400' : 'bg-red-500/10 text-red-600 dark:text-red-400' }} rounded-full text-xs font-medium">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('settings.edit-user', $user) }}" class="p-2 text-yellow-600 dark:text-yellow-400 hover:bg-yellow-500/10 rounded transition-colors" title="Edit User">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('settings.delete-user', $user) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-red-600 dark:text-red-400 hover:bg-red-500/10 rounded transition-colors" title="Delete User">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            No users found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    lucide.createIcons();
</script>
@endsection
