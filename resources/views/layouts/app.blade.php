<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <meta name="description" content="MyCafe - Point of Sale & Membership Loyalty System"/>
    <title>@yield('title', 'MyCafe - POS & Membership Loyalty System')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
        // Enforce pure white light mode (dark mode disabled)
        (function() {
            document.documentElement.classList.remove('dark');
            localStorage.removeItem('mycafe_theme');
        })();

        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: "#944b1c", // Warm Artisan Espresso
                            hover: "#7c3c14",
                            light: "#fff7ed",
                            dark: "#5c2a0c",
                            50: "#fff7ed",
                            100: "#ffedd5",
                            200: "#fed7aa",
                            500: "#944b1c",
                            600: "#7c3c14",
                            700: "#5c2a0c",
                        },
                        slate: {
                            850: "#151e2e",
                            900: "#0f172a",
                            950: "#080c16",
                        },
                        background: {
                            light: "#f8fafc",
                            dark: "#0b0f19",
                        },
                        surface: {
                            light: "#ffffff",
                            dark: "#111827",
                        },
                        card: {
                            light: "#ffffff",
                            dark: "#141d2d",
                        },
                        border: {
                            light: "#e2e8f0",
                            dark: "#1f293d",
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', '-apple-system', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'ui-monospace', 'monospace'],
                    },
                    boxShadow: {
                        'subtle': '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
                        'soft': '0 2px 8px -2px rgba(15, 23, 42, 0.06), 0 1px 3px -1px rgba(15, 23, 42, 0.04)',
                        'soft-md': '0 8px 20px -4px rgba(15, 23, 42, 0.08), 0 3px 6px -2px rgba(15, 23, 42, 0.04)',
                        'soft-lg': '0 20px 32px -8px rgba(15, 23, 42, 0.12), 0 8px 12px -4px rgba(15, 23, 42, 0.05)',
                    }
                }
            }
        };
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            letter-spacing: -0.015em;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        .card-hover {
            transition: transform 0.18s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.18s ease, border-color 0.18s ease;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -6px rgba(15, 23, 42, 0.08), 0 4px 8px -2px rgba(15, 23, 42, 0.04);
        }
        
        .btn-press {
            transition: transform 0.12s ease, filter 0.12s ease;
        }
        .btn-press:active {
            transform: scale(0.98);
        }
        
        @keyframes toastSlideIn {
            from { transform: translateY(16px) scale(0.96); opacity: 0; }
            to { transform: translateY(0) scale(1); opacity: 1; }
        }
        .animate-toast {
            animation: toastSlideIn 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @media print {
            aside, header, #sidebar-backdrop, .no-print, nav, #global-topbar {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
                font-size: 12px !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            main {
                padding: 0 !important;
                margin: 0 !important;
                overflow: visible !important;
            }
            .print-receipt {
                box-shadow: none !important;
                border: none !important;
                max-width: 320px !important;
                width: 100% !important;
                margin: 0 auto !important;
                padding: 0 !important;
            }
        }
    </style>

    <script>
        function showToast(message, type = 'success') {
            let container = document.getElementById('mycafe-toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'mycafe-toast-container';
                container.className = 'fixed bottom-5 right-5 z-[9999] flex flex-col gap-2 pointer-events-none';
                document.body.appendChild(container);
            }
            
            const toast = document.createElement('div');
            const isSuccess = type === 'success';
            const isError = type === 'error';
            const bg = isSuccess 
                ? 'bg-slate-900 text-white border-slate-700/60' 
                : (isError ? 'bg-rose-600 text-white border-rose-500' : 'bg-slate-800 text-white border-slate-700');
            const icon = isSuccess ? 'check_circle' : (isError ? 'error_outline' : 'info');
            const iconColor = isSuccess ? 'text-emerald-400' : (isError ? 'text-white' : 'text-amber-400');
            
            toast.className = `${bg} px-4 py-3 rounded-xl shadow-soft-lg border flex items-center gap-3 text-xs font-semibold pointer-events-auto animate-toast min-w-[260px]`;
            toast.innerHTML = `
                <span class="material-icons text-lg shrink-0 ${iconColor}">${icon}</span>
                <div class="flex-1">${message}</div>
                <button type="button" onclick="this.parentElement.remove()" class="text-white/60 hover:text-white">
                    <span class="material-icons text-sm">close</span>
                </button>
            `;
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.style.transition = 'all 0.25s ease';
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px) scale(0.96)';
                setTimeout(() => toast.remove(), 250);
            }, 3200);
        }

        button, a, [role="button"] {
            touch-action: manipulation;
        }

        function copyToClipboard(text, message = null) {
            if (!text) return;
            const successMsg = message || ('Berhasil disalin: ' + text);
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text)
                    .then(() => showToast(successMsg, 'success'))
                    .catch(() => fallbackCopyToClipboard(text, successMsg));
            } else {
                fallbackCopyToClipboard(text, successMsg);
            }
        }

        function fallbackCopyToClipboard(text, successMsg) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.left = "-9999px";
            textArea.style.top = "-9999px";
            textArea.style.opacity = "0";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    showToast(successMsg, 'success');
                } else {
                    showToast('Silakan salin manual: ' + text, 'info');
                }
            } catch (err) {
                showToast('Silakan salin manual: ' + text, 'info');
            }
            document.body.removeChild(textArea);
        }

        // Global Escape Listener to close any active modal
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const openModals = document.querySelectorAll('[id$="-modal"]:not(.hidden), [id$="-dropdown"]:not(.hidden)');
                openModals.forEach(m => m.classList.add('hidden'));
            }
        });
    </script>
    @yield('styles')
</head>
<body class="bg-background-light font-sans text-slate-800 min-h-screen">
    <div class="flex flex-col lg:flex-row min-h-screen">
        @auth
            @include('layouts.sidebar')
        @endauth

        @yield('content')
    </div>

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast(@json(session('success')), 'success');
            });
        </script>
    @endif
    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast(@json(session('error')), 'error');
            });
        </script>
    @endif
    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast(@json($errors->first()), 'error');
            });
        </script>
    @endif

    @yield('scripts')
</body>
</html>
