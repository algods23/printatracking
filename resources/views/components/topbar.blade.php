<header class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 px-6 py-4">
    <div class="flex items-center justify-between">

        <!-- Page Title -->
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                @yield('page-title', 'Dashboard')
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                @yield('page-subtitle', 'Welcome to Printa Signages Management System')
            </p>
        </div>

        <div class="flex items-center gap-4">



            <!-- Notifications -->
            <div class="relative" x-data="{ open: false }">

                <!-- Bell -->
                <button
                    @click="open = !open"
                    class="relative p-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg"
                >
                    <i data-lucide="bell" class="w-5 h-5"></i>

                    @if(isset($topbarActivities) && count($topbarActivities) > 0)
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                    @endif
                </button>

                <!-- Dropdown -->
                <div
                    x-show="open"
                    x-transition
                    @click.away="open = false"
                    class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl z-50 overflow-hidden"
                    style="display:none;"
                >

                    <!-- Header -->
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                        <span class="font-semibold text-sm text-gray-900 dark:text-white">
                            Recent Activities
                        </span>
                    </div>

                    <!-- Activities -->
                    <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-96 overflow-y-auto">

                        @forelse($topbarActivities ?? [] as $activity)

                            <div class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors flex gap-3">

                                <!-- Icon -->
                                <div class="w-9 h-9 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center shrink-0">

                                    @if($activity->action == 'login')
                                        <i data-lucide="log-in" class="w-4 h-4 text-blue-500"></i>

                                    @elseif($activity->action == 'logout')
                                        <i data-lucide="log-out" class="w-4 h-4 text-gray-500"></i>

                                    @elseif($activity->action == 'created')
                                        <i data-lucide="plus-circle" class="w-4 h-4 text-green-500"></i>

                                    @elseif($activity->action == 'updated')
                                        <i data-lucide="edit" class="w-4 h-4 text-yellow-500"></i>

                                    @elseif($activity->action == 'deleted')
                                        <i data-lucide="trash-2" class="w-4 h-4 text-red-500"></i>

                                    @else
                                        <i data-lucide="activity" class="w-4 h-4 text-gray-500"></i>
                                    @endif

                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">

                                    <!-- Account Name -->
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                        {{ $activity->user->name }}
                                    </p>

                                    <!-- What they do -->
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        {{ $activity->description }}
                                    </p>

                                    <!-- Time -->
                                    <p class="text-[11px] text-gray-400 mt-1">
                                        {{ $activity->created_at->shortAbsoluteDiffForHumans() }}
                                    </p>

                                </div>

                            </div>

                        @empty

                            <div class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                No recent activity
                            </div>

                        @endforelse

                    </div>

            

                </div>
            </div>
        </div>
    </div>
</header>