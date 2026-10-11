@extends('layouts.app')

@section('title', 'Tambah Member Baru - MyCafe')

@section('content')
<!-- Main Content -->
<main class="flex-1 flex flex-col min-w-0 min-h-screen lg:h-screen lg:overflow-y-auto">
    @if ($isStaff)
        @include('layouts.topbar', [
            'pageTitle' => 'Tambah Member Baru',
            'pageSubtitle' => 'Daftarkan pelanggan ke program loyalitas MyCafe dan terbitkan kartu membership.',
            'topbarActions' => [
                ['url' => route('members.index'), 'label' => 'Kembali ke Data Member', 'icon' => 'arrow_back', 'class' => 'px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-subtle']
            ]
        ])
    @else
        <!-- Simple Header for Self Registration -->
        <header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between">
            <a href="{{ route('login') }}" class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-primary text-white flex items-center justify-center font-bold shadow-sm">
                    <span class="material-icons text-lg">local_cafe</span>
                </div>
                <span class="font-black text-lg tracking-tight text-slate-900">My<span class="text-primary">Cafe</span></span>
            </a>
            <a href="{{ route('login') }}" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                <span>Sudah punya akun? Masuk</span>
                <span class="material-icons text-sm">arrow_forward</span>
            </a>
        </header>
    @endif

    <div class="p-4 sm:p-6 lg:p-8 space-y-6 flex-1 max-w-5xl mx-auto w-full">
        <!-- Action / Title Bar -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Tambah Member Baru</h1>
                <p class="text-xs text-slate-500 mt-0.5">Daftarkan pelanggan ke program loyalitas MyCafe dan terbitkan kartu membership digital.</p>
            </div>
            @if (!$isStaff)
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-primary/10 text-primary border border-primary/20">
                Registrasi Member Mandiri
            </span>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Area (2 cols) -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-subtle overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-icons text-primary">person_add</span>
                        <h2 class="font-bold text-sm text-slate-900">Formulir Pendaftaran Member</h2>
                    </div>
                    <span class="text-[11px] text-slate-400 font-mono">Kode: MBR-Otomatis</span>
                </div>

                <form action="{{ route('members.store') }}" method="POST" class="p-5 sm:p-6 space-y-4">
                    @csrf

                    <div>
                        <label for="nama" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <input id="nama" name="nama" type="text" required oninput="updateLivePreview()"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                            placeholder="Contoh: gajah / Rian Pratama"
                            value="{{ old('nama') }}"/>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Nomor HP / WhatsApp <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-semibold text-xs font-mono">+62</span>
                                <input id="phone" name="phone" type="tel" required oninput="updateLivePreview()"
                                    class="w-full pl-12 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                                    placeholder="81234567890"
                                    value="{{ old('phone') }}"/>
                            </div>
                        </div>
                        <div>
                            <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Alamat Email <span class="text-slate-400 font-normal">(Opsional)</span>
                            </label>
                            <input id="email" name="email" type="email"
                                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                                placeholder="rian@example.com"
                                value="{{ old('email') }}"/>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                        <div>
                            <label for="username" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Username Akun Login <span class="text-slate-400 font-normal">(Opsional)</span>
                            </label>
                            <input id="username" name="username" type="text"
                                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition font-mono"
                                placeholder="Otomatis dari nama (cth: gajah)"
                                value="{{ old('username') }}"/>
                        </div>
                        <div>
                            <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Kata Sandi Akun <span class="text-slate-400 font-normal">(Default: 123)</span>
                            </label>
                            <input id="password" name="password" type="text"
                                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition font-mono"
                                placeholder="123"
                                value="{{ old('password', '123') }}"/>
                        </div>
                    </div>

                    <div>
                        <label for="level" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Tier Awal Keanggotaan <span class="text-rose-500">*</span>
                        </label>
                        <select id="level" name="level" onchange="updateLivePreview()" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition font-semibold">
                            <option value="bronze" {{ old('level') === 'bronze' ? 'selected' : '' }}>Bronze (Tier Awal Pelanggan Baru)</option>
                            <option value="silver" {{ old('level') === 'silver' ? 'selected' : '' }}>Silver (Diskon Member 5%)</option>
                            <option value="gold" {{ old('level') === 'gold' ? 'selected' : '' }}>Gold (Diskon 10% + Bonus Poin)</option>
                            <option value="platinum" {{ old('level') === 'platinum' ? 'selected' : '' }}>Platinum (VIP Eksklusif 15%)</option>
                        </select>
                    </div>

                    <!-- Direct Login Option -->
                    <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-900">
                            <input type="checkbox" name="auto_login" value="1" checked class="rounded text-primary focus:ring-primary/40"/>
                            <span>Langsung alihkan & masuk ke akun member ini setelah pendaftaran (Auto-Login)</span>
                        </label>
                        <p class="text-[11px] text-slate-600 pl-6">
                            Member dapat login di halaman utama menggunakan <strong>Username</strong>, <strong>No. HP</strong>, <strong>Email</strong>, atau <strong>Kode Member</strong> dengan kata sandi yang ditentukan.
                        </p>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                        <a href="{{ $isStaff ? route('members.index') : route('login') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs font-semibold transition">
                            Batal
                        </a>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-bold shadow-subtle transition active:scale-[0.98] flex items-center gap-2">
                            <span class="material-icons text-base">save</span>
                            <span>Simpan & Terbitkan Kartu</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right Side: Live Digital Card Preview -->
            <div class="space-y-4">
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-subtle space-y-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">Preview Kartu Member</span>
                    
                    <!-- Digital Card -->
                    <div id="card-preview" class="p-5 rounded-2xl bg-gradient-to-br from-amber-700 via-amber-800 to-stone-900 text-white shadow-soft-lg space-y-4 transition-all">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="material-icons text-base text-amber-400">local_cafe</span>
                                    <span class="font-black text-sm tracking-tight">MyCafe Member</span>
                                </div>
                                <p class="text-[10px] text-white/70 uppercase tracking-widest font-mono">VIP Loyalty Card</p>
                            </div>
                            <span id="card-tier-badge" class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-white/20 text-white border border-white/30 font-mono">BRONZE</span>
                        </div>

                        <div class="pt-2">
                            <p class="text-[10px] text-white/70 uppercase tracking-wider">Nama Pelanggan</p>
                            <p id="card-name" class="font-extrabold text-base tracking-tight truncate">Nama Pelanggan</p>
                        </div>

                        <div class="pt-1 flex items-center justify-between border-t border-white/20 text-xs font-mono">
                            <div>
                                <p class="text-[9px] text-white/70 uppercase">Kode Member</p>
                                <p class="font-bold text-amber-300">MBR-OTOMATIS</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[9px] text-white/70 uppercase">Saldo Awal</p>
                                <p class="font-bold text-white">0 pts</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-2">
                        <p class="font-bold text-slate-800">Keuntungan Tier Terpilih:</p>
                        <ul id="tier-perks" class="text-[11px] text-slate-500 space-y-1 list-disc list-inside">
                            <li>Dapatkan 5 poin untuk setiap pembelian menu</li>
                            <li>Klaim promo selamat datang di kasir POS</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@section('scripts')
