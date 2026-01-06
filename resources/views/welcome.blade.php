<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System | Modern Dashboard</title>

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

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }

        .action-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .action-card:hover {
            transform: translateY(-8px) scale(1.02);
            background: rgba(255, 255, 255, 0.25);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.4);
        }

        .hero-title {
            animation: scaleIn 0.8s ease-out;
            background: linear-gradient(135deg, #ffffff 0%, #f0f0f0 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            animation: fadeInUp 1s ease-out 0.2s backwards;
        }

        .floating-icon {
            animation: float 3s ease-in-out infinite;
        }

        .card-delay-1 { animation-delay: 0.1s; }
        .card-delay-2 { animation-delay: 0.2s; }
        .card-delay-3 { animation-delay: 0.3s; }

        .badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            animation: fadeInUp 1.2s ease-out 0.4s backwards;
        }

        .icon-wrapper {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.1));
            transition: all 0.3s ease;
        }

        .action-card:hover .icon-wrapper {
            transform: scale(1.1) rotate(5deg);
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.4), rgba(255, 255, 255, 0.2));
        }

        .grid-pattern {
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 50px 50px;
        }

        .admin-tool-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 1rem;
            border-radius: 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .admin-tool-card:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .icon-sm {
            width: 3rem;
            height: 3rem;
            border-radius: 0.75rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.05));
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .admin-tool-card:hover .icon-sm {
            transform: scale(1.1) rotate(5deg);
        }
    </style>
</head>

