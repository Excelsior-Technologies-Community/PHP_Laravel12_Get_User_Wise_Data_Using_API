<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Customer Data Explorer & REST API Playground | Laravel 12</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            500: '#0284c7',
                            600: '#0369a1',
                            700: '#075985',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        pre, code, .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
        .custom-scroll::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scroll::-webkit-scrollbar-track {
            background: #0f172a;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }
        .custom-scroll::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }
        .method-get { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .method-post { background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); }
        .method-put { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .method-delete { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }
    </style>
</head>
<body class="h-full bg-slate-950 text-slate-100 flex flex-col overflow-hidden">

    <!-- Top Navigation -->
    <header class="h-16 bg-slate-900 border-b border-slate-800 px-4 sm:px-6 flex items-center justify-between z-30 shrink-0">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-sky-500/20">
                <i class="fa-solid fa-database text-lg"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-base sm:text-lg font-bold text-white tracking-tight">Customer Data API</h1>
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-sky-500/20 text-sky-300 border border-sky-500/30">Laravel 12</span>
                </div>
                <p class="text-xs text-slate-400 hidden sm:block">Customer-Wise Data Explorer, Visual Analytics & REST API Playground</p>
            </div>
        </div>

        <!-- Center / Right Controls -->
        <div class="flex items-center space-x-3">
            <!-- Customer Switcher -->
            <div class="flex items-center gap-2 bg-slate-800/90 border border-slate-700 rounded-xl px-3 py-1.5 shadow-sm">
                <i class="fa-solid fa-user-tag text-sky-400 text-xs"></i>
                <span class="text-xs text-slate-400 font-medium hidden md:inline">Active Customer:</span>
                <select id="global-customer-select" onchange="switchCustomer(this.value)" class="bg-transparent text-xs font-bold text-sky-300 focus:outline-none cursor-pointer">
                    <option value="1" class="bg-slate-900 text-slate-100">Customer #1: John Doe (Acme Corp)</option>
                    <option value="2" class="bg-slate-900 text-slate-100">Customer #2: Sarah Connor (Cyberdyne)</option>
                    <option value="3" class="bg-slate-900 text-slate-100">Customer #3: Alex Mercer (Gentek Labs)</option>
                </select>
            </div>

            <!-- View Mode Switcher -->
            <div class="flex bg-slate-800 p-1 rounded-xl border border-slate-700">
                <button onclick="switchViewMode('dashboard-view', this)" class="nav-tab active px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-sky-600 transition-all flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span class="hidden sm:inline">Analytics & Notes</span>
                </button>
                <button onclick="switchViewMode('playground-view', this)" class="nav-tab px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-400 hover:text-white transition-all flex items-center gap-1.5">
                    <i class="fa-solid fa-bolt"></i>
                    <span class="hidden sm:inline">API Playground</span>
                </button>
            </div>

            <!-- Create Note Button -->
            <button onclick="openCreateNoteModal()" class="px-3.5 py-1.5 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-sky-500/20 flex items-center gap-1.5 transition-all">
                <i class="fa-solid fa-plus text-xs"></i>
                <span class="hidden sm:inline">New Note</span>
            </button>
        </div>
    </header>

    <!-- Main Container -->
    <div class="flex-1 flex overflow-hidden relative">

        <!-- ==================== VIEW 1: CUSTOMER DASHBOARD & ANALYTICS ==================== -->
        <div id="dashboard-view" class="view-panel flex-1 flex flex-col overflow-y-auto custom-scroll p-4 sm:p-6 space-y-6">

            <!-- Customer Hero Banner & Metrics -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Customer Notes</span>
                        <div class="w-8 h-8 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-note-sticky"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-white mt-2" id="stat-total-notes">0</div>
                    <div class="text-xs text-slate-400 mt-1 flex items-center gap-1">
                        <span class="text-emerald-400 font-semibold" id="stat-has-notes-badge">● Active Record</span>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">This Month</span>
                        <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-calendar-days"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-white mt-2" id="stat-month-notes">0</div>
                    <div class="text-xs text-slate-400 mt-1">Current billing / activity cycle</div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Today's Notes</span>
                        <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-white mt-2" id="stat-today-notes">0</div>
                    <div class="text-xs text-slate-400 mt-1">Added today</div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">High Priority</span>
                        <div class="w-8 h-8 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-rose-400 mt-2" id="stat-high-notes">0</div>
                    <div class="text-xs text-slate-400 mt-1">Requiring immediate action</div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Monthly Activity Chart (2 Cols) -->
                <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                                <i class="fa-solid fa-chart-column text-sky-400"></i> Monthly Notes Activity
                            </h3>
                            <p class="text-xs text-slate-400">API Endpoint: <code class="text-sky-300 font-mono">/api/notes/monthly-activity</code></p>
                        </div>
                        <button onclick="loadCustomerMonthlyChart()" class="p-1.5 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 text-xs">
                            <i class="fa-solid fa-arrows-rotate"></i>
                        </button>
                    </div>
                    <div class="h-64 relative">
                        <canvas id="monthlyActivityChart"></canvas>
                    </div>
                </div>

                <!-- Category & Priority Doughnut Chart (1 Col) -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col">
                    <div class="mb-3">
                        <h3 class="text-sm font-bold text-white flex items-center gap-2">
                            <i class="fa-solid fa-chart-pie text-indigo-400"></i> Category Distribution
                        </h3>
                        <p class="text-xs text-slate-400">Notes by Category & Urgency</p>
                    </div>
                    <div class="flex-1 relative flex items-center justify-center min-h-[200px]">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Customer Notes Explorer & Filter Controls -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-slate-800">
                    <div>
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <i class="fa-solid fa-list-check text-sky-400"></i> Customer Notes Records
                        </h3>
                        <p class="text-xs text-slate-400" id="notes-sub-count">Showing notes for active customer</p>
                    </div>

                    <!-- Search & Filter Actions -->
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                            <input
                                type="text"
                                id="notes-search-input"
                                placeholder="Search title or description..."
                                class="pl-8 pr-3 py-1.5 bg-slate-800 text-slate-100 placeholder-slate-500 text-xs rounded-xl border border-slate-700 focus:border-sky-500 focus:outline-none w-48 sm:w-60"
                                oninput="debounceFilterNotes()"
                            >
                        </div>

                        <select id="filter-priority" onchange="loadCustomerNotes()" class="bg-slate-800 text-slate-200 text-xs px-2.5 py-1.5 rounded-xl border border-slate-700 focus:outline-none">
                            <option value="">All Priorities</option>
                            <option value="high">🔴 High Priority</option>
                            <option value="medium">🟡 Medium</option>
                            <option value="low">🟢 Low</option>
                        </select>

                        <select id="filter-quick-date" onchange="applyQuickDate(this.value)" class="bg-slate-800 text-slate-200 text-xs px-2.5 py-1.5 rounded-xl border border-slate-700 focus:outline-none">
                            <option value="">All Time</option>
                            <option value="today">📅 Today</option>
                            <option value="this_month">📆 This Month</option>
                        </select>

                        <select id="filter-sort-by" onchange="loadCustomerNotes()" class="bg-slate-800 text-slate-200 text-xs px-2.5 py-1.5 rounded-xl border border-slate-700 focus:outline-none">
                            <option value="created_at-desc">Newest First</option>
                            <option value="created_at-asc">Oldest First</option>
                            <option value="title-asc">Title (A-Z)</option>
                            <option value="id-desc">Note ID (High-Low)</option>
                        </select>
                    </div>
                </div>

                <!-- Notes Cards Container -->
                <div id="notes-cards-container" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 min-h-[160px]">
                    <!-- Rendered via JS -->
                </div>

                <!-- Pagination -->
                <div id="notes-pagination" class="flex items-center justify-between pt-3 border-t border-slate-800 text-xs text-slate-400">
                    <!-- Rendered via JS -->
                </div>
            </div>
        </div>

        <!-- ==================== VIEW 2: INTERACTIVE REST API PLAYGROUND ==================== -->
        <div id="playground-view" class="view-panel flex-1 hidden flex flex-col md:flex-row overflow-hidden bg-slate-950">

            <!-- Left: Endpoints Navigator (340px) -->
            <div class="w-full md:w-80 bg-slate-900 border-r border-slate-800 flex flex-col shrink-0">
                <div class="p-3.5 border-b border-slate-800 bg-slate-900/80">
                    <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-network-wired text-sky-400"></i> API Endpoints (16)
                    </h3>
                </div>

                <div class="flex-1 overflow-y-auto p-2 space-y-1 custom-scroll" id="endpoints-list">
                    <!-- Populated by JS -->
                </div>
            </div>

            <!-- Right: Interactive Request & Response Workspace -->
            <div class="flex-1 flex flex-col overflow-hidden bg-slate-950">

                <!-- Endpoint URL & Action Bar -->
                <div class="p-4 bg-slate-900/90 border-b border-slate-800 flex flex-wrap items-center gap-3">
                    <span id="selected-method-badge" class="px-2.5 py-1 rounded-lg text-xs font-black method-get">GET</span>
                    <div class="flex-1 min-w-[240px] bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-mono text-slate-200 flex items-center justify-between">
                        <span id="selected-endpoint-url" class="truncate">/api/notes?customer_id=1</span>
                        <button onclick="copyCurrentUrl()" title="Copy Full URL" class="text-slate-400 hover:text-white ml-2">
                            <i class="fa-solid fa-copy"></i>
                        </button>
                    </div>
                    <button onclick="executeApiRequest()" id="btn-send-request" class="px-4 py-2 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-sky-500/20 flex items-center gap-2 transition-all">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Send Request</span>
                    </button>
                </div>

                <!-- Parameters & Body / Response Split View -->
                <div class="flex-1 grid grid-cols-1 lg:grid-cols-2 overflow-hidden divide-y lg:divide-y-0 lg:divide-x divide-slate-800">

                    <!-- Left Column: Request Config & Params -->
                    <div class="flex flex-col overflow-y-auto p-4 space-y-4 custom-scroll">
                        <div>
                            <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-2 flex items-center gap-2">
                                <i class="fa-solid fa-sliders text-sky-400"></i> Query & Path Parameters
                            </h4>
                            <div id="request-params-container" class="space-y-2.5 text-xs">
                                <!-- Populated dynamically -->
                            </div>
                        </div>

                        <div id="request-body-section" class="hidden">
                            <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-2 flex items-center gap-2">
                                <i class="fa-solid fa-code text-indigo-400"></i> Request JSON Body
                            </h4>
                            <textarea id="request-body-json" rows="8" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-xs font-mono text-sky-300 focus:border-sky-500 focus:outline-none"></textarea>
                        </div>

                        <!-- Code Snippet Generator -->
                        <div class="pt-3 border-t border-slate-800/80">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Generate Code Snippet</span>
                                <div class="flex gap-1 text-[11px]">
                                    <button onclick="generateSnippet('curl')" class="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300">cURL</button>
                                    <button onclick="generateSnippet('js')" class="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300">JS Fetch</button>
                                    <button onclick="generateSnippet('php')" class="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300">PHP Guzzle</button>
                                </div>
                            </div>
                            <pre id="code-snippet-box" class="bg-slate-900 border border-slate-800 rounded-xl p-3 text-[11px] font-mono text-slate-300 overflow-x-auto custom-scroll"></pre>
                        </div>
                    </div>

                    <!-- Right Column: Live Response & Latency -->
                    <div class="flex flex-col overflow-hidden bg-slate-900/40">
                        <div class="px-4 py-2.5 bg-slate-900 border-b border-slate-800 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-300">Response:</span>
                                <span id="response-status-badge" class="px-2 py-0.5 rounded font-bold text-[11px] bg-slate-800 text-slate-400">Ready</span>
                            </div>
                            <div class="flex items-center gap-3 text-slate-400">
                                <span>Time: <strong id="response-time" class="text-slate-200">0 ms</strong></span>
                                <span>Size: <strong id="response-size" class="text-slate-200">0 KB</strong></span>
                                <button onclick="copyResponseJson()" title="Copy JSON" class="hover:text-white">
                                    <i class="fa-solid fa-copy"></i>
                                </button>
                            </div>
                        </div>

                        <div class="flex-1 overflow-auto p-4 custom-scroll bg-slate-950/80">
                            <pre id="response-json-viewer" class="text-xs font-mono text-emerald-400 whitespace-pre-wrap leading-relaxed">// Click "Send Request" to test this endpoint live.</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: CREATE / EDIT NOTE -->
    <div id="note-modal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between bg-slate-800/40">
                <h3 class="text-base font-bold text-white flex items-center gap-2" id="modal-title">
                    <i class="fa-solid fa-pen-to-square text-sky-400"></i> <span>Create Customer Note</span>
                </h3>
                <button onclick="closeNoteModal()" class="text-slate-400 hover:text-white text-lg">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="note-modal-form" onsubmit="handleSaveNote(event)" class="p-6 space-y-4 text-xs">
                <input type="hidden" id="modal-note-id">

                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Target Customer</label>
                    <select id="modal-customer-id" class="w-full bg-slate-800 text-slate-100 p-2.5 rounded-xl border border-slate-700 focus:border-sky-500 focus:outline-none">
                        <option value="1">Customer #1: John Doe (Acme Corp)</option>
                        <option value="2">Customer #2: Sarah Connor (Cyberdyne)</option>
                        <option value="3">Customer #3: Alex Mercer (Gentek Labs)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Note Title *</label>
                    <input type="text" id="modal-note-title" required placeholder="e.g. Q3 Roadmap Review" class="w-full bg-slate-800 text-slate-100 p-2.5 rounded-xl border border-slate-700 focus:border-sky-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Description / Content</label>
                    <textarea id="modal-note-desc" rows="4" placeholder="Enter detailed note content..." class="w-full bg-slate-800 text-slate-100 p-2.5 rounded-xl border border-slate-700 focus:border-sky-500 focus:outline-none"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-semibold mb-1">Priority Level</label>
                        <select id="modal-note-priority" class="w-full bg-slate-800 text-slate-100 p-2.5 rounded-xl border border-slate-700 focus:border-sky-500 focus:outline-none">
                            <option value="high">🔴 High</option>
                            <option value="medium" selected>🟡 Medium</option>
                            <option value="low">🟢 Low</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 font-semibold mb-1">Category / Tag</label>
                        <select id="modal-note-category" class="w-full bg-slate-800 text-slate-100 p-2.5 rounded-xl border border-slate-700 focus:border-sky-500 focus:outline-none">
                            <option value="Work">Work</option>
                            <option value="Ideas">Ideas</option>
                            <option value="Urgent">Urgent</option>
                            <option value="General" selected>General</option>
                        </select>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                    <button type="button" onclick="closeNoteModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-xl transition-all">Cancel</button>
                    <button type="submit" id="modal-submit-btn" class="px-5 py-2 bg-gradient-to-r from-sky-500 to-indigo-600 text-white font-bold rounded-xl shadow-lg shadow-sky-500/20 transition-all">Save Note</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT APP LOGIC -->
    <script>
        // State
        let activeCustomerId = 1;
        let monthlyChartInstance = null;
        let categoryChartInstance = null;
        let debounceTimer = null;
        let currentNotesPage = 1;

        // API Playground Endpoints Registry
        const apiEndpoints = [
            { id: 'list_notes', method: 'GET', url: '/api/notes', label: 'List Notes (Paginated & Filtered)', params: { customer_id: '1', search: '', from_date: '', to_date: '', priority: '', sort_by: 'created_at', sort_direction: 'desc', per_page: '5' } },
            { id: 'summary', method: 'GET', url: '/api/notes/summary', label: 'Customer Summary & Counts', params: { customer_id: '1' } },
            { id: 'statistics', method: 'GET', url: '/api/notes/statistics', label: 'Customer Statistics Overview', params: { customer_id: '1' } },
            { id: 'monthly_act', method: 'GET', url: '/api/notes/monthly-activity', label: 'Monthly Notes Activity Breakdown', params: { customer_id: '1' } },
            { id: 'yearly_act', method: 'GET', url: '/api/notes/yearly-activity', label: 'Yearly Activity Breakdown', params: { customer_id: '1' } },
            { id: 'today_notes', method: 'GET', url: '/api/notes/today', label: 'Today\'s Notes', params: { customer_id: '1' } },
            { id: 'month_notes', method: 'GET', url: '/api/notes/this-month', label: 'This Month\'s Notes', params: { customer_id: '1' } },
            { id: 'latest_notes', method: 'GET', url: '/api/notes/latest', label: 'Latest 5 Notes', params: { customer_id: '1' } },
            { id: 'title_search', method: 'GET', url: '/api/notes/title-search', label: 'Exact/Partial Title Search', params: { customer_id: '1', title: 'Roadmap' } },
            { id: 'date_range', method: 'GET', url: '/api/notes/date-range', label: 'Date Range Filtering', params: { customer_id: '1', from_date: '2026-01-01', to_date: '2026-12-31' } },
            { id: 'has_notes', method: 'GET', url: '/api/notes/has-notes', label: 'Has Notes Check (Boolean)', params: { customer_id: '1' } },
            { id: 'get_note', method: 'GET', url: '/api/notes/{noteId}', label: 'Get Single Note Details', params: { noteId: '1', customer_id: '1' } },
            { id: 'create_note', method: 'POST', url: '/api/notes', label: 'Create New Customer Note', body: { created_by: 1, title: 'Sprint Retrospective', description: 'Action items for next sprint release.', priority: 'high', category: 'Work' } },
            { id: 'update_note', method: 'PUT', url: '/api/notes/{noteId}', label: 'Update Customer Note', params: { noteId: '1' }, body: { title: 'Updated Strategic Review', description: 'Refined target metrics and OKRs.', priority: 'medium', category: 'Work' } },
            { id: 'delete_note', method: 'DELETE', url: '/api/notes/{noteId}', label: 'Delete Customer Note', params: { noteId: '1' } },
            { id: 'all_customers', method: 'GET', url: '/api/customers', label: 'List All Customers & Counts', params: {} },
            { id: 'customer_counts', method: 'GET', url: '/api/customers/note-counts', label: 'Customer Note Count Map', params: {} },
        ];

        let selectedEndpoint = apiEndpoints[0];

        // ------------------ SWITCH VIEW MODE ------------------
        function switchViewMode(viewId, btn) {
            document.querySelectorAll('.view-panel').forEach(p => p.classList.add('hidden'));
            document.querySelectorAll('.nav-tab').forEach(b => {
                b.classList.remove('bg-sky-600', 'text-white');
                b.classList.add('text-slate-400');
            });

            document.getElementById(viewId).classList.remove('hidden');
            btn.classList.remove('text-slate-400');
            btn.classList.add('bg-sky-600', 'text-white');

            if (viewId === 'playground-view') {
                renderEndpointsSidebar();
                selectEndpoint(selectedEndpoint.id);
            }
        }

        // ------------------ LOAD CUSTOMERS LIST ------------------
        async function loadCustomersList() {
            try {
                const res = await fetch('/api/customers');
                const data = await res.json();
                if (data.status && Array.isArray(data.data) && data.data.length > 0) {
                    const select1 = document.getElementById('global-customer-select');
                    const select2 = document.getElementById('modal-customer-id');
                    const optionsHtml = data.data.map(c => `
                        <option value="${c.id}" class="bg-slate-900 text-slate-100" ${c.id === activeCustomerId ? 'selected' : ''}>
                            Customer #${c.id}: ${c.name} (${c.total_notes} notes)
                        </option>
                    `).join('');
                    if (select1) select1.innerHTML = optionsHtml;
                    if (select2) select2.innerHTML = optionsHtml;
                }
            } catch (e) {
                console.error('Load customers error:', e);
            }
        }

        // ------------------ SWITCH CUSTOMER ------------------
        function switchCustomer(custId) {
            activeCustomerId = parseInt(custId);
            document.getElementById('global-customer-select').value = activeCustomerId;
            loadDashboardData();
        }

        // ------------------ LOAD DASHBOARD DATA ------------------
        async function loadDashboardData() {
            loadCustomerStats();
            loadCustomerMonthlyChart();
            loadCustomerNotes();
        }

        async function loadCustomerStats() {
            try {
                const res = await fetch(`/api/notes/statistics?customer_id=${activeCustomerId}`);
                const data = await res.json();
                if (data.status && data.data) {
                    const stats = data.data;
                    document.getElementById('stat-total-notes').innerText = stats.total_notes || 0;
                    document.getElementById('stat-month-notes').innerText = stats.this_month_notes || 0;
                    document.getElementById('stat-today-notes').innerText = stats.today_notes || 0;
                }

                // Check has notes
                const hasRes = await fetch(`/api/notes/has-notes?customer_id=${activeCustomerId}`);
                const hasData = await hasRes.json();
                const badge = document.getElementById('stat-has-notes-badge');
                if (hasData.has_notes) {
                    badge.innerText = '● Active Notes';
                    badge.className = 'text-emerald-400 font-semibold';
                } else {
                    badge.innerText = '○ No Notes Yet';
                    badge.className = 'text-slate-500 font-semibold';
                }
            } catch (e) {
                console.error('Stats error:', e);
            }
        }

        async function loadCustomerMonthlyChart() {
            try {
                const res = await fetch(`/api/notes/monthly-activity?customer_id=${activeCustomerId}`);
                const data = await res.json();
                const monthlyData = data.data || [];

                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                const counts = new Array(12).fill(0);

                monthlyData.forEach(item => {
                    const mIdx = (item.month || 1) - 1;
                    counts[mIdx] = item.total_notes;
                });

                const ctx = document.getElementById('monthlyActivityChart').getContext('2d');
                if (monthlyChartInstance) monthlyChartInstance.destroy();

                monthlyChartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: months,
                        datasets: [{
                            label: 'Notes Created',
                            data: counts,
                            backgroundColor: 'rgba(56, 189, 248, 0.7)',
                            borderColor: '#38bdf8',
                            borderWidth: 1.5,
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8', stepSize: 1 } },
                            x: { grid: { display: false }, ticks: { color: '#94a3b8' } }
                        }
                    }
                });
            } catch (e) {
                console.error('Monthly chart error:', e);
            }
        }

        async function loadCustomerNotes(page = 1) {
            currentNotesPage = page;
            const container = document.getElementById('notes-cards-container');
            container.innerHTML = `<div class="col-span-full py-10 flex flex-col items-center justify-center text-slate-500 gap-2"><i class="fa-solid fa-spinner fa-spin text-xl text-sky-400"></i><span>Loading customer notes...</span></div>`;

            const search = document.getElementById('notes-search-input').value.trim();
            const priority = document.getElementById('filter-priority').value;
            const sortVal = document.getElementById('filter-sort-by').value.split('-');
            const quickDate = document.getElementById('filter-quick-date').value;

            let url = `/api/notes?customer_id=${activeCustomerId}&page=${page}&per_page=6&sort_by=${sortVal[0]}&sort_direction=${sortVal[1]}`;
            if (search) url += `&search=${encodeURIComponent(search)}`;
            if (priority) url += `&priority=${priority}`;
            if (quickDate === 'today') url += `&today=1`;
            if (quickDate === 'this_month') url += `&this_month=1`;

            try {
                const res = await fetch(url);
                const data = await res.json();
                const notes = Array.isArray(data.data) ? data.data : (data.data?.data || []);
                const meta = data.pagination || (Array.isArray(data.data) ? { current_page: 1, last_page: 1, total: notes.length } : (data.data || {}));

                document.getElementById('notes-sub-count').innerText = `Total ${meta.total ?? notes.length} notes found`;

                if (!notes || notes.length === 0) {
                    container.innerHTML = `
                        <div class="col-span-full py-12 text-center text-slate-500">
                            <i class="fa-regular fa-folder-open text-3xl mb-2 text-slate-600"></i>
                            <p class="text-sm font-semibold text-slate-300">No notes found for Customer #${activeCustomerId}</p>
                            <p class="text-xs text-slate-500 mt-1">Click "New Note" above to add the first note.</p>
                        </div>
                    `;
                    renderPagination(meta);
                    return;
                }

                // Render cards
                container.innerHTML = notes.map(note => {
                    const p = note.priority || 'medium';
                    const pClass = p === 'high' ? 'bg-rose-500/15 text-rose-300 border-rose-500/30' : (p === 'medium' ? 'bg-amber-500/15 text-amber-300 border-amber-500/30' : 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30');
                    const cat = note.category || 'General';
                    const dateStr = new Date(note.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

                    return `
                        <div class="bg-slate-800/80 border border-slate-700/80 hover:border-sky-500/50 rounded-xl p-4 transition-all shadow-sm flex flex-col justify-between group">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase border ${pClass}">
                                        ${p}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-md bg-slate-900/80 text-slate-400 text-[11px] font-medium border border-slate-700/60">
                                        📁 ${cat}
                                    </span>
                                </div>
                                <h4 class="text-sm font-bold text-slate-100 group-hover:text-sky-300 transition-colors line-clamp-1">${escapeHtml(note.title)}</h4>
                                <p class="text-xs text-slate-400 mt-1.5 line-clamp-3 leading-relaxed">${escapeHtml(note.description || 'No additional description provided.')}</p>
                            </div>

                            <div class="mt-4 pt-3 border-t border-slate-700/60 flex items-center justify-between text-xs text-slate-500">
                                <span class="text-[11px]"><i class="fa-regular fa-clock mr-1"></i>${dateStr}</span>
                                <div class="flex items-center gap-1">
                                    <button onclick="openEditNoteModal(${JSON.stringify(note).replace(/"/g, '&quot;')})" class="p-1.5 text-slate-400 hover:text-sky-300 rounded hover:bg-slate-700" title="Edit Note">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button onclick="deleteNote(${note.id})" class="p-1.5 text-slate-400 hover:text-rose-400 rounded hover:bg-slate-700" title="Delete Note">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');

                renderPagination(meta);
                renderCategoryChart(notes);

                // Update High Priority Stat
                const highCount = notes.filter(n => n.priority === 'high').length;
                document.getElementById('stat-high-notes').innerText = highCount;
            } catch (e) {
                console.error('Fetch notes error:', e);
            }
        }

        function renderPagination(meta) {
            const el = document.getElementById('notes-pagination');
            if (!meta.last_page || meta.last_page <= 1) {
                el.innerHTML = `<span>Page 1 of 1</span>`;
                return;
            }

            el.innerHTML = `
                <span>Showing page ${meta.current_page} of ${meta.last_page} (${meta.total} records)</span>
                <div class="flex gap-1">
                    <button ${meta.current_page <= 1 ? 'disabled' : ''} onclick="loadCustomerNotes(${meta.current_page - 1})" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 disabled:opacity-40 disabled:cursor-not-allowed">Previous</button>
                    <button ${meta.current_page >= meta.last_page ? 'disabled' : ''} onclick="loadCustomerNotes(${meta.current_page + 1})" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 disabled:opacity-40 disabled:cursor-not-allowed">Next</button>
                </div>
            `;
        }

        function renderCategoryChart(notes) {
            const catCounts = {};
            notes.forEach(n => {
                const c = n.category || 'General';
                catCounts[c] = (catCounts[c] || 0) + 1;
            });

            const labels = Object.keys(catCounts);
            const data = Object.values(catCounts);

            const ctx = document.getElementById('categoryChart').getContext('2d');
            if (categoryChartInstance) categoryChartInstance.destroy();

            categoryChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels.length ? labels : ['General'],
                    datasets: [{
                        data: data.length ? data : [1],
                        backgroundColor: ['#38bdf8', '#818cf8', '#f43f5e', '#fbbf24', '#34d399'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { color: '#94a3b8', font: { size: 10 } } }
                    },
                    cutout: '65%'
                }
            });
        }

        function debounceFilterNotes() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => loadCustomerNotes(1), 250);
        }

        function applyQuickDate(val) {
            loadCustomerNotes(1);
        }

        function escapeHtml(str) {
            return (str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        // ------------------ CRUD MODAL ACTIONS ------------------
        function openCreateNoteModal() {
            document.getElementById('modal-title').innerHTML = `<i class="fa-solid fa-plus text-sky-400"></i> <span>Create Customer Note</span>`;
            document.getElementById('modal-note-id').value = '';
            document.getElementById('modal-customer-id').value = activeCustomerId;
            document.getElementById('modal-note-title').value = '';
            document.getElementById('modal-note-desc').value = '';
            document.getElementById('modal-note-priority').value = 'medium';
            document.getElementById('modal-note-category').value = 'General';
            document.getElementById('note-modal').classList.remove('hidden');
        }

        function openEditNoteModal(note) {
            document.getElementById('modal-title').innerHTML = `<i class="fa-solid fa-pen-to-square text-sky-400"></i> <span>Edit Note #${note.id}</span>`;
            document.getElementById('modal-note-id').value = note.id;
            document.getElementById('modal-customer-id').value = note.created_by;
            document.getElementById('modal-note-title').value = note.title;
            document.getElementById('modal-note-desc').value = note.description || '';
            document.getElementById('modal-note-priority').value = note.priority || 'medium';
            document.getElementById('modal-note-category').value = note.category || 'General';
            document.getElementById('note-modal').classList.remove('hidden');
        }

        function closeNoteModal() {
            document.getElementById('note-modal').classList.add('hidden');
        }

        async function handleSaveNote(e) {
            e.preventDefault();
            const noteId = document.getElementById('modal-note-id').value;
            const customerId = document.getElementById('modal-customer-id').value;
            const title = document.getElementById('modal-note-title').value.trim();
            const description = document.getElementById('modal-note-desc').value.trim();
            const priority = document.getElementById('modal-note-priority').value;
            const category = document.getElementById('modal-note-category').value;

            const isEdit = !!noteId;
            const url = isEdit ? `/api/notes/${noteId}` : `/api/notes`;
            const method = isEdit ? 'PUT' : 'POST';

            try {
                const res = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        created_by: customerId,
                        customer_id: customerId,
                        title,
                        description,
                        priority,
                        category
                    })
                });
                const data = await res.json();
                if (data.status) {
                    closeNoteModal();
                    loadCustomersList();
                    loadDashboardData();
                } else {
                    alert(data.message || 'Error saving note.');
                }
            } catch (err) {
                console.error('Save error:', err);
            }
        }

        async function deleteNote(id) {
            if (!confirm(`Are you sure you want to delete Note #${id}?`)) return;
            try {
                const res = await fetch(`/api/notes/${id}`, { method: 'DELETE', headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                if (data.status) {
                    loadCustomersList();
                    loadDashboardData();
                } else {
                    alert(data.message || 'Error deleting note.');
                }
            } catch (e) {
                console.error('Delete error:', e);
            }
        }

        // ------------------ REST API PLAYGROUND ------------------
        function renderEndpointsSidebar() {
            const list = document.getElementById('endpoints-list');
            list.innerHTML = apiEndpoints.map(ep => {
                const mClass = `method-${ep.method.toLowerCase()}`;
                const isSelected = ep.id === selectedEndpoint.id;
                return `
                    <div onclick="selectEndpoint('${ep.id}')" class="p-2.5 rounded-xl border ${isSelected ? 'border-sky-500 bg-slate-800/90' : 'border-transparent hover:bg-slate-800/50'} cursor-pointer transition-all">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold ${mClass}">${ep.method}</span>
                            <span class="text-xs font-bold text-slate-200 truncate">${ep.url}</span>
                        </div>
                        <p class="text-[11px] text-slate-400 truncate">${ep.label}</p>
                    </div>
                `;
            }).join('');
        }

        function selectEndpoint(id) {
            selectedEndpoint = apiEndpoints.find(e => e.id === id) || apiEndpoints[0];
            renderEndpointsSidebar();

            const badge = document.getElementById('selected-method-badge');
            badge.innerText = selectedEndpoint.method;
            badge.className = `px-2.5 py-1 rounded-lg text-xs font-black method-${selectedEndpoint.method.toLowerCase()}`;

            // Build Params Form
            const container = document.getElementById('request-params-container');
            const params = selectedEndpoint.params || {};
            const paramKeys = Object.keys(params);

            if (paramKeys.length === 0) {
                container.innerHTML = `<span class="text-slate-500 text-xs">No parameters required for this endpoint.</span>`;
            } else {
                container.innerHTML = paramKeys.map(key => `
                    <div class="flex items-center gap-2">
                        <label class="w-28 text-slate-400 font-mono text-xs shrink-0">${key}:</label>
                        <input type="text" id="param-${key}" value="${params[key]}" oninput="updateUrlPreview()" class="flex-1 bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1 text-slate-100 font-mono text-xs focus:border-sky-500 focus:outline-none">
                    </div>
                `).join('');
            }

            // Body section
            const bodySection = document.getElementById('request-body-section');
            if (selectedEndpoint.method === 'POST' || selectedEndpoint.method === 'PUT') {
                bodySection.classList.remove('hidden');
                document.getElementById('request-body-json').value = JSON.stringify(selectedEndpoint.body || {}, null, 2);
            } else {
                bodySection.classList.add('hidden');
            }

            updateUrlPreview();
            generateSnippet('curl');
        }

        function updateUrlPreview() {
            let finalUrl = selectedEndpoint.url;
            const params = selectedEndpoint.params || {};
            const queryParts = [];

            Object.keys(params).forEach(key => {
                const el = document.getElementById(`param-${key}`);
                const val = el ? el.value.trim() : params[key];

                if (finalUrl.includes(`{${key}}`)) {
                    finalUrl = finalUrl.replace(`{${key}}`, val || '1');
                } else if (val) {
                    queryParts.push(`${key}=${encodeURIComponent(val)}`);
                }
            });

            if (queryParts.length > 0 && selectedEndpoint.method === 'GET') {
                finalUrl += '?' + queryParts.join('&');
            }

            document.getElementById('selected-endpoint-url').innerText = finalUrl;
            return finalUrl;
        }

        async function executeApiRequest() {
            const finalUrl = updateUrlPreview();
            const method = selectedEndpoint.method;
            const btn = document.getElementById('btn-send-request');
            const statusBadge = document.getElementById('response-status-badge');
            const timeEl = document.getElementById('response-time');
            const sizeEl = document.getElementById('response-size');
            const viewer = document.getElementById('response-json-viewer');

            btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Sending...`;
            viewer.innerText = '// Fetching response...';

            const startTime = performance.now();

            try {
                let options = {
                    method: method,
                    headers: { 'Accept': 'application/json' }
                };

                if (method === 'POST' || method === 'PUT') {
                    options.headers['Content-Type'] = 'application/json';
                    const rawBody = document.getElementById('request-body-json').value;
                    options.body = rawBody;
                }

                const res = await fetch(finalUrl, options);
                const endTime = performance.now();
                const latency = Math.round(endTime - startTime);

                const data = await res.json();
                const jsonStr = JSON.stringify(data, null, 2);

                timeEl.innerText = `${latency} ms`;
                sizeEl.innerText = `${(jsonStr.length / 1024).toFixed(2)} KB`;

                statusBadge.innerText = `${res.status} ${res.statusText || 'OK'}`;
                statusBadge.className = res.ok ? 'px-2 py-0.5 rounded font-bold text-[11px] bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'px-2 py-0.5 rounded font-bold text-[11px] bg-rose-500/20 text-rose-300 border border-rose-500/30';

                viewer.innerText = jsonStr;
                btn.innerHTML = `<i class="fa-solid fa-paper-plane"></i> Send Request`;
            } catch (err) {
                console.error('API Exec Error:', err);
                statusBadge.innerText = 'Network Error';
                statusBadge.className = 'px-2 py-0.5 rounded font-bold text-[11px] bg-rose-500/20 text-rose-300';
                viewer.innerText = `Error: ${err.message}`;
                btn.innerHTML = `<i class="fa-solid fa-paper-plane"></i> Send Request`;
            }
        }

        function generateSnippet(type) {
            const url = window.location.origin + updateUrlPreview();
            const method = selectedEndpoint.method;
            let code = '';

            if (type === 'curl') {
                code = `curl -X ${method} "${url}" \\\n  -H "Accept: application/json"`;
                if (method === 'POST' || method === 'PUT') {
                    const body = document.getElementById('request-body-json')?.value || '{}';
                    code += ` \\\n  -H "Content-Type: application/json" \\\n  -d '${body.replace(/\n/g, '')}'`;
                }
            } else if (type === 'js') {
                code = `fetch("${url}", {\n  method: "${method}",\n  headers: { "Accept": "application/json" }`;
                if (method === 'POST' || method === 'PUT') {
                    const body = document.getElementById('request-body-json')?.value || '{}';
                    code += `,\n  body: JSON.stringify(${body})\n})`;
                } else {
                    code += `\n})\n.then(res => res.json())\n.then(data => console.log(data));`;
                }
            } else if (type === 'php') {
                code = `$client = new \\GuzzleHttp\\Client();\n$response = $client->request('${method}', '${url}', [\n  'headers' => ['Accept' => 'application/json']\n]);\n$data = json_decode($response->getBody(), true);`;
            }

            document.getElementById('code-snippet-box').innerText = code;
        }

        function copyCurrentUrl() {
            const url = window.location.origin + updateUrlPreview();
            navigator.clipboard.writeText(url);
            alert('URL copied to clipboard!');
        }

        function copyResponseJson() {
            const text = document.getElementById('response-json-viewer').innerText;
            navigator.clipboard.writeText(text);
            alert('JSON Response copied to clipboard!');
        }

        // Init on DOM ready
        window.addEventListener('DOMContentLoaded', async () => {
            await loadCustomersList();
            loadDashboardData();
        });
    </script>
</body>
</html>
