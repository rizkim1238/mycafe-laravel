@php
    $currentUser = auth()->user();
    $userRole = $currentUser->role ?? 'member';
    $userName = $currentUser->name ?? 'Pengguna';

    $navGroups = [];
    if ($userRole === 'admin') {
        $navGroups = [
            'RINGKASAN' => [
                ['route' => 'admin.dashboard', 'icon' => 'grid_view', 'label' => 'Dashboard Overview'],
            ],
            'OPERASIONAL KASIR' => [
                ['route' => 'pos.index', 'icon' => 'point_of_sale', 'label' => 'Kasir POS', 'badge' => 'LIVE'],
                ['route' => 'transactions.index', 'icon' => 'receipt_long', 'label' => 'Riwayat Transaksi'],
            ],
            'LOYALITAS MEMBER' => [
                ['route' => 'members.index', 'icon' => 'groups', 'label' => 'Data Member', 'active_routes' => ['members.index', 'members.show']],
                ['route' => 'members.create', 'icon' => 'person_add', 'label' => 'Tambah Member'],
                ['route' => 'rewards.manage', 'icon' => 'redeem', 'label' => 'Katalog Reward'],
            ],
            'LAPORAN & EKSEKUTIF' => [
                ['route' => 'reports.business', 'icon' => 'analytics', 'label' => 'Laporan Bisnis'],
                ['route' => 'owner.report', 'icon' => 'insights', 'label' => 'Statistik Owner'],
            ],
        ];
    } elseif ($userRole === 'owner') {
        $navGroups = [
            'EKSEKUTIF' => [
                ['route' => 'owner.report', 'icon' => 'insights', 'label' => 'Statistik Owner'],
                ['route' => 'reports.business', 'icon' => 'analytics', 'label' => 'Laporan Bisnis'],
                ['route' => 'admin.dashboard', 'icon' => 'grid_view', 'label' => 'Dashboard Overview'],
            ],
            'AUDIT & MONITORING' => [
                ['route' => 'members.index', 'icon' => 'groups', 'label' => 'Data Member'],
                ['route' => 'transactions.index', 'icon' => 'receipt_long', 'label' => 'Riwayat Transaksi'],
            ],
        ];
    } elseif ($userRole === 'kasir') {
        $navGroups = [
            'KASIR POS' => [
                ['route' => 'pos.index', 'icon' => 'point_of_sale', 'label' => 'Kasir POS', 'badge' => 'AKTIF'],
                ['route' => 'transactions.index', 'icon' => 'receipt_long', 'label' => 'Riwayat Transaksi'],
            ],
            'PELANGGAN' => [
                ['route' => 'members.index', 'icon' => 'groups', 'label' => 'Data Member'],
                ['route' => 'members.create', 'icon' => 'person_add', 'label' => 'Tambah Member'],
                ['route' => 'rewards.catalog', 'icon' => 'redeem', 'label' => 'Katalog Reward'],
            ],
        ];
    } else { // member
        $navGroups = [
            'AKUN MEMBER' => [
                ['route' => 'member.profile', 'icon' => 'badge', 'label' => 'Kartu Member & Poin'],
                ['route' => 'transactions.index', 'icon' => 'receipt_long', 'label' => 'Riwayat Belanja'],
            ],
            'REWARD LOYALITAS' => [
                ['route' => 'rewards.catalog', 'icon' => 'redeem', 'label' => 'Tukar Poin Reward'],
            ],
        ];
    }

    $roleBadges = [
        'admin'  => ['label' => 'Admin System', 'bg' => 'bg-amber-50 text-amber-700 border-amber-200'],
        'owner'  => ['label' => 'Owner',        'bg' => 'bg-indigo-50 text-indigo-700 border-indigo-200'],
        'kasir'  => ['label' => 'Kasir Utama',   'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
        'member' => ['label' => 'Member VIP',    'bg' => 'bg-sky-50 text-sky-700 border-sky-200'],
    ];
    $badge = $roleBadges[$userRole] ?? ['label' => ucfirst($userRole), 'bg' => 'bg-slate-100 text-slate-700 border-slate-200'];
@endphp

<!-- Mobile Backdrop -->
<div id="sidebar-backdrop" onclick="toggleSidebar()" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-40 lg:hidden transition-opacity"></div>

<!-- Desktop & Offcanvas Sidebar -->
<aside id="app-sidebar" class="-translate-x-full lg:translate-x-0 fixed lg:sticky top-0 inset-y-0 left-0 w-64 bg-white border-r border-slate-200 text-slate-700 z-50 flex flex-col h-screen shrink-0 transition-transform duration-200 ease-in-out">
    <!-- Brand Header -->
    <div class="p-5 border-b border-slate-100 flex items-center justify-between shrink-0">
        <a href="{{ route('home') }}" class="flex items-center gap-3 group">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-amber-700 text-white flex items-center justify-center shadow-soft group-hover:scale-105 transition-transform">
                <span class="material-icons text-xl">local_cafe</span>
            </div>
            <div>
                <div class="flex items-center gap-1.5">
                    <span class="font-black text-lg tracking-tight text-slate-900">My<span class="text-primary">Cafe</span></span>
                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono">PRO</span>
                </div>
                <span class="text-[10px] tracking-wider text-slate-400 uppercase font-semibold block">POS & Loyalty System</span>
            </div>
        </a>
        <button type="button" onclick="toggleSidebar()" class="lg:hidden p-1.5 text-slate-400 hover:text-slate-900 rounded-lg hover:bg-slate-100 transition">
            <span class="material-icons text-xl">close</span>
        </button>
    </div>

    <!-- Live Status Banner -->
    <div class="px-5 py-2.5 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between text-[11px] shrink-0">
        <div class="flex items-center gap-2 text-slate-600">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>
            <span class="font-medium">Laravel Engine Online</span>
        </div>
        <span class="font-mono text-[10px] text-slate-400 font-semibold">v2.0</span>
    </div>

    <!-- Navigation Scroll Area -->
    <div class="flex-1 overflow-y-auto custom-scrollbar p-3.5 space-y-5">
        @foreach ($navGroups as $groupLabel => $items)
        <div>
            <div class="px-3 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                {{ $groupLabel }}
            </div>
            <div class="space-y-1">
                @foreach ($items as $item)
                    @php
                        $isActive = request()->routeIs($item['route']);
                        if (!$isActive && isset($item['active_routes'])) {
                            foreach ($item['active_routes'] as $ar) {
                                if (request()->routeIs($ar)) {
                                    $isActive = true;
                                    break;
                                }
                            }
                        }
                    @endphp
                    <a href="{{ route($item['route']) }}" 
                       class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition group {{ $isActive 
                            ? 'bg-primary/10 text-primary border border-primary/20 shadow-xs' 
                            : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                        <div class="flex items-center gap-2.5">
                            <span class="material-icons text-lg transition-transform group-hover:scale-110 {{ $isActive ? 'text-primary' : 'text-slate-400 group-hover:text-primary' }}">
                                {{ $item['icon'] }}
                            </span>
                            <span>{{ $item['label'] }}</span>
                        </div>
                        @if (isset($item['badge']))
                        <span class="text-[9px] font-black px-1.5 py-0.5 rounded uppercase font-mono {{ $isActive ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                            {{ $item['badge'] }}
                        </span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    <!-- Bottom User Footer -->
    @php
        $userProfileRoute = match($userRole) {
            'admin' => route('admin.dashboard'),
            'owner' => route('owner.report'),
            'kasir' => route('pos.index'),
            default => route('member.profile'),
        };
    @endphp
    <div class="p-3.5 border-t border-slate-200 bg-slate-50/80 flex items-center justify-between gap-2 shrink-0">
        <a href="{{ $userProfileRoute }}" class="flex items-center gap-2.5 min-w-0 flex-1 hover:opacity-85 transition group" title="Lihat Profil">
            <div class="w-9 h-9 rounded-xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center font-black text-xs shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                {{ strtoupper(substr($userName, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="text-xs font-bold text-slate-900 truncate group-hover:text-primary transition-colors">{{ $userName }}</p>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded border {{ $badge['bg'] }}">
                        {{ $badge['label'] }}
                    </span>
                </div>
            </div>
        </a>

        <div class="flex items-center shrink-0">
            <a href="{{ route('logout') }}" onclick="return confirm('Apakah Anda yakin ingin keluar?')" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition" title="Keluar">
                <span class="material-icons text-lg">logout</span>
            </a>
        </div>
    </div>
</aside>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('app-sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        if (!sidebar) return;
        const isOpen = !sidebar.classList.contains('-translate-x-full');
        if (isOpen) {
            sidebar.classList.add('-translate-x-full');
            if (backdrop) backdrop.classList.add('hidden');
        } else {
            sidebar.classList.remove('-translate-x-full');
            if (backdrop) backdrop.classList.remove('hidden');
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const sidebar = document.getElementById('app-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            if (sidebar && !sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.add('-translate-x-full');
                if (backdrop) backdrop.classList.add('hidden');
            }
        }
    });
</script>