<body class="overflow-x-hidden">
    <!-- BACKGROUND GRID PATTERN -->
    <div class="fixed inset-0 grid-pattern pointer-events-none"></div>

    <!-- MAIN CONTAINER -->
    <div class="relative min-h-screen flex items-center justify-center px-4 py-12">
        <div class="max-w-7xl w-full">
            
            <!-- HERO SECTION -->
            <div class="text-center mb-16">
                <!-- BADGE -->
                <div class="inline-block mb-6">
                    <span class="badge px-6 py-2 rounded-full text-white text-sm font-semibold shadow-lg">
                        <i class="fas fa-graduation-cap mr-2"></i>
                        Version 2.0
                    </span>
                </div>

                <!-- TITLE -->
                <h1 class="hero-title text-4xl md:text-5xl lg:text-6xl font-extrabold mb-6 leading-tight">
                    Student Management
                    <br>
                    <span class="text-3xl md:text-4xl lg:text-5xl">System</span>
                </h1>

                <!-- SUBTITLE -->
                <p class="subtitle text-white/90 text-lg md:text-xl lg:text-2xl max-w-3xl mx-auto font-light leading-relaxed">
                    Empowering educational institutions with modern, robust, and scalable solutions.
                    <br class="hidden md:block">
                    Manage students, staff, and operations seamlessly.
                </p>
            </div>

            <!-- ACTION CARDS GRID -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                
                <!-- SWAGGER REFRESH CARD -->
                <a href="{{ env('APP_URL') }}/swagger-refresh" target="_blank" class="action-card card-delay-1 rounded-2xl p-8 group">
                    <div class="flex flex-col items-center text-center space-y-4">
                        <!-- ICON -->
                        <div class="icon-wrapper w-20 h-20 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-sync-alt text-3xl text-white"></i>
                        </div>
                        
                        <!-- TITLE -->
                        <h3 class="text-white text-2xl font-bold">
                            Swagger Refresh
                        </h3>
                        
                        <!-- DESCRIPTION -->
                        <p class="text-white/80 text-sm leading-relaxed">
                            Regenerate API documentation instantly. Keep your Swagger specs up-to-date with the latest endpoints.
                        </p>

                        <!-- ARROW ICON -->
                        <div class="mt-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <i class="fas fa-arrow-right text-white text-xl"></i>
                        </div>
                    </div>
                </a>

                <!-- API DOCUMENTATION CARD -->
                <a href="{{ env('APP_URL') }}/api/documentation#/" target="_blank" class="action-card card-delay-2 rounded-2xl p-8 group">
                    <div class="flex flex-col items-center text-center space-y-4">
                        <!-- ICON -->
                        <div class="icon-wrapper w-20 h-20 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-book text-3xl text-white"></i>
                        </div>
                        
                        <!-- TITLE -->
                        <h3 class="text-white text-2xl font-bold">
                            API Documentation
                        </h3>
                        
                        <!-- DESCRIPTION -->
                        <p class="text-white/80 text-sm leading-relaxed">
                            Explore comprehensive API documentation. Test endpoints, view schemas, and integrate seamlessly.
                        </p>

                        <!-- ARROW ICON -->
                        <div class="mt-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <i class="fas fa-arrow-right text-white text-xl"></i>
                        </div>
                    </div>
                </a>

                <!-- ACTIVITY LOG CARD -->
                <a href="{{ env('APP_URL') }}/error-log" target="_blank" class="action-card card-delay-3 rounded-2xl p-8 group">
                    <div class="flex flex-col items-center text-center space-y-4">
                        <!-- ICON -->
                        <div class="icon-wrapper w-20 h-20 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-chart-line text-3xl text-white"></i>
                        </div>
                        
                        <!-- TITLE -->
                        <h3 class="text-white text-2xl font-bold">
                            Activity Log
                        </h3>
                        
                        <!-- DESCRIPTION -->
                        <p class="text-white/80 text-sm leading-relaxed">
                            Monitor system activities and track errors in real-time. Ensure smooth operations and quick debugging.
                        </p>

                        <!-- ARROW ICON -->
                        <div class="mt-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <i class="fas fa-arrow-right text-white text-xl"></i>
                        </div>
                    </div>
                </a>

            </div>

            <!-- DEVELOPER & ADMIN TOOLS SECTION -->
            <div class="mt-16" style="animation: fadeInUp 2s ease-out 1s backwards;">
                <div id="admin-tools-locked" class="glass-card rounded-3xl overflow-hidden">
                    <div class="cursor-pointer p-6 flex items-center justify-between hover:bg-white/10 transition-all duration-300" onclick="showPasswordPrompt()">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-tools text-yellow-300 text-xl"></i>
                            <h3 class="text-white text-xl font-bold">Developer & Admin Tools</h3>
                            <span class="px-3 py-1 bg-yellow-500/30 border border-yellow-400/50 rounded-full text-yellow-200 text-xs font-semibold">
                                <i class="fas fa-lock mr-1"></i>
                                Secret
                            </span>
                        </div>
                        <i class="fas fa-lock text-white/70 text-xl"></i>
                    </div>
                </div>

                <div id="admin-tools-unlocked" class="glass-card rounded-3xl overflow-hidden hidden">
                    <div class="p-6 flex items-center justify-between bg-green-500/10 border-b border-white/10">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-tools text-green-300 text-xl"></i>
                            <h3 class="text-white text-xl font-bold">Developer & Admin Tools</h3>
                            <span class="px-3 py-1 bg-green-500/30 border border-green-400/50 rounded-full text-green-200 text-xs font-semibold">
                                <i class="fas fa-unlock mr-1"></i>
                                Unlocked
                            </span>
                        </div>
                        <button onclick="lockAdminTools()" class="text-white/70 hover:text-white transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <div class="p-6">
                        <p class="text-white/60 text-sm mb-6 flex items-center gap-2">
                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                            These tools are for development and administration purposes only. Use with caution.
                        </p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
                            
                            <!-- ROLE REFRESH -->
                            <a href="{{ env('APP_URL') }}/role-refresh" target="_blank" class="admin-tool-card">
                                <div class="icon-sm">
                                    <i class="fas fa-user-shield text-lg text-red-300"></i>
                                </div>
                                <h4 class="text-white font-semibold text-sm">Role Refresh</h4>
                                <p class="text-white/60 text-xs">Refresh role permissions</p>
                            </a>

                            <!-- MODULE UPDATE -->
                            <a href="{{ env('APP_URL') }}/module-update" target="_blank" class="admin-tool-card">
                                <div class="icon-sm">
                                    <i class="fas fa-puzzle-piece text-lg text-blue-300"></i>
                                </div>
                                <h4 class="text-white font-semibold text-sm">Module Update</h4>
                                <p class="text-white/60 text-xs">Update system modules</p>
                            </a>

                            <!-- DB OPERATION -->
                            <a href="{{ env('APP_URL') }}/one-time/db-operation" target="_blank" class="admin-tool-card">
                                <div class="icon-sm">
                                    <i class="fas fa-database text-lg text-purple-300"></i>
                                </div>
                                <h4 class="text-white font-semibold text-sm">DB Operation</h4>
                                <p class="text-white/60 text-xs">One-time database tasks</p>
                            </a>

                            <!-- SETUP -->
                            <a href="{{ env('APP_URL') }}/setup" target="_blank" class="admin-tool-card">
                                <div class="icon-sm">
                                    <i class="fas fa-cog text-lg text-green-300"></i>
                                </div>
                                <h4 class="text-white font-semibold text-sm">Setup</h4>
                                <p class="text-white/60 text-xs">Initial system setup</p>
                            </a>

                            <!-- MIGRATE -->
                            <a href="{{ env('APP_URL') }}/migrate" target="_blank" class="admin-tool-card">
                                <div class="icon-sm">
                                    <i class="fas fa-arrow-up text-lg text-orange-300"></i>
                                </div>
                                <h4 class="text-white font-semibold text-sm">Migrate</h4>
                                <p class="text-white/60 text-xs">Run database migrations</p>
                            </a>

                        </div>
                    </div>
                </div>
            </div>

            <!-- PASSWORD MODAL -->
            <div id="password-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50" onclick="closePasswordModal(event)">
                <div class="glass-card rounded-3xl p-8 max-w-md w-full mx-4" onclick="event.stopPropagation()">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-yellow-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-lock text-3xl text-yellow-300"></i>
                        </div>
                        <h3 class="text-white text-2xl font-bold mb-2">Admin Access Required</h3>
                        <p class="text-white/70 text-sm">Enter password to access developer tools</p>
                    </div>
                    
                    <form onsubmit="checkPassword(event)" class="space-y-4">
                        <div>
                            <input 
                                type="password" 
                                id="admin-password" 
                                class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-white/50 focus:outline-none focus:border-white/40 focus:bg-white/15 transition-all"
                                placeholder="Enter password"
                                autocomplete="off"
                            >
                            <p id="password-error" class="text-red-300 text-sm mt-2 hidden">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                Incorrect password. Please try again.
                            </p>
                        </div>
                        
                        <div class="flex gap-3">
                            <button 
                                type="button"
                                onclick="closePasswordModal()"
                                class="flex-1 px-6 py-3 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl text-white font-semibold transition-all"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit"
                                class="flex-1 px-6 py-3 bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 rounded-xl text-white font-semibold transition-all shadow-lg"
                            >
                                Unlock
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="text-center mt-12" style="animation: fadeInUp 1.8s ease-out 0.8s backwards;">
                <p class="text-white/60 text-sm">
                    &copy; {{ date('Y') }} Student Management System. Powered by Laravel {{ app()->version() }}
                </p>
            </div>

            </div>

        </div>
    </div>

    <script>
        function showPasswordPrompt() {
            const modal = document.getElementById('password-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('admin-password').focus();
            document.getElementById('password-error').classList.add('hidden');
            document.getElementById('admin-password').value = '';
        }

        function closePasswordModal(event) {
            if (event) event.preventDefault();
            const modal = document.getElementById('password-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.getElementById('admin-password').value = '';
            document.getElementById('password-error').classList.add('hidden');
        }

        function checkPassword(event) {
            event.preventDefault();
            const password = document.getElementById('admin-password').value;
            const correctPassword = '1234';
            
            if (password === correctPassword) {
                // Unlock admin tools
                document.getElementById('admin-tools-locked').classList.add('hidden');
                document.getElementById('admin-tools-unlocked').classList.remove('hidden');
                closePasswordModal();
                
                // Smooth scroll to admin tools
                setTimeout(() => {
                    document.getElementById('admin-tools-unlocked').scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                }, 100);
            } else {
                // Show error
                const errorElement = document.getElementById('password-error');
                const inputElement = document.getElementById('admin-password');
                
                errorElement.classList.remove('hidden');
                inputElement.classList.add('border-red-400');
                
                // Shake animation
                inputElement.style.animation = 'shake 0.5s';
                setTimeout(() => {
                    inputElement.style.animation = '';
                }, 500);
                
                // Clear password
                inputElement.value = '';
                inputElement.focus();
            }
        }

        function lockAdminTools() {
            document.getElementById('admin-tools-unlocked').classList.add('hidden');
            document.getElementById('admin-tools-locked').classList.remove('hidden');
        }

        // Add shake animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
                20%, 40%, 60%, 80% { transform: translateX(10px); }
            }
        `;
        document.head.appendChild(style);

        // Close modal on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closePasswordModal();
            }
        });
    </script>
</body>

</html>
