<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Clock In/Out') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Clock & Date Card -->
            <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl shadow-xl p-8 text-center text-white">
                <p class="text-indigo-200 text-sm font-medium uppercase tracking-widest mb-1">{{ now('Asia/Kuala_Lumpur')->format('l') }}</p>
                <p class="text-indigo-100 text-lg mb-4">{{ now('Asia/Kuala_Lumpur')->format('F j, Y') }}</p>
                <div id="current-time" class="text-6xl font-mono font-bold tracking-tight"></div>
                <div id="status-badge" class="mt-5 inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold bg-white/20 backdrop-blur-sm">
                    <span id="status-dot" class="w-2.5 h-2.5 rounded-full bg-gray-300 animate-pulse"></span>
                    <span id="status-text">Loading...</span>
                </div>
            </div>

            <!-- WFH Toggle -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Work From Home</p>
                        <p class="text-xs text-gray-400">Toggle if you're working remotely today</p>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="wfh-toggle" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-2 gap-4">
                <!-- Clock In -->
                <button id="clock-in-btn" class="group relative overflow-hidden bg-white border-2 border-emerald-200 hover:border-emerald-400 rounded-2xl p-6 shadow-sm hover:shadow-lg transition-all duration-300 text-center">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-14 h-14 rounded-full bg-emerald-100 group-hover:bg-emerald-200 flex items-center justify-center transition-colors duration-300">
                            <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                        </div>
                        <span class="text-lg font-bold text-gray-800">Clock In</span>
                        <span class="text-xs text-gray-400">Start your workday</span>
                    </div>
                </button>

                <!-- Clock Out -->
                <button id="clock-out-btn" class="group relative overflow-hidden bg-white border-2 border-red-200 hover:border-red-400 rounded-2xl p-6 shadow-sm hover:shadow-lg transition-all duration-300 text-center">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-14 h-14 rounded-full bg-red-100 group-hover:bg-red-200 flex items-center justify-center transition-colors duration-300">
                            <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </div>
                        <span class="text-lg font-bold text-gray-800">Clock Out</span>
                        <span class="text-xs text-gray-400">End your workday</span>
                    </div>
                </button>

                <!-- Start Break -->
                <button id="break-in-btn" class="group relative overflow-hidden bg-white border-2 border-amber-200 hover:border-amber-400 rounded-2xl p-6 shadow-sm hover:shadow-lg transition-all duration-300 text-center">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-14 h-14 rounded-full bg-amber-100 group-hover:bg-amber-200 flex items-center justify-center transition-colors duration-300">
                            <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-lg font-bold text-gray-800">Start Break</span>
                        <span class="text-xs text-gray-400">Take a pause</span>
                    </div>
                </button>

                <!-- End Break -->
                <button id="break-out-btn" class="group relative overflow-hidden bg-white border-2 border-orange-200 hover:border-orange-400 rounded-2xl p-6 shadow-sm hover:shadow-lg transition-all duration-300 text-center">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-14 h-14 rounded-full bg-orange-100 group-hover:bg-orange-200 flex items-center justify-center transition-colors duration-300">
                            <svg class="w-7 h-7 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-lg font-bold text-gray-800">End Break</span>
                        <span class="text-xs text-gray-400">Resume work</span>
                    </div>
                </button>
            </div>

            <!-- Status Messages -->
            <div id="status-message" class="transition-all duration-300"></div>

            <!-- Today's Activity Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-base font-semibold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Today's Activity
                    </h3>
                </div>
                <div id="today-status" class="p-6">
                    <div class="flex items-center justify-center py-4">
                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-500"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        let currentBreakId = null;

        function formatWorkHours(hours) {
            if (!hours) return 'N/A';
            const totalMinutes = Math.round(parseFloat(hours) * 60);
            const h = Math.floor(totalMinutes / 60);
            const m = totalMinutes % 60;
            return h + 'h ' + m + 'm';
        }

        function formatTime(dateString) {
            return new Date(dateString).toLocaleTimeString('en-US', {
                timeZone: 'Asia/Kuala_Lumpur',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });
        }

        function updateClock() {
            const now = new Date();
            const options = {
                timeZone: 'Asia/Kuala_Lumpur',
                hour12: true,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', options);
        }

        function getLocation(successCallback, errorCallback = null) {
            if (navigator.geolocation) {
                showStatus('Getting your location...', 'info');
                navigator.geolocation.getCurrentPosition(
                    position => {
                        hideStatus();
                        successCallback(position.coords.latitude, position.coords.longitude);
                    },
                    error => {
                        hideStatus();
                        if (errorCallback) {
                            errorCallback(error);
                        } else {
                            showStatus('Location unavailable, but you can still clock in.', 'info');
                            successCallback(null, null);
                        }
                    },
                    { timeout: 10000, enableHighAccuracy: true }
                );
            } else {
                if (errorCallback) {
                    errorCallback(new Error('Geolocation is not supported by this browser'));
                } else {
                    showStatus('Geolocation is not supported, but you can still clock in.', 'info');
                    successCallback(null, null);
                }
            }
        }

        function updateTodayStatus(data) {
            const container = document.getElementById('today-status');
            const statusDot = document.getElementById('status-dot');
            const statusText = document.getElementById('status-text');

            if (data.attendance) {
                let html = '';

                // Timeline-style layout
                html += '<div class="space-y-4">';

                // Clock In Row
                html += '<div class="flex items-start gap-4">';
                html += '<div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center ' + (data.attendance.clock_in ? 'bg-emerald-100' : 'bg-gray-100') + '">';
                html += '<svg class="w-5 h-5 ' + (data.attendance.clock_in ? 'text-emerald-600' : 'text-gray-400') + '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>';
                html += '</div>';
                html += '<div class="flex-1 min-w-0">';
                html += '<p class="text-sm font-medium text-gray-500">Clock In</p>';
                if (data.attendance.clock_in) {
                    html += '<p class="text-lg font-semibold text-gray-900">' + formatTime(data.attendance.clock_in) + '</p>';
                    if (data.attendance.clock_in_address) {
                        html += '<p class="text-xs text-gray-400 truncate">' + data.attendance.clock_in_address + '</p>';
                    }
                } else {
                    html += '<p class="text-sm text-gray-400">Not yet</p>';
                }
                html += '</div></div>';

                // Clock Out Row
                html += '<div class="flex items-start gap-4">';
                html += '<div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center ' + (data.attendance.clock_out ? 'bg-red-100' : 'bg-gray-100') + '">';
                html += '<svg class="w-5 h-5 ' + (data.attendance.clock_out ? 'text-red-600' : 'text-gray-400') + '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>';
                html += '</div>';
                html += '<div class="flex-1 min-w-0">';
                html += '<p class="text-sm font-medium text-gray-500">Clock Out</p>';
                if (data.attendance.clock_out) {
                    html += '<p class="text-lg font-semibold text-gray-900">' + formatTime(data.attendance.clock_out) + '</p>';
                    if (data.attendance.clock_out_address) {
                        html += '<p class="text-xs text-gray-400 truncate">' + data.attendance.clock_out_address + '</p>';
                    }
                } else {
                    html += '<p class="text-sm text-gray-400">Not yet</p>';
                }
                html += '</div></div>';

                // Total Hours Row (only if clocked out)
                if (data.attendance.clock_out) {
                    html += '<div class="flex items-start gap-4">';
                    html += '<div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center bg-indigo-100">';
                    html += '<svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                    html += '</div>';
                    html += '<div class="flex-1 min-w-0">';
                    html += '<p class="text-sm font-medium text-gray-500">Total Hours</p>';
                    html += '<p class="text-lg font-semibold text-indigo-600">' + formatWorkHours(data.attendance.total_work_hours) + '</p>';
                    html += '</div></div>';
                }

                // Breaks
                if (data.breaks && data.breaks.length > 0) {
                    html += '<div class="border-t border-gray-100 pt-4 mt-4">';
                    html += '<p class="text-sm font-medium text-gray-500 mb-3 flex items-center gap-2">';
                    html += '<svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                    html += 'Breaks</p>';
                    html += '<div class="space-y-2">';
                    data.breaks.forEach((breakItem, index) => {
                        const isActive = !breakItem.break_out;
                        html += '<div class="flex items-center justify-between px-3 py-2 rounded-lg ' + (isActive ? 'bg-amber-50 border border-amber-200' : 'bg-gray-50') + '">';
                        html += '<div class="flex items-center gap-2">';
                        html += '<span class="text-xs font-medium text-gray-400">#' + (index + 1) + '</span>';
                        html += '<span class="text-sm text-gray-700">' + formatTime(breakItem.break_in);
                        if (breakItem.break_out) {
                            html += ' - ' + formatTime(breakItem.break_out);
                        }
                        html += '</span></div>';
                        if (isActive) {
                            html += '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>Active</span>';
                        } else if (breakItem.duration_minutes) {
                            html += '<span class="text-xs text-gray-500">' + Math.round(breakItem.duration_minutes) + ' min</span>';
                        }
                        html += '</div>';
                    });
                    html += '</div></div>';
                }

                html += '</div>';
                container.innerHTML = html;

                // Update status badge
                updateStatusBadge(data, statusDot, statusText);
                updateButtonStates(data);
            } else {
                container.innerHTML = '<div class="text-center py-8"><svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><p class="text-gray-400 text-sm">No activity recorded today</p><p class="text-gray-300 text-xs mt-1">Click "Clock In" to start</p></div>';
                statusDot.className = 'w-2.5 h-2.5 rounded-full bg-gray-300';
                statusText.textContent = 'Ready to clock in';
            }
        }

        function updateStatusBadge(data, dot, text) {
            if (data.attendance) {
                let wfhLabel = data.attendance.is_wfh ? ' (WFH)' : '';
                if (data.attendance.clock_out) {
                    dot.className = 'w-2.5 h-2.5 rounded-full bg-gray-300';
                    let statusLabel = 'Day complete' + wfhLabel;
                    if (data.attendance.is_late) statusLabel += ' - Late';
                    if (data.attendance.is_early_leave) statusLabel += ' - Early Leave';
                    text.textContent = statusLabel;
                } else if (data.attendance.clock_in) {
                    let onBreak = false;
                    if (data.breaks) {
                        data.breaks.forEach(b => { if (!b.break_out) onBreak = true; });
                    }
                    if (onBreak) {
                        dot.className = 'w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse';
                        text.textContent = 'On break' + wfhLabel;
                    } else {
                        dot.className = 'w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse';
                        let label = 'Clocked In - Working' + wfhLabel;
                        if (data.attendance.is_late) label += ' (Late)';
                        text.textContent = label;
                    }
                }
            }
        }

        function updateButtonStates(data) {
            const clockInBtn = document.getElementById('clock-in-btn');
            const clockOutBtn = document.getElementById('clock-out-btn');
            const breakInBtn = document.getElementById('break-in-btn');
            const breakOutBtn = document.getElementById('break-out-btn');

            // Reset all buttons
            [clockInBtn, clockOutBtn, breakInBtn, breakOutBtn].forEach(btn => {
                btn.disabled = false;
                btn.classList.remove('opacity-40', 'cursor-not-allowed', 'pointer-events-none');
            });

            if (data.attendance) {
                if (data.attendance.clock_in && !data.attendance.clock_out) {
                    disableBtn(clockInBtn);
                }
                if (data.attendance.clock_out) {
                    [clockInBtn, clockOutBtn, breakInBtn, breakOutBtn].forEach(disableBtn);
                }
                if (data.breaks) {
                    data.breaks.forEach(breakItem => {
                        if (!breakItem.break_out) {
                            currentBreakId = breakItem.id;
                            disableBtn(breakInBtn);
                        }
                    });
                }
            } else {
                [clockOutBtn, breakInBtn, breakOutBtn].forEach(disableBtn);
            }
        }

        function disableBtn(btn) {
            btn.disabled = true;
            btn.classList.add('opacity-40', 'cursor-not-allowed', 'pointer-events-none');
        }

        function showStatus(message, type = 'info') {
            const container = document.getElementById('status-message');
            const styles = {
                success: 'bg-emerald-50 border-emerald-200 text-emerald-700',
                error: 'bg-red-50 border-red-200 text-red-700',
                info: 'bg-blue-50 border-blue-200 text-blue-700'
            };
            const icons = {
                success: '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                error: '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                info: '<svg class="w-5 h-5 flex-shrink-0 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>'
            };
            container.innerHTML = '<div class="flex items-center gap-3 px-4 py-3 rounded-xl border ' + styles[type] + '">' + icons[type] + '<span class="text-sm font-medium">' + message + '</span></div>';
        }

        function hideStatus() {
            document.getElementById('status-message').innerHTML = '';
        }

        function clockIn() {
            getLocation((lat, lng) => {
                showStatus('Clocking in...', 'info');
                fetch('{{ route("clock.clock-in") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ lat, lng, is_wfh: document.getElementById('wfh-toggle').checked })
                })
                .then(response => response.json())
                .then(data => {
                    hideStatus();
                    if (data.success) {
                        showStatus('Successfully clocked in!', 'success');
                        loadTodayStatus();
                        setTimeout(() => hideStatus(), 3000);
                    } else {
                        showStatus('Error: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    hideStatus();
                    showStatus('Network error. Please try again.', 'error');
                });
            });
        }

        function clockOut() {
            getLocation((lat, lng) => {
                showStatus('Clocking out...', 'info');
                fetch('{{ route("clock.clock-out") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ lat, lng })
                })
                .then(response => response.json())
                .then(data => {
                    hideStatus();
                    if (data.success) {
                        showStatus('Successfully clocked out!', 'success');
                        loadTodayStatus();
                        setTimeout(() => hideStatus(), 3000);
                    } else {
                        showStatus('Error: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    hideStatus();
                    showStatus('Network error. Please try again.', 'error');
                });
            });
        }

        function breakIn() {
            getLocation((lat, lng) => {
                showStatus('Starting break...', 'info');
                fetch('{{ route("clock.break-in") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ lat, lng })
                })
                .then(response => response.json())
                .then(data => {
                    hideStatus();
                    if (data.success) {
                        currentBreakId = data.break.id;
                        showStatus('Break started!', 'success');
                        loadTodayStatus();
                        setTimeout(() => hideStatus(), 3000);
                    } else {
                        showStatus('Error: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    hideStatus();
                    showStatus('Network error. Please try again.', 'error');
                });
            });
        }

        function breakOut() {
            if (!currentBreakId) {
                showStatus('No active break found.', 'error');
                return;
            }
            getLocation((lat, lng) => {
                showStatus('Ending break...', 'info');
                fetch('{{ route("clock.break-out") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ lat, lng, break_id: currentBreakId })
                })
                .then(response => response.json())
                .then(data => {
                    hideStatus();
                    if (data.success) {
                        showStatus('Break ended!', 'success');
                        loadTodayStatus();
                        setTimeout(() => hideStatus(), 3000);
                    } else {
                        showStatus('Error: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    hideStatus();
                    showStatus('Network error. Please try again.', 'error');
                });
            });
        }

        function loadTodayStatus() {
            fetch('{{ route("attendance.today") }}')
                .then(response => response.json())
                .then(data => updateTodayStatus(data))
                .catch(error => console.error('Error loading status:', error));
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateClock();
            setInterval(updateClock, 1000);
            loadTodayStatus();

            document.getElementById('clock-in-btn').addEventListener('click', clockIn);
            document.getElementById('clock-out-btn').addEventListener('click', clockOut);
            document.getElementById('break-in-btn').addEventListener('click', breakIn);
            document.getElementById('break-out-btn').addEventListener('click', breakOut);
        });
    </script>
</x-app-layout>
