<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pemeliharaan Sistem - E-SPPB Enterprise</title>
    
    <!-- Outfit Font from Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;750&display=swap" rel="stylesheet">
    
    <!-- Premium Tailwind-based Styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        @keyframes pulse-ring {
            0% { transform: scale(0.95); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
            100% { transform: scale(0.95); opacity: 0.5; }
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        .animate-pulse-ring {
            animation: pulse-ring 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        .glassmorphism {
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>
</head>
<body class="h-full bg-slate-950 text-slate-100 flex items-center justify-center overflow-hidden antialiased font-sans">
    <!-- Glowing background elements -->
    <div class="absolute top-[-20%] left-[-10%] w-[600px] h-[600px] rounded-full bg-indigo-900/20 blur-[150px]"></div>
    <div class="absolute bottom-[-20%] right-[-10%] w-[600px] h-[600px] rounded-full bg-sky-900/20 blur-[150px]"></div>
    
    <div class="relative z-10 max-w-xl w-full mx-4 text-center">
        <!-- Floating Illustration -->
        <div class="mb-8 flex justify-center animate-float">
            <div class="relative flex items-center justify-center w-24 h-24 rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl">
                <!-- Outer pulse ring -->
                <div class="absolute inset-[-4px] rounded-[28px] border-2 border-indigo-500/20 animate-pulse-ring"></div>
                
                <!-- Icon -->
                <svg class="w-12 h-12 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A1.75 1.75 0 1 0 19.73 18.5l-5.83-5.83m-2.48-1.5a3.75 3.75 0 1 1-5.3-5.3 3.75 3.75 0 0 1 5.3 5.3ZM12 12.75l-4.5 4.5" />
                </svg>
            </div>
        </div>
        
        <!-- Glassmorphism Card -->
        <div class="glassmorphism p-8 md:p-10 rounded-3xl shadow-2xl space-y-6">
            <div class="space-y-2">
                <span class="inline-flex items-center gap-x-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
                    MODE PEMELIHARAAN AKTIF
                </span>
                <h1 class="text-3xl md:text-4xl font-bold tracking-tight text-white mt-3">
                    Under Maintenance
                </h1>
            </div>
            
            <p class="text-slate-300 text-sm md:text-base leading-relaxed">
                {{ $message }}
            </p>
            
            <div class="border-t border-slate-800 pt-6 flex flex-col items-center gap-y-2">
                <p class="text-xs text-slate-500">
                    Jika Anda adalah Administrator, Anda dapat masuk melalui link di bawah:
                </p>
                <a href="/admin/login" class="inline-flex items-center gap-x-2 text-xs font-semibold text-indigo-400 hover:text-indigo-300 transition duration-150 ease-in-out">
                    <span>Masuk ke Admin Panel</span>
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        </div>
        
        <!-- Footer -->
        <p class="mt-8 text-xs text-slate-600 tracking-wider uppercase">
            &copy; {{ date('Y') }} {{ \App\Models\AppSetting::get('company_name', 'E-SPPB Enterprise') }}. All rights reserved.
        </p>
    </div>
</body>
</html>
