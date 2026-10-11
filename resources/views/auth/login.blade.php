<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Masuk - MyCafe POS & Loyalty System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>

    <script>
        (function() {
            document.documentElement.classList.remove('dark');
            localStorage.removeItem('mycafe_theme');
        })();

        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: "#944b1c",
                            hover: "#7c3c14",
                            light: "#fff7ed",
                            dark: "#5c2a0c",
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    boxShadow: {
                        'soft': '0 2px 8px -2px rgba(15, 23, 42, 0.06), 0 1px 3px -1px rgba(15, 23, 42, 0.04)',
                        'soft-lg': '0 20px 32px -8px rgba(15, 23, 42, 0.12), 0 8px 12px -4px rgba(15, 23, 42, 0.05)',
                    }
                }
            }
        };
    </script>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex items-center justify-center p-4 sm:p-6 font-sans">
    <!-- Subtle Ambient Background Pattern -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none opacity-40">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-primary/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl"></div>
    </div>

    <div class="w-full max-w-md relative z-10">
        <!-- Main Login Card -->
        <div class="bg-white border border-slate-200 rounded-3xl p-7 sm:p-8 shadow-soft-lg">
            <!-- Brand & Heading -->
            <div class="text-center mb-7">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-primary to-amber-700 text-white shadow-soft mb-3.5 border border-primary/20">
                    <span class="material-icons text-3xl">local_cafe</span>
                </div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900 flex items-center justify-center gap-1">
                    My<span class="text-primary">Cafe</span>
                    <span class="text-[10px] uppercase font-bold tracking-widest text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full ml-1 border border-slate-200">POS</span>
                </h1>
                <p class="text-xs text-slate-500 mt-1">Platform Manajemen Kasir & Loyalitas Pelanggan (Laravel Edition)</p>
            </div>

            <!-- Flash Alert -->
            @if (session('success'))
            <div class="mb-5 p-3.5 rounded-xl text-xs font-semibold flex items-center gap-2.5 border bg-emerald-50 text-emerald-800 border-emerald-200">
                <span class="material-icons text-base shrink-0">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            <!-- Error Alert -->
            @if (session('error'))
            <div class="mb-5 p-3.5 rounded-xl bg-rose-50 text-rose-800 border border-rose-200 flex items-center gap-2.5 text-xs font-semibold">
                <span class="material-icons text-base shrink-0">error_outline</span>
                <span>{{ session('error') }}</span>
            </div>
            @endif

            <!-- Login Form -->
            <form id="login-form" action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="identifier" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Username, No. HP, Email, atau Kode Member</label>
                    <div class="relative">
                        <span class="material-icons absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">person</span>
                        <input id="identifier" name="identifier" type="text" required
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                            placeholder="Username"
                            value="{{ old('identifier') }}"/>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Kata Sandi</label>
                        <span class="text-[11px] text-slate-400">Default: <strong class="text-primary font-mono">123</strong></span>
                    </div>
                    <div class="relative">
                        <span class="material-icons absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">lock</span>
                        <input id="password" name="password" type="password" required
                            class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                            placeholder="Masukkan kata sandi..."/>
                        <button type="button" onclick="togglePassVisibility()" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 transition">
                            <span id="pass-icon" class="material-icons text-base">visibility</span>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-500 hover:text-slate-800 transition">
                        <input type="checkbox" name="remember-me" class="rounded bg-white border-slate-300 text-primary focus:ring-primary/40"/>
                        <span>Ingat saya di peramban</span>
                    </label>
                    <a href="{{ route('members.create') }}" class="text-primary hover:text-primary-hover font-semibold transition">Daftar Member Baru</a>
                </div>

                <button type="submit" class="w-full py-3 bg-primary hover:bg-primary-hover text-white font-bold rounded-xl text-sm shadow-soft transition active:scale-[0.98] flex items-center justify-center gap-2 mt-2">
                    <span>Masuk ke Dashboard</span>
                    <span class="material-icons text-base">arrow_forward</span>
                </button>

                <!-- Quick Demo Login Selection -->
                <div class="pt-5 border-t border-slate-100">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Akses Cepat Demo:</p>
                        <span class="text-[10px] text-slate-400">Password: <code class="text-primary font-bold">123</code></span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <button type="button" onclick="quickFill('admin', '123')" class="p-2.5 bg-slate-50 hover:bg-primary/5 border border-slate-200 hover:border-primary/40 rounded-xl text-left transition group cursor-pointer active:scale-95">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800 group-hover:text-primary">Administrator</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 font-mono font-bold">ADM</span>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-0.5">Semua hak akses POS & data</p>
                        </button>
                        <button type="button" onclick="quickFill('kasir', '123')" class="p-2.5 bg-slate-50 hover:bg-emerald-50 border border-slate-200 hover:border-emerald-300 rounded-xl text-left transition group cursor-pointer active:scale-95">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800 group-hover:text-emerald-700">Kasir POS</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono font-bold">KSR</span>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-0.5">Penjualan & struk kasir</p>
                        </button>
                        <button type="button" onclick="quickFill('owner', '123')" class="p-2.5 bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-300 rounded-xl text-left transition group cursor-pointer active:scale-95">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800 group-hover:text-indigo-700">Owner Cafe</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-200 font-mono font-bold">OWN</span>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-0.5">Laporan & omset eksekutif</p>
                        </button>
                        <button type="button" onclick="quickFill('budi', '123')" class="p-2.5 bg-slate-50 hover:bg-sky-50 border border-slate-200 hover:border-sky-300 rounded-xl text-left transition group cursor-pointer active:scale-95">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800 group-hover:text-sky-700">Member Pelanggan</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-sky-50 text-sky-700 border border-sky-200 font-mono font-bold">MBR</span>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-0.5">Kartu poin & klaim reward</p>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Footer Notice -->
        <div class="text-center text-xs text-slate-400 mt-5">
            &copy; {{ date('Y') }} MyCafe POS & Member Management. Siap produksi.
        </div>
    </div>

    <script>
        function togglePassVisibility() {
            const input = document.getElementById('password');
            const icon = document.getElementById('pass-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility';
            }
        }

        function quickFill(user, pass) {
            const idInput = document.getElementById('identifier');
            const passInput = document.getElementById('password');
            if (idInput) {
                idInput.value = user;
                idInput.focus();
            }
            if (passInput) passInput.value = pass;
        }
    </script>
</body>
</html>
