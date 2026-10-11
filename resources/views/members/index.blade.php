@extends('layouts.app')

@section('title', 'Data Member - MyCafe POS System')

@section('content')
<!-- Main Content Area -->
<main class="flex-1 flex flex-col min-w-0 min-h-screen lg:h-screen lg:overflow-y-auto">
    <!-- Topbar -->
    @include('layouts.topbar', [
        'pageTitle' => 'Data Member & Loyalitas',
        'pageSubtitle' => 'Kelola database pelanggan, tier membership, riwayat poin, dan status akun.',
        'topbarActions' => [
            ['url' => route('members.index', array_merge(request()->query(), ['export' => 'csv'])), 'label' => 'Unduh CSV', 'icon' => 'download', 'class' => 'px-3 py-1.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition shadow-subtle'],
            ['url' => route('members.create'), 'label' => 'Tambah Member', 'icon' => 'person_add', 'class' => 'px-3.5 py-1.5 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition shadow-subtle active:scale-95']
        ]
    ])

    <div class="p-4 sm:p-6 lg:p-8 space-y-6 flex-1">
        <!-- Tier Metrics Summary -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <a href="{{ route('members.index') }}" class="p-4 rounded-2xl bg-white border {{ empty($tier) ? 'border-primary ring-1 ring-primary/30' : 'border-slate-200' }} shadow-subtle transition hover:border-primary">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Semua Member</span>
                <p class="text-xl font-black mt-1 text-slate-900 font-mono">{{ number_format($totalRows) }}</p>
            </a>
            <a href="{{ route('members.index', ['tier' => 'bronze']) }}" class="p-4 rounded-2xl bg-white border {{ $tier === 'bronze' ? 'border-amber-500 ring-1 ring-amber-500/30' : 'border-slate-200' }} shadow-subtle transition hover:border-amber-500">
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 block">Bronze</span>
                <p class="text-xl font-black mt-1 text-slate-900 font-mono">{{ number_format($tierCounts['bronze'] ?? 0) }}</p>
            </a>
            <a href="{{ route('members.index', ['tier' => 'silver']) }}" class="p-4 rounded-2xl bg-white border {{ $tier === 'silver' ? 'border-slate-400 ring-1 ring-slate-400/30' : 'border-slate-200' }} shadow-subtle transition hover:border-slate-400">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Silver</span>
                <p class="text-xl font-black mt-1 text-slate-900 font-mono">{{ number_format($tierCounts['silver'] ?? 0) }}</p>
            </a>
            <a href="{{ route('members.index', ['tier' => 'gold']) }}" class="p-4 rounded-2xl bg-white border {{ $tier === 'gold' ? 'border-yellow-500 ring-1 ring-yellow-500/30' : 'border-slate-200' }} shadow-subtle transition hover:border-yellow-500">
                <span class="text-[10px] font-bold uppercase tracking-wider text-yellow-600 block">Gold</span>
                <p class="text-xl font-black mt-1 text-slate-900 font-mono">{{ number_format($tierCounts['gold'] ?? 0) }}</p>
            </a>
            <a href="{{ route('members.index', ['tier' => 'platinum']) }}" class="p-4 rounded-2xl bg-white border {{ $tier === 'platinum' ? 'border-purple-500 ring-1 ring-purple-500/30' : 'border-slate-200' }} shadow-subtle transition hover:border-purple-500">
                <span class="text-[10px] font-bold uppercase tracking-wider text-purple-600 block">Platinum</span>
                <p class="text-xl font-black mt-1 text-slate-900 font-mono">{{ number_format($tierCounts['platinum'] ?? 0) }}</p>
            </a>
            <a href="{{ route('members.index', ['status' => 'active']) }}" class="p-4 rounded-2xl bg-white border {{ (request('status') === 'active') ? 'border-emerald-500 ring-1 ring-emerald-500/30' : 'border-slate-200' }} shadow-subtle transition hover:border-emerald-500">
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 block">Total Aktif</span>
                <p class="text-xl font-black mt-1 text-slate-900 font-mono">{{ number_format($activeCount ?? $members->where('is_active', 1)->count()) }}</p>
            </a>
        </div>

        <!-- Filter & Search Controls -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-subtle">
            <form action="{{ route('members.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3 items-center justify-between">
                <div class="relative flex-1 w-full">
                    <span class="material-icons absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari berdasarkan nama, nomor HP, email, atau kode member MBR-..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition"/>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <select name="tier" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition">
                        <option value="">Semua Tier</option>
                        <option value="bronze" {{ $tier === 'bronze' ? 'selected' : '' }}>Bronze</option>
                        <option value="silver" {{ $tier === 'silver' ? 'selected' : '' }}>Silver</option>
                        <option value="gold" {{ $tier === 'gold' ? 'selected' : '' }}>Gold</option>
                        <option value="platinum" {{ $tier === 'platinum' ? 'selected' : '' }}>Platinum</option>
                    </select>

                    <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs sm:text-sm font-bold transition shadow-subtle flex items-center gap-1.5 shrink-0">
                        <span class="material-icons text-sm">filter_list</span>
                        <span>Filter</span>
                    </button>

                    @if ($search || $tier || request('status'))
                    <a href="{{ route('members.index') }}" class="px-3 py-2 text-slate-500 hover:text-slate-800 text-xs font-semibold rounded-xl bg-slate-100 transition shrink-0" title="Reset filter">
                        Reset
                    </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Main Member Data Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-subtle overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-4">Kode Member</th>
                            <th class="py-3 px-4">Nama Pelanggan</th>
                            <th class="py-3 px-4">Kontak</th>
                            <th class="py-3 px-4">Tier</th>
                            <th class="py-3 px-4">Saldo Poin</th>
                            <th class="py-3 px-4">Total Belanja</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @if ($members->isEmpty())
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto space-y-2">
                                    <span class="material-icons text-4xl text-slate-300">person_off</span>
                                    <p class="font-bold text-slate-700">Tidak ada member ditemukan</p>
                                    <p class="text-xs text-slate-400">Coba kata kunci lain atau daftarkan member baru.</p>
                                    <a href="{{ route('members.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-primary text-white text-xs font-bold rounded-xl shadow-subtle hover:bg-primary-hover transition">
                                        <span class="material-icons text-sm">person_add</span>
                                        <span>Tambah Member</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @else
                            @foreach ($members as $m)
                            <tr class="hover:bg-slate-50/80 transition group">
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <button type="button" onclick="copyCode('{{ $m->member_code }}')" class="font-mono text-xs font-bold text-amber-700 bg-amber-500/10 hover:bg-amber-500/20 px-2 py-1 rounded-lg border border-amber-500/20 transition flex items-center gap-1 active:scale-95" title="Klik untuk salin kode member">
                                        <span>{{ $m->member_code }}</span>
                                        <span class="material-icons text-[12px] opacity-60">content_copy</span>
                                    </button>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 border border-slate-200 flex items-center justify-center text-xs font-bold shrink-0">
                                            {{ strtoupper(substr($m->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('members.show', $m->id) }}" class="font-bold text-slate-900 hover:text-primary transition block">
                                                {{ $m->name }}
                                            </a>
                                            <span class="text-[10px] text-slate-400 font-mono">Join: {{ date('d M Y', strtotime($m->join_date)) }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <p class="font-medium text-slate-800 font-mono text-xs">{{ $m->phone ?: '-' }}</p>
                                    <p class="text-[10px] text-slate-400 truncate max-w-[140px]">{{ $m->email }}</p>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    @php
                                        $tBadge = match(strtolower($m->tier)) {
                                            'platinum' => 'bg-purple-500/10 text-purple-600 border-purple-500/20',
                                            'gold'     => 'bg-yellow-500/10 text-yellow-600 border-yellow-500/20',
                                            'silver'   => 'bg-slate-500/10 text-slate-600 border-slate-500/20',
                                            default    => 'bg-amber-500/10 text-amber-700 border-amber-500/20',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full border {{ $tBadge }}">
                                        {{ strtoupper($m->tier) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5 font-bold font-mono text-slate-900">
                                        <span class="material-icons text-amber-500 text-sm">stars</span>
                                        <span>{{ number_format($m->total_poin) }}</span>
                                        <button type="button" 
                                                data-id="{{ $m->id }}"
                                                data-name="{{ $m->name }}"
                                                data-points="{{ $m->total_poin }}"
                                                onclick="openPointsFromBtn(this)" 
                                                class="text-[10px] text-primary hover:bg-amber-500/10 px-1.5 py-0.5 rounded font-sans font-semibold transition active:scale-95" title="Atur Poin">
                                            +/-
                                        </button>
                                    </div>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap font-mono font-bold text-slate-800">
                                    {{ formatRupiah($m->total_belanja) }}
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $m->is_active ? 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20' : 'bg-slate-500/10 text-slate-500 border border-slate-500/20' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $m->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        {{ $m->is_active ? 'Aktif' : 'Non-aktif' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <!-- View Details -->
                                        <a href="{{ route('members.show', $m->id) }}" class="p-1.5 text-primary hover:bg-amber-500/10 rounded-lg transition" title="Lihat Profil Lengkap">
                                            <span class="material-icons text-base">visibility</span>
                                        </a>
                                        <!-- Edit Member Button (Modal) -->
                                        <button type="button" 
                                                data-id="{{ $m->id }}"
                                                data-name="{{ $m->name }}"
                                                data-phone="{{ $m->phone ?? '' }}"
                                                data-email="{{ $m->email ?? '' }}"
                                                data-tier="{{ strtolower($m->tier ?? 'bronze') }}"
                                                data-active="{{ $m->is_active ? 1 : 0 }}"
                                                onclick="openEditFromBtn(this)" 
                                                class="p-1.5 text-slate-500 hover:text-amber-600 hover:bg-amber-500/10 rounded-lg transition active:scale-95" title="Edit Data Member">
                                            <span class="material-icons text-base">edit</span>
                                        </button>
                                        <!-- Adjust Points Button -->
                                        <button type="button" 
                                                data-id="{{ $m->id }}"
                                                data-name="{{ $m->name }}"
                                                data-points="{{ $m->total_poin }}"
                                                onclick="openPointsFromBtn(this)" 
                                                class="p-1.5 text-slate-500 hover:text-emerald-600 hover:bg-emerald-500/10 rounded-lg transition active:scale-95" title="Tambah / Kurangi Poin">
                                            <span class="material-icons text-base">toll</span>
                                        </button>
                                        <!-- Delete Member Form -->
                                        <form action="{{ route('members.delete') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus member {{ addslashes($m->name) }} secara permanen?')" class="inline">
                                            @csrf
                                            <input type="hidden" name="member_id" value="{{ $m->id }}"/>
                                            <button type="submit" class="p-1.5 text-rose-500 hover:bg-rose-500/10 rounded-lg transition" title="Hapus Member">
                                                <span class="material-icons text-base">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            @if ($totalPages > 1)
            <div class="p-4 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500">
                <p>Menampilkan {{ count($members) }} dari {{ $totalRows }} member</p>
                <div class="flex gap-1">
                    @for ($i = 1; $i <= $totalPages; $i++)
                        @php
                            $mQuery = request()->query();
                            $mQuery['page'] = $i;
                        @endphp
                        <a href="{{ route('members.index', $mQuery) }}" 
                           class="w-8 h-8 flex items-center justify-center rounded-lg font-bold {{ $i === $page ? 'bg-primary text-white shadow-subtle' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                            {{ $i }}
                        </a>
                    @endfor
                </div>
            </div>
            @endif
        </div>
    </div>
</main>

<!-- Edit Member Modal -->
<div id="edit-modal" onclick="if(event.target===this) closeEditModal()" class="hidden fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-soft-lg border border-slate-200 relative animate-toast">
        <div class="flex justify-between items-center pb-3 border-b border-slate-200">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center">
                    <span class="material-icons text-lg">edit</span>
                </div>
                <h3 class="font-extrabold text-base text-slate-900">Edit Data Member</h3>
            </div>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600">
                <span class="material-icons">close</span>
            </button>
        </div>

        <form action="{{ route('members.update') }}" method="POST" class="pt-4 space-y-4">
            @csrf
            <input type="hidden" name="member_id" id="edit-id"/>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap</label>
                <input type="text" name="name" id="edit-name" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500"/>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nomor HP</label>
                <input type="text" name="phone" id="edit-phone" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500"/>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email</label>
                <input type="email" name="email" id="edit-email" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500"/>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tingkat Tier</label>
                <select name="tier" id="edit-tier" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
                    <option value="bronze">Bronze</option>
                    <option value="silver">Silver</option>
                    <option value="gold">Gold</option>
                    <option value="platinum">Platinum</option>
                </select>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_active" id="edit-active" value="1" class="rounded text-primary focus:ring-amber-500"/>
                <label for="edit-active" class="text-xs font-semibold text-slate-700">Member Aktif</label>
            </div>

            <div class="pt-3 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs font-bold transition shadow-subtle">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Adjust Points Modal -->
<div id="points-modal" onclick="if(event.target===this) closePointsModal()" class="hidden fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-soft-lg border border-slate-200 relative animate-toast">
        <div class="flex justify-between items-center pb-3 border-b border-slate-200">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                    <span class="material-icons text-lg">toll</span>
                </div>
                <h3 class="font-extrabold text-base text-slate-900">Atur Poin Member</h3>
            </div>
            <button type="button" onclick="closePointsModal()" class="text-slate-400 hover:text-slate-600">
                <span class="material-icons">close</span>
            </button>
        </div>

        <form action="{{ route('members.adjust-points') }}" method="POST" class="pt-4 space-y-4">
            @csrf
            <input type="hidden" name="member_id" id="points-id"/>

            <div>
                <p class="text-xs text-slate-500">Member:</p>
                <p class="text-sm font-bold text-slate-900 mt-0.5" id="points-member-name">-</p>
                <p class="text-xs text-amber-600 font-mono mt-0.5" id="points-current-val">Saldo saat ini: 0 pts</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jumlah Perubahan Poin</label>
                <input type="number" name="points" id="points-delta" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold font-mono text-slate-900 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500" placeholder="Contoh: 100 atau -50"/>
                <p class="text-[11px] text-slate-400 mt-1">Gunakan angka positif (+) untuk menambah, atau negatif (-) untuk mengurangi.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Keterangan / Alasan</label>
                <input type="text" name="reason" value="Penyesuaian manual admin" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500"/>
            </div>

            <div class="pt-3 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" onclick="closePointsModal()" class="px-4 py-2 border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-subtle">Simpan Poin</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function copyCode(code) {
        if (!code) return;
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(code).then(() => {
                showToast('Kode ' + code + ' berhasil disalin ke clipboard!', 'success');
            }).catch(() => fallbackCopy(code));
        } else {
            fallbackCopy(code);
        }
    }

    function fallbackCopy(text) {
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
            document.execCommand('copy');
            showToast('Kode ' + text + ' berhasil disalin!', 'success');
        } catch (err) {
            showToast('Salin manual: ' + text, 'info');
        }
        document.body.removeChild(textArea);
    }

    function openEditFromBtn(btn) {
        const ds = btn.dataset;
        document.getElementById('edit-id').value = ds.id;
        document.getElementById('edit-name').value = ds.name;
        document.getElementById('edit-phone').value = ds.phone || '';
        document.getElementById('edit-email').value = ds.email || '';
        document.getElementById('edit-tier').value = (ds.tier || 'bronze').toLowerCase();
        document.getElementById('edit-active').checked = parseInt(ds.active) === 1;
        document.getElementById('edit-modal').classList.remove('hidden');
    }

    function openEditModal(m) {
        if (!m) return;
        document.getElementById('edit-id').value = m.id;
        document.getElementById('edit-name').value = m.name;
        document.getElementById('edit-phone').value = m.phone || '';
        document.getElementById('edit-email').value = m.email || '';
        document.getElementById('edit-tier').value = (m.tier || 'bronze').toLowerCase();
        document.getElementById('edit-active').checked = parseInt(m.is_active) === 1;
        document.getElementById('edit-modal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
    }

    function openPointsFromBtn(btn) {
        const ds = btn.dataset;
        openPointsModal(ds.id, ds.name, parseInt(ds.points) || 0);
    }

    function openPointsModal(id, name, curPoints) {
        document.getElementById('points-id').value = id;
        document.getElementById('points-member-name').textContent = name;
        document.getElementById('points-current-val').textContent = 'Saldo saat ini: ' + Number(curPoints).toLocaleString('id-ID') + ' pts';
        document.getElementById('points-delta').value = '';
        document.getElementById('points-modal').classList.remove('hidden');
    }

    function closePointsModal() {
        document.getElementById('points-modal').classList.add('hidden');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeEditModal();
            closePointsModal();
        }
    });
</script>
@endsection
