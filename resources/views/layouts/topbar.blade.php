@php
    $pageTitle = $pageTitle ?? 'Dashboard';
    $pageSubtitle = $pageSubtitle ?? 'Sistem Manajemen Loyalitas & Kasir MyCafe';
    $currentUser = auth()->user();
    $role = $currentUser->role ?? 'member';
    $profileRoute = match($role) {
        'admin' => route('admin.dashboard'),
        'owner' => route('owner.report'),
        'kasir' => route('pos.index'),
        default => route('member.profile'),
    };
@endphp
<header class="bg-white border-b border-slate-200 px-4 sm:px-6 py-3.5 flex flex-col md:flex-row md:items-center justify-between gap-3 shrink-0 transition-colors relative z-20">
    <div class="flex items-center gap-3 min-w-0">
        <!-- Mobile Sidebar Toggle -->
        <button type="button" onclick="toggleSidebar()" class="lg:hidden p-2 -ml-1 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition shrink-0" aria-label="Buka menu navigasi" title="Menu Navigasi">
            <span class="material-icons text-xl">menu</span>
        </button>

        <div class="min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-lg sm:text-xl font-black tracking-tight text-slate-900 truncate">{{ $pageTitle }}</h1>
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 shrink-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Aktif</span>
                </span>
            </div>
            <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5 truncate">{{ $pageSubtitle }}</p>
        </div>
    </div>

    <div class="flex items-center gap-2 flex-wrap self-end md:self-auto shrink-0">
        <!-- Live Clock Pill -->
        <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-600 text-xs font-semibold shadow-subtle select-none">
            <span class="material-icons text-sm text-primary">schedule</span>
            <span id="topbar-clock" class="font-mono">{{ date('d M Y') }}</span>
        </div>

        <!-- Direct Actions if provided -->
        @if (isset($topbarActions) && is_array($topbarActions))
            @foreach ($topbarActions as $action)
                @if (!empty($action['onclick']))
                    <button type="button" onclick="{{ $action['onclick'] }}" 
                            class="{{ $action['class'] ?? 'px-3.5 py-1.5 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs font-bold transition shadow-subtle flex items-center gap-1.5 active:scale-95 cursor-pointer' }}">
                        @if (!empty($action['icon']))
                            <span class="material-icons text-sm">{{ $action['icon'] }}</span>
                        @endif
                        <span>{{ $action['label'] }}</span>
                    </button>
                @else
                    <a href="{{ $action['url'] ?? '#' }}" 
                       class="{{ $action['class'] ?? 'px-3.5 py-1.5 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs font-bold transition shadow-subtle flex items-center gap-1.5 active:scale-95 cursor-pointer' }}">
                        @if (!empty($action['icon']))
                            <span class="material-icons text-sm">{{ $action['icon'] }}</span>
                        @endif
                        <span>{{ $action['label'] }}</span>
                    </a>
                @endif
            @endforeach
        @endif

        <!-- Interactive User Dropdown -->
        @if ($currentUser)
        <div class="relative">
            <button type="button" id="topbar-user-btn" onclick="toggleUserDropdown(event)" 
                    class="flex items-center gap-2 p-1.5 pl-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-bold transition shadow-subtle group cursor-pointer"
                    aria-expanded="false" aria-haspopup="true" title="Menu Profil Pengguna">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-800 hidden sm:inline-block max-w-[120px] truncate">
                    {{ $currentUser->name }}
                </span>
                <span class="text-[9px] px-1.5 py-0.5 rounded font-black uppercase tracking-wider font-mono bg-amber-500/10 text-amber-700 border border-amber-500/20">
                    {{ strtoupper($role) }}
                </span>
                <div class="w-7 h-7 rounded-lg bg-primary text-white flex items-center justify-center font-black text-xs shadow-xs group-hover:scale-105 transition-transform">
                    {{ strtoupper(substr($currentUser->name ?? 'U', 0, 1)) }}
                </div>
                <span class="material-icons text-xs text-slate-400 group-hover:text-slate-600 transition-transform" id="topbar-user-arrow">expand_more</span>
            </button>

            <!-- Dropdown Menu -->
            <div id="topbar-user-dropdown" class="hidden absolute right-0 top-full mt-2 w-56 bg-white border border-slate-200 rounded-2xl shadow-soft-lg p-2 z-50 animate-toast text-slate-800">
                <div class="p-2.5 border-b border-slate-100 mb-1">
                    <p class="font-extrabold text-xs text-slate-900 truncate">{{ $currentUser->name }}</p>
                    <p class="text-[11px] text-slate-400 truncate mt-0.5">{{ $currentUser->email ?: ($currentUser->username ?: 'Pengguna') }}</p>
                    <span class="inline-block mt-1 text-[9px] font-bold px-2 py-0.5 rounded-full bg-primary/10 text-primary border border-primary/20 uppercase font-mono">
                        Role: {{ ucfirst($role) }}
                    </span>
                </div>

                <div class="space-y-0.5 text-xs font-semibold">
                    <a href="{{ $profileRoute }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:text-primary hover:bg-amber-50/50 transition">
                        <span class="material-icons text-base text-primary">{{ $role === 'member' ? 'badge' : ($role === 'owner' ? 'insights' : 'dashboard') }}</span>
                        <span>{{ $role === 'member' ? 'Kartu Member & Poin' : ($role === 'owner' ? 'Statistik Owner' : 'Dashboard Overview') }}</span>
                    </a>
                    <a href="{{ route('transactions.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:text-primary hover:bg-amber-50/50 transition">
                        <span class="material-icons text-base text-slate-400">receipt_long</span>
                        <span>Riwayat Transaksi</span>
                    </a>
                    @if (in_array($role, ['admin', 'owner', 'kasir']))
                    <a href="{{ route('pos.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:text-primary hover:bg-amber-50/50 transition">
                        <span class="material-icons text-base text-slate-400">point_of_sale</span>
                        <span>Kasir POS</span>
                    </a>
                    @endif
                    @if (in_array($role, ['admin', 'owner']))
                    <a href="{{ route('reports.business') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:text-primary hover:bg-amber-50/50 transition">
                        <span class="material-icons text-base text-slate-400">analytics</span>
                        <span>Laporan Bisnis</span>
                    </a>
                    @endif
                    @if ($role === 'member')
                    <a href="{{ route('rewards.catalog') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 hover:text-primary hover:bg-amber-50/50 transition">
                        <span class="material-icons text-base text-slate-400">redeem</span>
                        <span>Tukar Poin Reward</span>
                    </a>
                    @endif
                </div>

                <div class="pt-1 mt-1 border-t border-slate-100">
                    <a href="{{ route('logout') }}" onclick="return confirm('Apakah Anda yakin ingin keluar dari akun?')" 
                       class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-rose-600 hover:bg-rose-50 text-xs font-bold transition">
                        <span class="material-icons text-base">logout</span>
                        <span>Keluar (Logout)</span>
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</header>