<script>
    function updateLivePreview() {
        const nama = document.getElementById('nama').value.trim();
        const level = document.getElementById('level').value;
        const cardName = document.getElementById('card-name');
        const cardBadge = document.getElementById('card-tier-badge');
        const cardEl = document.getElementById('card-preview');
        const perksList = document.getElementById('tier-perks');

        cardName.textContent = nama || 'Nama Pelanggan';
        cardBadge.textContent = level.toUpperCase();

        if (level === 'platinum') {
            cardEl.className = 'p-5 rounded-2xl bg-gradient-to-br from-purple-800 via-indigo-900 to-slate-950 text-white shadow-soft-lg space-y-4 transition-all';
            perksList.innerHTML = '<li>Diskon VIP 15% setiap transaksi</li><li>Ekstra double poin reward loyalitas</li><li>Prioritas meja dan event VIP cafe</li>';
        } else if (level === 'gold') {
            cardEl.className = 'p-5 rounded-2xl bg-gradient-to-br from-amber-600 via-yellow-700 to-stone-900 text-white shadow-soft-lg space-y-4 transition-all';
            perksList.innerHTML = '<li>Diskon langsung 10% di kasir</li><li>Bonus 25 poin di hari ulang tahun</li><li>Akses menu seasonal sebelum rilis</li>';
        } else if (level === 'silver') {
            cardEl.className = 'p-5 rounded-2xl bg-gradient-to-br from-slate-600 via-slate-700 to-slate-900 text-white shadow-soft-lg space-y-4 transition-all';
            perksList.innerHTML = '<li>Diskon 5% untuk menu kopi pilihan</li><li>Tukar poin untuk merchandise cafe</li>';
        } else {
            cardEl.className = 'p-5 rounded-2xl bg-gradient-to-br from-amber-700 via-amber-800 to-stone-900 text-white shadow-soft-lg space-y-4 transition-all';
            perksList.innerHTML = '<li>Dapatkan 5 poin untuk setiap menu</li><li>Klaim promo selamat datang di kasir POS</li>';
        }
    }
    document.addEventListener('DOMContentLoaded', updateLivePreview);
</script>
@endsection
