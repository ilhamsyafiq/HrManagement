<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                        <x-application-logo class="block h-9 w-auto fill-current text-indigo-600" />
                        <span class="hidden lg:block text-lg font-bold text-gray-800 dark:text-gray-200">{{ config('app.name', 'HR Management') }}</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-4 sm:-my-px sm:ml-10 sm:flex items-center">
                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                        {{-- Admin Navigation --}}
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>

                        {{-- People Management Dropdown --}}
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                            <button @click="open = !open" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.attendances') || request()->routeIs('admin.leaves') || request()->routeIs('leave.approvals') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                {{ __('People') }}
                                <svg class="ml-1 h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            </button>
                            <div x-show="open" x-transition class="absolute z-50 mt-2 w-48 rounded-xl bg-white dark:bg-gray-700 shadow-lg border border-gray-100 dark:border-gray-600 py-1" style="display: none;">
                                <a href="{{ route('admin.users') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">{{ __('Users') }}</a>
                                <a href="{{ route('admin.attendances') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">{{ __('Attendances') }}</a>
                                <a href="{{ route('admin.leaves') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">{{ __('Leaves') }}</a>
                                <a href="{{ route('leave.approvals') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">{{ __('Approvals') }}</a>
                            </div>
                        </div>

                        {{-- Organization Dropdown --}}
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                            <button @click="open = !open" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none {{ request()->routeIs('claims.*') || request()->routeIs('announcements.*') || request()->routeIs('calendar.*') || request()->routeIs('holidays.*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                {{ __('Organization') }}
                                <svg class="ml-1 h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            </button>
                            <div x-show="open" x-transition class="absolute z-50 mt-2 w-48 rounded-xl bg-white dark:bg-gray-700 shadow-lg border border-gray-100 dark:border-gray-600 py-1" style="display: none;">
                                <a href="{{ route('claims.index') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">{{ __('Claims') }}</a>
                                <a href="{{ route('announcements.index') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">{{ __('Announcements') }}</a>
                                <a href="{{ route('calendar.index') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">{{ __('Calendar & Holidays') }}</a>
                            </div>
                        </div>

                        {{-- Messages --}}
                        <x-nav-link :href="route('messages.index')" :active="request()->routeIs('messages.*')">
                            {{ __('Messages') }}
                            @php $unreadCount = auth()->user()->receivedMessages()->where('is_read', false)->count(); @endphp
                            @if($unreadCount > 0)
                                <span class="ml-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-500 rounded-full">{{ $unreadCount }}</span>
                            @endif
                        </x-nav-link>

                    @else
                        {{-- Employee/Intern Navigation --}}
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>

                        {{-- Work Dropdown --}}
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                            <button @click="open = !open" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none {{ request()->routeIs('clock.*') || request()->routeIs('attendance.*') || request()->routeIs('leave.*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                {{ __('Work') }}
                                <svg class="ml-1 h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            </button>
                            <div x-show="open" x-transition class="absolute z-50 mt-2 w-48 rounded-xl bg-white dark:bg-gray-700 shadow-lg border border-gray-100 dark:border-gray-600 py-1" style="display: none;">
                                <a href="{{ route('clock.index') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">{{ __('Clock In/Out') }}</a>
                                <a href="{{ route('attendance.index') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">{{ __('Attendance') }}</a>
                                <a href="{{ route('leave.index') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">{{ __('Leave') }}</a>
                                <a href="{{ route('claims.index') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">{{ __('Claims') }}</a>
                            </div>
                        </div>

                        {{-- Info Dropdown --}}
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                            <button @click="open = !open" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none {{ request()->routeIs('calendar.*') || request()->routeIs('announcements.*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                {{ __('Info') }}
                                <svg class="ml-1 h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            </button>
                            <div x-show="open" x-transition class="absolute z-50 mt-2 w-48 rounded-xl bg-white dark:bg-gray-700 shadow-lg border border-gray-100 dark:border-gray-600 py-1" style="display: none;">
                                <a href="{{ route('calendar.index') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">{{ __('Calendar & Holidays') }}</a>
                                <a href="{{ route('announcements.index') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">{{ __('News') }}</a>
                            </div>
                        </div>

                        {{-- Messages --}}
                        <x-nav-link :href="route('messages.index')" :active="request()->routeIs('messages.*')">
                            {{ __('Messages') }}
                            @php $unreadCount = auth()->user()->receivedMessages()->where('is_read', false)->count(); @endphp
                            @if($unreadCount > 0)
                                <span class="ml-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-500 rounded-full">{{ $unreadCount }}</span>
                            @endif
                        </x-nav-link>

                        @if(auth()->user()->isSupervisor())
                            {{-- Supervisor extras --}}
                            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                <button @click="open = !open" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none {{ request()->routeIs('leave.approvals') || request()->routeIs('supervisor.*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                    {{ __('Manage') }}
                                    <svg class="ml-1 h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                </button>
                                <div x-show="open" x-transition class="absolute z-50 mt-2 w-48 rounded-xl bg-white dark:bg-gray-700 shadow-lg border border-gray-100 dark:border-gray-600 py-1" style="display: none;">
                                    <a href="{{ route('leave.approvals') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">{{ __('Approvals') }}</a>
                                    <a href="{{ route('supervisor.show') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">{{ __('My Team') }}</a>
                                </div>
                            </div>
                        @endif

                        @if(auth()->user()->isIntern())
                            <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                                {{ __('Reports') }}
                            </x-nav-link>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Theme Toggle & Settings -->
            <div class="hidden sm:flex sm:items-center sm:ml-6 sm:space-x-3">
                <!-- Dark Mode Toggle -->
                <button @click="$store.darkMode.toggle()" class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none transition" title="Toggle theme">
                    <!-- Sun icon (light mode) -->
                    <svg x-show="$store.darkMode.mode === 'light'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <!-- Moon icon (dark mode) -->
                    <svg x-show="$store.darkMode.mode === 'dark'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <!-- Computer icon (system mode) -->
                    <svg x-show="$store.darkMode.mode === 'system'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </button>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ml-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('employee-profile.show')">
                            {{ __('My Profile') }}
                        </x-dropdown-link>
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Account Settings') }}
                        </x-dropdown-link>
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                            <div class="border-t border-gray-100 dark:border-gray-600 my-1"></div>
                            <x-dropdown-link :href="route('admin.working-hours')">
                                {{ __('Working Hours') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.office-locations')">
                                {{ __('Geofencing') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.audit-logs')">
                                {{ __('Audit Logs') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.reports')">
                                {{ __('Reports') }}
                            </x-dropdown-link>
                        @endif
                        <div class="border-t border-gray-100 dark:border-gray-600 my-1"></div>
                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="$store.darkMode.toggle()" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none transition duration-150 ease-in-out sm:hidden">
                    <svg x-show="$store.darkMode.mode === 'light'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <svg x-show="$store.darkMode.mode === 'dark'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg x-show="$store.darkMode.mode === 'system'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </button>
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">{{ __('Dashboard') }}</x-responsive-nav-link>

                <div class="px-4 pt-3 pb-1"><p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('People') }}</p></div>
                <x-responsive-nav-link :href="route('admin.users')" :active="request()->routeIs('admin.users.*')">{{ __('Users') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.attendances')" :active="request()->routeIs('admin.attendances')">{{ __('Attendances') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.leaves')" :active="request()->routeIs('admin.leaves')">{{ __('Leaves') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('leave.approvals')" :active="request()->routeIs('leave.approvals')">{{ __('Approvals') }}</x-responsive-nav-link>

                <div class="px-4 pt-3 pb-1"><p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('Organization') }}</p></div>
                <x-responsive-nav-link :href="route('claims.index')" :active="request()->routeIs('claims.*')">{{ __('Claims') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('announcements.index')" :active="request()->routeIs('announcements.*')">{{ __('Announcements') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('calendar.index')" :active="request()->routeIs('calendar.*')">{{ __('Calendar & Holidays') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('messages.index')" :active="request()->routeIs('messages.*')">{{ __('Messages') }}</x-responsive-nav-link>

                <div class="px-4 pt-3 pb-1"><p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('Settings') }}</p></div>
                <x-responsive-nav-link :href="route('admin.working-hours')" :active="request()->routeIs('admin.working-hours')">{{ __('Working Hours') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.office-locations')" :active="request()->routeIs('admin.office-locations')">{{ __('Geofencing') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.audit-logs')" :active="request()->routeIs('admin.audit-logs')">{{ __('Audit Logs') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.reports')" :active="request()->routeIs('admin.reports')">{{ __('Reports') }}</x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-responsive-nav-link>

                <div class="px-4 pt-3 pb-1"><p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('Work') }}</p></div>
                <x-responsive-nav-link :href="route('clock.index')" :active="request()->routeIs('clock.*')">{{ __('Clock In/Out') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('attendance.index')" :active="request()->routeIs('attendance.*')">{{ __('Attendance') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('leave.index')" :active="request()->routeIs('leave.*')">{{ __('Leave') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('claims.index')" :active="request()->routeIs('claims.*')">{{ __('Claims') }}</x-responsive-nav-link>

                <div class="px-4 pt-3 pb-1"><p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('Info') }}</p></div>
                <x-responsive-nav-link :href="route('calendar.index')" :active="request()->routeIs('calendar.*')">{{ __('Calendar & Holidays') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('announcements.index')" :active="request()->routeIs('announcements.*')">{{ __('News') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('messages.index')" :active="request()->routeIs('messages.*')">{{ __('Messages') }}</x-responsive-nav-link>

                @if(auth()->user()->isSupervisor())
                    <div class="px-4 pt-3 pb-1"><p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('Manage') }}</p></div>
                    <x-responsive-nav-link :href="route('leave.approvals')" :active="request()->routeIs('leave.approvals')">{{ __('Approvals') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('supervisor.show')" :active="request()->routeIs('supervisor.*')">{{ __('My Team') }}</x-responsive-nav-link>
                @endif
                @if(auth()->user()->isIntern())
                    <x-responsive-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">{{ __('Reports') }}</x-responsive-nav-link>
                @endif
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('employee-profile.show')">{{ __('My Profile') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('profile.edit')">{{ __('Account Settings') }}</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