<script>
    (function() {
        // Topbar Clock
        function updateClock() {
            const el = document.getElementById('topbar-clock');
            if (!el) return;
            const now = new Date();
            const dateStr = now.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            el.textContent = `${dateStr} • ${timeStr}`;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // User Dropdown Handlers
        window.toggleUserDropdown = function(e) {
            e.stopPropagation();
            const dd = document.getElementById('topbar-user-dropdown');
            const arrow = document.getElementById('topbar-user-arrow');
            if (!dd) return;
            const isHidden = dd.classList.contains('hidden');
            if (isHidden) {
                dd.classList.remove('hidden');
                if (arrow) arrow.style.transform = 'rotate(180deg)';
            } else {
                dd.classList.add('hidden');
                if (arrow) arrow.style.transform = 'rotate(0deg)';
            }
        };

        document.addEventListener('click', function(e) {
            const dd = document.getElementById('topbar-user-dropdown');
            const btn = document.getElementById('topbar-user-btn');
            if (dd && !dd.classList.contains('hidden')) {
                if (!dd.contains(e.target) && (!btn || !btn.contains(e.target))) {
                    dd.classList.add('hidden');
                    const arrow = document.getElementById('topbar-user-arrow');
                    if (arrow) arrow.style.transform = 'rotate(0deg)';
                }
            }
        });
    })();
</script>
