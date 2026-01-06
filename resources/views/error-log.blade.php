<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error Log | Student Management System</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #4facfe 75%, #00f2fe 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            min-height: 100vh;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }

        .glass-input {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .glass-input:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.4);
            outline: none;
        }

        .error-row {
            animation: fadeInUp 0.4s ease-out;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .error-row:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .badge-status {
            padding: 0.375rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-200 { background: rgba(34, 197, 94, 0.2); color: #86efac; border: 1px solid rgba(34, 197, 94, 0.3); }
        .status-400 { background: rgba(251, 146, 60, 0.2); color: #fdba74; border: 1px solid rgba(251, 146, 60, 0.3); }
        .status-401 { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); }
        .status-403 { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); }
        .status-404 { background: rgba(251, 146, 60, 0.2); color: #fdba74; border: 1px solid rgba(251, 146, 60, 0.3); }
        .status-500 { background: rgba(220, 38, 38, 0.2); color: #fca5a5; border: 1px solid rgba(220, 38, 38, 0.3); }

        .badge-method {
            padding: 0.375rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .method-GET { background: rgba(34, 197, 94, 0.2); color: #86efac; border: 1px solid rgba(34, 197, 94, 0.3); }
        .method-POST { background: rgba(59, 130, 246, 0.2); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.3); }
        .method-PUT { background: rgba(251, 146, 60, 0.2); color: #fdba74; border: 1px solid rgba(251, 146, 60, 0.3); }
        .method-DELETE { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); }
        .method-PATCH { background: rgba(168, 85, 247, 0.2); color: #d8b4fe; border: 1px solid rgba(168, 85, 247, 0.3); }

        .grid-pattern {
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 50px 50px;
        }

        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        .expandable-cell {
            cursor: pointer;
            position: relative;
        }

        .expandable-cell:hover .expand-icon {
            opacity: 1;
        }

        .expand-icon {
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .details-row {
            display: none;
        }

        .details-row.show {
            display: table-row;
            animation: fadeInUp 0.3s ease-out;
        }
    </style>
</head>

<body class="overflow-x-hidden">
    <!-- BACKGROUND GRID PATTERN -->
    <div class="fixed inset-0 grid-pattern pointer-events-none"></div>

    <!-- MAIN CONTAINER -->
    <div class="relative min-h-screen py-8 px-4">
        <div class="max-w-7xl mx-auto">
            
            <!-- HEADER -->
            <div class="mb-8" style="animation: fadeInUp 0.6s ease-out;">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-4">
                        <a href="{{ env('APP_URL') }}" class="glass-card px-4 py-2 rounded-xl text-white/80 hover:text-white hover:bg-white/15 transition-all flex items-center gap-2">
                            <i class="fas fa-arrow-left"></i>
                            <span class="hidden sm:inline">Back to Home</span>
                        </a>
                        <h1 class="text-4xl md:text-5xl font-extrabold text-white">
                            <i class="fas fa-exclamation-triangle mr-3 text-red-300"></i>
                            Error Log
                        </h1>
                    </div>
                    <div class="glass-card px-4 py-2 rounded-xl">
                        <span class="text-white/70 text-sm">Total Errors: </span>
                        <span class="text-red-300 font-bold text-lg">{{ $error_logs->total() }}</span>
                    </div>
                </div>
                <p class="text-white/80 text-lg">Monitor and debug system errors in real-time</p>
            </div>

            <!-- SEARCH & FILTER -->
            <div class="glass-card rounded-2xl p-6 mb-6" style="animation: fadeInUp 0.8s ease-out;">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-white/50"></i>
                        <input 
                            type="text" 
                            id="searchInput" 
                            placeholder="Search by message, file, user..." 
                            class="glass-input w-full pl-10 pr-4 py-3 rounded-xl text-white placeholder-white/50"
                            onkeyup="filterTable()"
                        >
                    </div>
                    <div class="relative">
                        <i class="fas fa-filter absolute left-3 top-1/2 transform -translate-y-1/2 text-white/50"></i>
                        <select 
                            id="statusFilter" 
                            class="glass-input w-full pl-10 pr-4 py-3 rounded-xl text-white appearance-none cursor-pointer"
                            onchange="filterTable()"
                        >
                            <option value="">All Status Codes</option>
                            <option value="200">200 - Success</option>
                            <option value="400">400 - Bad Request</option>
                            <option value="401">401 - Unauthorized</option>
                            <option value="403">403 - Forbidden</option>
                            <option value="404">404 - Not Found</option>
                            <option value="500">500 - Server Error</option>
                        </select>
                    </div>
                    <button 
                        onclick="clearFilters()" 
                        class="glass-input px-6 py-3 rounded-xl text-white hover:bg-white/15 transition-all flex items-center justify-center gap-2"
                    >
                        <i class="fas fa-redo"></i>
                        Clear Filters
                    </button>
                </div>
            </div>

            <!-- TABLE CONTAINER -->
            <div class="glass-card rounded-2xl overflow-hidden" style="animation: fadeInUp 1s ease-out;">
                <div class="overflow-x-auto scrollbar-thin">
                    <table class="w-full" id="errorTable">
                        <thead>
                            <tr class="border-b border-white/10">
                                <th class="text-left py-4 px-6 text-white/90 font-semibold text-sm uppercase tracking-wider">ID</th>
                                <th class="text-left py-4 px-6 text-white/90 font-semibold text-sm uppercase tracking-wider">User</th>
                                <th class="text-left py-4 px-6 text-white/90 font-semibold text-sm uppercase tracking-wider">API URL</th>
                                <th class="text-left py-4 px-6 text-white/90 font-semibold text-sm uppercase tracking-wider">Message</th>
                                <th class="text-center py-4 px-6 text-white/90 font-semibold text-sm uppercase tracking-wider">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($error_logs as $error_log)
                            <tr class="error-row border-b border-white/5" data-status="{{ $error_log->status_code }}" onclick="toggleDetails({{ $error_log->id }})">
                                <td class="py-4 px-6">
                                    <span class="text-white/70 font-mono text-sm">#{{ $error_log->id }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-red-400 to-orange-400 flex items-center justify-center shrink-0">
                                            <i class="fas fa-user text-white text-xs"></i>
                                        </div>
                                        <div class="min-w-0">
                                            @if ($error_log->ERRuser)
                                            <p class="text-white font-medium truncate">{{ $error_log->ERRuser->first_Name . ' ' . $error_log->ERRuser->last_Name }}</p>
                                            <p class="text-white/50 text-xs">ID: {{ $error_log->user_id }}</p>
                                            @else
                                            <p class="text-white/70 font-medium">Guest</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <p class="text-white/80 font-mono text-sm break-all max-w-xs truncate" title="{{ $error_log->api_url }}">
                                        {{ $error_log->api_url }}
                                    </p>
                                </td>
                                <td class="py-4 px-6 max-w-md">
                                    <p class="text-red-200 font-medium text-sm truncate" title="{{ $error_log->message }}">
                                        {{ Str::limit($error_log->message, 60) }}
                                    </p>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="glass-input px-3 py-1 rounded-lg text-white/80 inline-block">
                                        <i class="fas fa-chevron-down transition-transform duration-300" id="chevron-{{ $error_log->id }}"></i>
                                    </div>
                                </td>
                            </tr>
                            <tr class="details-row border-b border-white/5" id="details-{{ $error_log->id }}">
                                <td colspan="5" class="p-6 bg-black/20">
                                    <!-- STATUS, METHOD, IP - TOP ROW -->
                                    <div class="flex flex-wrap items-center gap-4 mb-6 pb-4 border-b border-white/10">
                                        <div class="flex items-center gap-2">
                                            <span class="text-white/50 text-xs uppercase font-semibold">Status:</span>
                                            <span class="badge-status status-{{ substr($error_log->status_code, 0, 3) }}">
                                                {{ $error_log->status_code }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-white/50 text-xs uppercase font-semibold">Method:</span>
                                            <span class="badge-method method-{{ $error_log->request_method }}">
                                                {{ $error_log->request_method }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-white/50 text-xs uppercase font-semibold">IP Address:</span>
                                            <div class="flex items-center gap-2 glass-input px-3 py-1 rounded-lg">
                                                <i class="fas fa-map-marker-alt text-white/50 text-xs"></i>
                                                <span class="text-white/80 font-mono text-sm">{{ $error_log->ip_address }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <h4 class="text-white/70 text-xs uppercase font-semibold mb-2">Full Message</h4>
                                            <p class="text-red-200 font-mono text-sm bg-black/30 p-3 rounded-lg break-all">{{ $error_log->message }}</p>
                                        </div>
                                        <div>
                                            <h4 class="text-white/70 text-xs uppercase font-semibold mb-2">File & Line</h4>
                                            <p class="text-white/80 font-mono text-sm bg-black/30 p-3 rounded-lg break-all">
                                                <i class="fas fa-file-code mr-2 text-blue-300"></i>{{ $error_log->file }}
                                                <br>
                                                <i class="fas fa-hashtag mr-2 text-green-300"></i>Line: {{ $error_log->line }}
                                            </p>
                                        </div>
                                        @if($error_log->fields)
                                        <div>
                                            <h4 class="text-white/70 text-xs uppercase font-semibold mb-2">Fields</h4>
                                            <p class="text-white/80 font-mono text-sm bg-black/30 p-3 rounded-lg break-all">{{ $error_log->fields }}</p>
                                        </div>
                                        @endif
                                        @if($error_log->token)
                                        <div>
                                            <h4 class="text-white/70 text-xs uppercase font-semibold mb-2">Token</h4>
                                            <p class="text-white/80 font-mono text-xs bg-black/30 p-3 rounded-lg break-all">{{ Str::limit($error_log->token, 100) }}</p>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <div class="p-6 border-t border-white/10">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <p class="text-white/70 text-sm">
                            Showing {{ $error_logs->firstItem() ?? 0 }} to {{ $error_logs->lastItem() ?? 0 }} of {{ $error_logs->total() }} errors
                        </p>
                        <div class="flex gap-2">
                            {{ $error_logs->links() }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="text-center mt-8" style="animation: fadeInUp 1.2s ease-out;">
                <p class="text-white/60 text-sm">
                    &copy; {{ date('Y') }} Student Management System. Powered by Laravel {{ app()->version() }}
                </p>
            </div>

        </div>
    </div>

    <script>
        function toggleDetails(id) {
            const detailsRow = document.getElementById(`details-${id}`);
            const chevron = document.getElementById(`chevron-${id}`);
            
            if (detailsRow.classList.contains('show')) {
                detailsRow.classList.remove('show');
                chevron.style.transform = 'rotate(0deg)';
            } else {
                detailsRow.classList.add('show');
                chevron.style.transform = 'rotate(180deg)';
            }
        }

        function filterTable() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const table = document.getElementById('errorTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                
                // Skip details rows
                if (row.classList.contains('details-row')) {
                    continue;
                }

                let showRow = true;

                // Search filter
                if (searchInput) {
                    const searchText = row.textContent.toLowerCase();
                    showRow = searchText.includes(searchInput);
                }

                // Status filter
                if (statusFilter && showRow) {
                    const status = row.getAttribute('data-status');
                    showRow = status && status.includes(statusFilter);
                }

                row.style.display = showRow ? '' : 'none';
                
                // Hide corresponding details row if parent is hidden
                const id = row.querySelector('[id^="chevron-"]')?.id.replace('chevron-', '');
                if (id) {
                    const detailsRow = document.getElementById(`details-${id}`);
                    if (detailsRow) {
                        detailsRow.style.display = showRow ? '' : 'none';
                    }
                }
            }
        }

        function clearFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('statusFilter').value = '';
            filterTable();
        }
    </script>
</body>

</html>
