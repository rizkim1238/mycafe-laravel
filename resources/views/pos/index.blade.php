@extends('layouts.app')

@section('title', 'Cashier POS - MyCafe POS System')

@section('styles')
<style>
    @media print {
        aside, #pos-left, header, #print-hidden-actions, nav, #global-topbar { display: none !important; }
        #receipt-modal { position: static !important; background: transparent !important; padding: 0 !important; }
        #receipt-modal-content { border: none !important; box-shadow: none !important; width: 100% !important; max-width: 320px !important; margin: 0 auto !important; padding: 0 !important; color: black !important; }
        body { background: white !important; color: black !important; }
    }
</style>
@endsection

@section('content')
<!-- Main POS Workspace -->
<main class="flex-1 flex flex-col min-w-0 min-h-screen lg:h-screen overflow-y-auto lg:overflow-hidden">
    <!-- Top POS Status Bar -->
    <header class="bg-white border-b border-slate-200 px-4 sm:px-6 py-3.5 flex justify-between items-center shrink-0">
        <div class="flex items-center gap-3">
            <!-- Mobile Sidebar Toggle -->
            <button type="button" onclick="toggleSidebar()" class="lg:hidden p-2 -ml-1 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition shrink-0 cursor-pointer" aria-label="Buka menu navigasi" title="Menu Navigasi">
                <span class="material-icons text-xl">menu</span>
            </button>
            <div class="w-9 h-9 rounded-xl bg-primary text-white flex items-center justify-center shadow-subtle">
                <span class="material-icons text-xl">point_of_sale</span>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-900 leading-tight">MyCafe POS Kasir</h1>
                <p class="text-xs text-slate-500">Kasir Bertugas: <strong class="text-slate-700">{{ auth()->user()->name ?? 'Kasir' }}</strong></p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="hidden sm:block text-right">
                <p class="text-xs font-semibold text-slate-800">{{ date('l, d F Y') }}</p>
                <p class="text-[11px] text-slate-400 font-mono" id="live-clock">--:--:-- WIB</p>
            </div>
            <a href="{{ route('transactions.index') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-semibold text-slate-700 transition flex items-center gap-1.5 shadow-subtle">
                <span class="material-icons text-sm">history</span>
                <span>Riwayat Trx</span>
            </a>
        </div>
    </header>

    <!-- POS Split Content -->
    <div class="flex-1 flex flex-col lg:flex-row overflow-y-auto lg:overflow-hidden p-3 sm:p-5 gap-4">
        <!-- Left: Product Catalog -->
        <section id="pos-left" class="flex-1 flex flex-col min-w-0 bg-white rounded-2xl border border-slate-200 shadow-subtle overflow-hidden">
            <!-- Category Tabs & Product Search -->
            <div class="p-4 border-b border-slate-200 space-y-3 shrink-0">
                <div class="relative">
                    <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                    <input type="text" id="product-search" oninput="filterProducts()" placeholder="Cari menu kopi, minuman, atau makanan..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition"/>
                </div>

                <!-- Category Filter Pills -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 custom-scrollbar text-xs">
                    <button type="button" onclick="selectCategory('')" class="cat-pill active px-3.5 py-1.5 rounded-lg font-bold bg-primary text-white transition shrink-0" data-cat="">
                        Semua Menu
                    </button>
                    @foreach ($categories as $cat)
                    <button type="button" onclick="selectCategory('{{ $cat }}')" class="cat-pill px-3.5 py-1.5 rounded-lg font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition shrink-0" data-cat="{{ $cat }}">
                        {{ $cat }}
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- Products Grid -->
            <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
                <div id="products-grid" class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3.5">
                    @foreach ($products as $p)
                    <div class="product-card p-3.5 rounded-xl border border-slate-200 bg-white hover:border-amber-500/50 hover:bg-amber-50/20 transition cursor-pointer flex flex-col justify-between group shadow-subtle hover:shadow-soft relative active:scale-[0.99]"
                         data-id="{{ $p->id }}"
                         data-name="{{ $p->name }}"
                         data-price="{{ $p->price }}"
                         data-category="{{ $p->category }}"
                         data-poin="{{ $p->poin_value }}"
                         onclick="addToCartById({{ $p->id }})">
                        <div>
                            <div class="flex justify-between items-start gap-1 mb-2">
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-amber-500/10 text-amber-700 border border-amber-500/20">
                                    {{ $p->category }}
                                </span>
                                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-500/10 px-1.5 py-0.5 rounded border border-emerald-500/20">
                                    +{{ $p->poin_value }} pts
                                </span>
                            </div>
                            <h3 class="font-bold text-xs sm:text-sm text-slate-800 group-hover:text-primary transition line-clamp-2">
                                {{ $p->name }}
                            </h3>
                        </div>
                        <div class="mt-3 pt-2.5 border-t border-slate-200 flex items-center justify-between">
                            <span class="font-bold text-xs sm:text-sm text-slate-900 font-mono">
                                {{ formatRupiah($p->price) }}
                            </span>
                            <span class="w-6 h-6 rounded-lg bg-primary text-white flex items-center justify-center text-xs font-bold group-hover:scale-105 transition shadow-subtle">
                                +
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Right: Active Cart & Checkout Panel -->
        <section class="w-full lg:w-[420px] xl:w-[460px] shrink-0 flex flex-col bg-white rounded-2xl border border-slate-200 shadow-subtle overflow-y-auto custom-scrollbar">
            <!-- Member Identification Bar -->
            <div class="p-4 border-b border-slate-200 bg-slate-50/70 space-y-2 shrink-0">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Member Pelanggan</span>
                    <button type="button" onclick="setGuestMember()" class="text-[11px] font-semibold text-primary hover:underline">
                        Umum (Non-Member)
                    </button>
                </div>
                <div class="relative">
                    <input type="text" id="member-search-input" oninput="searchMemberDebounce()" placeholder="Ketik No. HP / Nama Member..." class="w-full pl-9 pr-8 py-2 bg-white border border-slate-200 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition"/>
                    <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">person_search</span>
                    <button type="button" onclick="clearMemberSearch()" id="clear-member-btn" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                        <span class="material-icons text-sm">close</span>
                    </button>
                    <!-- Search Member Autocomplete Dropdown -->
                    <div id="member-results-box" class="hidden absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-soft-lg z-30 max-h-48 overflow-y-auto divide-y divide-slate-200"></div>
                </div>

                <!-- Selected Member Card -->
                <div id="selected-member-card" class="hidden p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-between">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center font-bold text-xs shrink-0" id="sel-avatar">B</div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5">
                                <p class="text-xs font-bold text-slate-900 truncate" id="sel-name">Budi</p>
                                <span class="text-[9px] px-1.5 py-0.2 rounded font-bold uppercase bg-primary text-white" id="sel-tier">GOLD</span>
                            </div>
                            <p class="text-[10px] text-slate-500 font-mono" id="sel-code">MBR-0001</p>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-[10px] text-slate-500 block">Saldo Poin</span>
                        <span class="text-xs font-black text-primary font-mono" id="sel-points">1.250 pts</span>
                    </div>
                </div>
            </div>

            <!-- Cart Item List -->
            <div class="flex-1 overflow-y-auto p-4 space-y-2.5 custom-scrollbar" id="cart-items-container">
                <div id="cart-empty-message" class="py-12 text-center text-slate-400">
                    <span class="material-icons text-4xl text-slate-300 mb-1">shopping_basket</span>
                    <p class="text-xs font-semibold">Keranjang masih kosong</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Pilih menu di sebelah kiri untuk menambahkan pesanan.</p>
                </div>
            </div>

            <!-- Redeem Points Toggle (If member selected) -->
            <div id="redeem-box" class="hidden px-4 py-2.5 bg-amber-500/10 border-t border-b border-amber-500/20 text-xs shrink-0">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="flex items-center gap-2 cursor-pointer font-semibold text-amber-800">
                        <input type="checkbox" id="redeem-check" onchange="toggleRedeem()" class="rounded text-primary focus:ring-amber-500"/>
                        <span>Tukarkan Poin untuk Diskon</span>
                    </label>
                    <span class="text-[10px] text-slate-400 font-mono">1 Poin = Rp 50</span>
                </div>
                <div id="redeem-input-wrapper" class="hidden flex items-center gap-2 mt-1">
                    <span class="text-[11px] text-slate-500">Gunakan:</span>
                    <input type="number" id="redeem-amount" oninput="calculateTotals()" min="0" value="0" class="w-24 px-2 py-1 bg-white border border-slate-200 rounded-lg text-xs font-bold text-primary font-mono"/>
                    <span class="text-[11px] text-slate-500">poin</span>
                    <button type="button" onclick="useMaxPoints()" class="text-[10px] font-bold text-primary hover:underline ml-auto">Gunakan Max</button>
                </div>
            </div>

            <!-- Summary & Checkout Controls -->
            <div class="p-4 border-t border-slate-200 bg-slate-50/50 space-y-3 shrink-0">
                <div class="space-y-1.5 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal Pesanan</span>
                        <span class="font-mono font-semibold" id="summary-subtotal">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-rose-500 font-semibold" id="row-diskon" style="display: none;">
                        <span>Diskon Poin</span>
                        <span class="font-mono" id="summary-diskon">- Rp 0</span>
                    </div>
                    <div class="flex justify-between text-emerald-600 font-semibold">
                        <span>Estimasi Poin Didapat</span>
                        <span class="font-mono" id="summary-poin">+0 pts</span>
                    </div>
                    <div class="flex justify-between text-base font-extrabold text-slate-900 pt-2 border-t border-slate-200">
                        <span>Total Bayar</span>
                        <span class="text-primary font-mono text-lg" id="summary-total">Rp 0</span>
                    </div>
                </div>

                <!-- Payment Method Selection -->
                <div class="space-y-2">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Metode Pembayaran</label>
                    <div class="grid grid-cols-3 gap-1.5 text-xs font-semibold">
                        <label class="pay-btn p-2 rounded-xl border border-primary bg-primary/10 text-primary cursor-pointer text-center flex items-center justify-center gap-1.5 transition select-none">
                            <input type="radio" name="payment_method" value="cash" checked class="hidden" onchange="changePayment(this)"/>
                            <span class="material-icons text-sm">payments</span>
                            <span>Tunai</span>
                        </label>
                        <label class="pay-btn p-2 rounded-xl border border-slate-200 text-slate-600 cursor-pointer text-center flex items-center justify-center gap-1.5 transition select-none">
                            <input type="radio" name="payment_method" value="qris" class="hidden" onchange="changePayment(this)"/>
                            <span class="material-icons text-sm">qr_code_scanner</span>
                            <span>QRIS</span>
                        </label>
                        <label class="pay-btn p-2 rounded-xl border border-slate-200 text-slate-600 cursor-pointer text-center flex items-center justify-center gap-1.5 transition select-none">
                            <input type="radio" name="payment_method" value="debit" class="hidden" onchange="changePayment(this)"/>
                            <span class="material-icons text-sm">credit_card</span>
                            <span>Debit</span>
                        </label>
                    </div>

                    <!-- Cash Payment Panel -->
                    <div id="cash-panel" class="p-3 rounded-xl bg-slate-100/70 border border-slate-200 space-y-2.5 transition">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Uang Diterima</span>
                            <span id="cash-status" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">Uang Pas</span>
                        </div>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">Rp</span>
                            <input type="number" id="cash-tendered" oninput="calculateChange()" placeholder="0" class="w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-900 font-mono focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500"/>
                        </div>
                        <!-- Quick Cash Presets -->
                        <div class="grid grid-cols-4 gap-1.5 text-[11px] font-bold">
                            <button type="button" onclick="setCashAmount('exact')" class="py-1.5 px-1 rounded-lg bg-white border border-slate-200 hover:border-primary text-slate-700 hover:text-primary transition shadow-subtle text-center">Pas</button>
                            <button type="button" onclick="setCashAmount(20000)" class="py-1.5 px-1 rounded-lg bg-white border border-slate-200 hover:border-primary text-slate-700 hover:text-primary transition shadow-subtle text-center">20k</button>
                            <button type="button" onclick="setCashAmount(50000)" class="py-1.5 px-1 rounded-lg bg-white border border-slate-200 hover:border-primary text-slate-700 hover:text-primary transition shadow-subtle text-center">50k</button>
                            <button type="button" onclick="setCashAmount(100000)" class="py-1.5 px-1 rounded-lg bg-white border border-slate-200 hover:border-primary text-slate-700 hover:text-primary transition shadow-subtle text-center">100k</button>
                        </div>
                        <!-- Kembalian Calculation -->
                        <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-xs">
                            <span class="font-semibold text-slate-500">Kembalian:</span>
                            <span id="cash-change" class="font-bold font-mono text-sm text-emerald-600">Rp 0</span>
                        </div>
                    </div>

                    <!-- QRIS Info Panel -->
                    <div id="qris-panel" class="hidden p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 text-center">
                        <div class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-500 mx-auto flex items-center justify-center mb-1">
                            <span class="material-icons text-base">qr_code_scanner</span>
                        </div>
                        <p class="text-xs font-bold text-amber-800">Tampilkan Kode QRIS ke Pelanggan</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Dapat dibayar via GoPay, BCA, Mandiri, OVO, ShopeePay.</p>
                    </div>

                    <!-- Debit Info Panel -->
                    <div id="debit-panel" class="hidden p-3 rounded-xl bg-sky-500/10 border border-sky-500/20 text-center">
                        <div class="w-8 h-8 rounded-lg bg-sky-500/20 text-sky-500 mx-auto flex items-center justify-center mb-1">
                            <span class="material-icons text-base">credit_card</span>
                        </div>
                        <p class="text-xs font-bold text-sky-800">Gesek Kartu Debit/Kredit pada Mesin EDC</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Pastikan approval slip telah dicetak oleh mesin EDC.</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2 pt-1">
                    <button type="button" onclick="resetCart()" class="px-3.5 py-3 border border-slate-200 text-slate-500 hover:text-rose-500 rounded-xl text-xs font-bold transition hover:bg-slate-100" title="Kosongkan Keranjang">
                        <span class="material-icons text-base">delete_sweep</span>
                    </button>
                    <button type="button" onclick="submitTransaction()" id="btn-submit-trx" class="flex-1 py-3.5 bg-primary hover:bg-primary-hover text-white rounded-xl text-sm font-bold shadow-soft transition active:scale-[0.98] flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span class="material-icons text-lg">check_circle</span>
                        <span>PROSES TRANSAKSI</span>
                    </button>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Thermal Receipt Print Modal -->
<div id="receipt-modal" onclick="if(event.target===this) closeReceiptAndNew()" class="hidden fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div id="receipt-modal-content" class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-soft-lg border border-slate-200 relative text-slate-900">
        <button type="button" onclick="closeReceiptAndNew()" class="no-print absolute right-4 top-4 p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition cursor-pointer" title="Tutup">
            <span class="material-icons text-xl">close</span>
        </button>
        <div class="text-center pb-3 border-b border-dashed border-slate-300">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-primary flex items-center justify-center mx-auto mb-1.5">
                <span class="material-icons text-xl">local_cafe</span>
            </div>
            <h3 class="font-black text-xl text-slate-900 tracking-tight">MyCafe POS</h3>
            <p class="text-[11px] text-slate-500">Struk Pembayaran Resmi Cafe</p>
            <p class="text-[10px] font-mono text-slate-500 mt-1" id="rec-inv">TRX-0000</p>
            <p class="text-[10px] text-slate-500" id="rec-date">01 Jan 2026</p>
        </div>

        <div class="py-2.5 text-xs space-y-1 font-mono text-slate-600 border-b border-dashed border-slate-300">
            <div class="flex justify-between">
                <span>Kasir:</span>
                <span class="font-bold">{{ auth()->user()->name ?? 'Kasir' }}</span>
            </div>
            <div class="flex justify-between">
                <span>Pelanggan:</span>
                <span id="rec-member" class="font-bold">Pelanggan Umum</span>
            </div>
        </div>

        <!-- Itemized Receipt -->
        <div class="py-3 max-h-48 overflow-y-auto divide-y divide-slate-100 text-xs font-mono" id="rec-items"></div>

        <div class="pt-2.5 space-y-1 text-xs font-mono border-t border-dashed border-slate-300">
            <div class="flex justify-between text-slate-600">
                <span>Subtotal:</span>
                <span id="rec-subtotal">Rp 0</span>
            </div>
            <div class="flex justify-between text-rose-600" id="rec-diskon-row" style="display: none;">
                <span>Diskon Poin:</span>
                <span id="rec-diskon">- Rp 0</span>
            </div>
            <div class="flex justify-between font-bold text-sm text-slate-900 pt-1 border-t border-slate-200">
                <span>TOTAL BAYAR:</span>
                <span id="rec-total" class="font-black">Rp 0</span>
            </div>
            <div class="flex justify-between text-emerald-700 font-bold pt-1">
                <span>Poin Loyalitas Didapat:</span>
                <span id="rec-poin">+0 pts</span>
            </div>
        </div>

        <div id="print-hidden-actions" class="mt-5 flex gap-2">
            <button type="button" onclick="window.print()" class="flex-1 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-subtle">
                <span class="material-icons text-base">print</span>
                <span>Cetak Struk Thermal</span>
            </button>
            <button type="button" onclick="closeReceiptAndNew()" class="px-4 py-2.5 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs font-bold transition">
                Pesanan Baru
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Live Clock
    function updateClock() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('id-ID') + ' WIB';
        const el = document.getElementById('live-clock');
        if (el) el.textContent = timeStr;
    }
    setInterval(updateClock, 1000);
    updateClock();

    // State
    let cart = [];
    let selectedMember = null;
    let activeCategory = '';

    // Category Filter
    function selectCategory(cat) {
        activeCategory = cat;
        document.querySelectorAll('.cat-pill').forEach(btn => {
            if (btn.getAttribute('data-cat') === cat) {
                btn.className = 'cat-pill active px-3.5 py-1.5 rounded-lg font-bold bg-primary text-white transition shrink-0';
            } else {
                btn.className = 'cat-pill px-3.5 py-1.5 rounded-lg font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition shrink-0';
            }
        });
        filterProducts();
    }

    function filterProducts() {
        const q = document.getElementById('product-search').value.toLowerCase().trim();
        document.querySelectorAll('.product-card').forEach(card => {
            const name = card.getAttribute('data-name').toLowerCase();
            const cat  = card.getAttribute('data-category');
            const matchCat = (activeCategory === '' || cat === activeCategory);
            const matchQ   = (q === '' || name.includes(q));
            if (matchCat && matchQ) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // Products Map
    const productsCatalog = @json($products->keyBy('id'));

    function addToCartById(id) {
        const prod = productsCatalog[id];
        if (prod) {
            addToCart(prod);
        }
    }

    // Cart Actions
    function addToCart(prod) {
        const existing = cart.find(i => i.id === prod.id);
        if (existing) {
            existing.qty += 1;
        } else {
            cart.push({
                id: prod.id,
                name: prod.name,
                price: parseFloat(prod.price),
                poin_value: parseInt(prod.poin_value) || 0,
                qty: 1
            });
        }
        renderCart();
        showToast(`+1 ${prod.name} ditambahkan ke keranjang`, 'info');
    }

    function updateQty(id, delta) {
        const item = cart.find(i => i.id === id);
        if (!item) return;
        item.qty += delta;
        if (item.qty <= 0) {
            cart = cart.filter(i => i.id !== id);
        }
        renderCart();
    }

    function removeFromCart(id) {
        const item = cart.find(i => i.id === id);
        const name = item ? item.name : 'Item';
        cart = cart.filter(i => i.id !== id);
        renderCart();
        showToast(`${name} dihapus dari keranjang`, 'info');
    }

    function resetCart() {
        if (cart.length === 0) return;
        cart = [];
        renderCart();
        showToast('Keranjang belanja telah dikosongkan', 'info');
    }

    function renderCart() {
        const container = document.getElementById('cart-items-container');
        const emptyMsg = document.getElementById('cart-empty-message');

        document.querySelectorAll('.product-card').forEach(card => {
            const pId = parseInt(card.getAttribute('data-id'));
            const inCart = cart.find(i => i.id === pId);
            let badge = card.querySelector('.cart-count-badge');
            if (inCart && inCart.qty > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'cart-count-badge absolute -top-2 -right-2 bg-primary text-white text-[10px] font-black w-6 h-6 rounded-full flex items-center justify-center shadow-subtle ring-2 ring-white';
                    card.appendChild(badge);
                }
                badge.textContent = inCart.qty + 'x';
                card.classList.add('ring-2', 'ring-primary/40');
            } else {
                if (badge) badge.remove();
                card.classList.remove('ring-2', 'ring-primary/40');
            }
        });

        if (cart.length === 0) {
            container.innerHTML = '';
            container.appendChild(emptyMsg);
            emptyMsg.style.display = 'block';
        } else {
            emptyMsg.style.display = 'none';
            let html = '';
            cart.forEach(item => {
                html += `
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-2 text-xs transition">
                        <div class="min-w-0 flex-1">
                            <h4 class="font-bold text-slate-800 truncate">${item.name}</h4>
                            <p class="text-[11px] text-slate-400 font-mono">Rp ${item.price.toLocaleString('id-ID')} &bull; +${item.poin_value * item.qty} pts</p>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <button type="button" onclick="updateQty(${item.id}, -1)" class="w-7 h-7 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold flex items-center justify-center transition active:scale-95 cursor-pointer select-none" title="Kurangi qty">-</button>
                            <span class="w-6 text-center font-bold text-slate-900 font-mono select-none">${item.qty}</span>
                            <button type="button" onclick="updateQty(${item.id}, 1)" class="w-7 h-7 rounded-lg bg-primary hover:bg-primary-hover text-white font-bold flex items-center justify-center transition active:scale-95 cursor-pointer select-none" title="Tambah qty">+</button>
                            <button type="button" onclick="removeFromCart(${item.id})" class="ml-1 p-1 text-slate-400 hover:text-rose-500 rounded-lg hover:bg-rose-50 transition cursor-pointer" title="Hapus menu ini">
                                <span class="material-icons text-base">delete</span>
                            </button>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        }

        calculateTotals();
    }

    function calculateTotals() {
        let subtotal = 0;
        let poinEarned = 0;
        cart.forEach(i => {
            subtotal += i.price * i.qty;
            poinEarned += (i.poin_value || 0) * i.qty;
        });

        let diskon = 0;
        const redeemCheck = document.getElementById('redeem-check');
        const redeemAmountInput = document.getElementById('redeem-amount');
        let poinRedeem = parseInt(redeemAmountInput.value) || 0;

        if (selectedMember && redeemCheck.checked && poinRedeem > 0) {
            const maxAvailable = selectedMember.total_poin;
            if (poinRedeem > maxAvailable) {
                poinRedeem = maxAvailable;
                redeemAmountInput.value = maxAvailable;
            }
            diskon = Math.min(poinRedeem * 50, subtotal);
        }

        const total = Math.max(0, subtotal - diskon);

        document.getElementById('summary-subtotal').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
        document.getElementById('summary-poin').textContent = '+' + poinEarned + ' pts';
        document.getElementById('summary-total').textContent = 'Rp ' + total.toLocaleString('id-ID');

        const diskonRow = document.getElementById('row-diskon');
        if (diskon > 0) {
            diskonRow.style.display = 'flex';
            document.getElementById('summary-diskon').textContent = '- Rp ' + diskon.toLocaleString('id-ID');
        } else {
            diskonRow.style.display = 'none';
        }

        calculateChange();
    }

    function setCashAmount(val) {
        let total = getCurrentTotal();
        const input = document.getElementById('cash-tendered');
        if (val === 'exact') {
            if (total === 0) {
                showToast('Keranjang masih kosong, pilih menu terlebih dahulu.', 'info');
                return;
            }
            input.value = total;
        } else {
            input.value = val;
        }
        calculateChange();
    }

    function getCurrentTotal() {
        const txt = document.getElementById('summary-total').textContent;
        return parseInt(txt.replace(/\D/g, '')) || 0;
    }

    function calculateChange() {
        const total = getCurrentTotal();
        const tenderedInput = document.getElementById('cash-tendered');
        const tendered = parseInt(tenderedInput.value) || 0;
        const changeEl = document.getElementById('cash-change');
        const statusEl = document.getElementById('cash-status');

        if (tendered === 0 && total === 0) {
            changeEl.textContent = 'Rp 0';
            changeEl.className = 'font-bold font-mono text-sm text-slate-500';
            statusEl.textContent = 'Menunggu Input';
            statusEl.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600';
            return;
        }

        const diff = tendered - total;
        if (diff >= 0) {
            changeEl.textContent = 'Rp ' + diff.toLocaleString('id-ID');
            changeEl.className = 'font-bold font-mono text-sm text-emerald-600';
            if (diff === 0) {
                statusEl.textContent = 'Uang Pas';
                statusEl.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700';
            } else {
                statusEl.textContent = 'Kembalian Siap';
                statusEl.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-sky-100 text-sky-700';
            }
        } else {
            changeEl.textContent = 'Kurang Rp ' + Math.abs(diff).toLocaleString('id-ID');
            changeEl.className = 'font-bold font-mono text-sm text-rose-500';
            statusEl.textContent = 'Nominal Kurang';
            statusEl.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-100 text-rose-700';
        }
    }

    let searchTimeout = null;
    function searchMemberDebounce() {
        clearTimeout(searchTimeout);
        const q = document.getElementById('member-search-input').value.trim();
        const btn = document.getElementById('clear-member-btn');
        const box = document.getElementById('member-results-box');

        if (q.length === 0) {
            btn.classList.add('hidden');
            box.classList.add('hidden');
            return;
        }
        btn.classList.remove('hidden');

        searchTimeout = setTimeout(() => {
            fetchMemberSearch(q);
        }, 250);
    }

    function fetchMemberSearch(q) {
        const formData = new FormData();
        formData.append('q', q);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route("pos.search-member") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            const box = document.getElementById('member-results-box');
            box.innerHTML = '';
            if (res.success && res.data.length > 0) {
                res.data.forEach(m => {
                    const item = document.createElement('div');
                    item.className = "p-2.5 hover:bg-amber-500/10 cursor-pointer flex justify-between items-center transition text-xs select-none";
                    item.innerHTML = `
                        <div>
                            <p class="font-bold text-slate-800">${m.name}</p>
                            <p class="text-[10px] text-slate-400 font-mono">${m.member_code} &bull; ${m.phone || '-'}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-bold text-primary uppercase">${m.tier}</span>
                            <span class="text-[10px] text-slate-500 block font-mono">${m.total_poin} pts</span>
                        </div>
                    `;
                    item.addEventListener('click', () => selectMember(m));
                    box.appendChild(item);
                });
                box.classList.remove('hidden');
            } else {
                box.innerHTML = '<div class="p-3 text-center text-xs text-slate-400">Tidak ada member ditemukan</div>';
                box.classList.remove('hidden');
            }
        })
        .catch(err => console.error(err));
    }

    function selectMember(m) {
        selectedMember = m;
        document.getElementById('member-results-box').classList.add('hidden');
        document.getElementById('member-search-input').value = m.name;

        document.getElementById('sel-name').textContent = m.name;
        document.getElementById('sel-code').textContent = m.member_code;
        document.getElementById('sel-tier').textContent = (m.tier || 'BRONZE').toUpperCase();
        document.getElementById('sel-points').textContent = m.total_poin.toLocaleString('id-ID') + ' pts';
        document.getElementById('sel-avatar').textContent = m.name.charAt(0).toUpperCase();

        document.getElementById('selected-member-card').classList.remove('hidden');

        if (m.total_poin > 0) {
            document.getElementById('redeem-box').classList.remove('hidden');
        } else {
            document.getElementById('redeem-box').classList.add('hidden');
        }

        calculateTotals();
        showToast(`Member ${m.name} dipilih (${m.tier.toUpperCase()})`, 'success');
    }

    function setGuestMember() {
        clearMemberSearch();
        showToast('Diatur sebagai Pelanggan Umum (Non-Member)', 'info');
    }

    function clearMemberSearch() {
        selectedMember = null;
        document.getElementById('member-search-input').value = '';
        document.getElementById('clear-member-btn').classList.add('hidden');
        document.getElementById('member-results-box').classList.add('hidden');
        document.getElementById('selected-member-card').classList.add('hidden');
        document.getElementById('redeem-box').classList.add('hidden');
        document.getElementById('redeem-check').checked = false;
        document.getElementById('redeem-input-wrapper').classList.add('hidden');
        document.getElementById('redeem-amount').value = 0;
        calculateTotals();
    }

    function toggleRedeem() {
        const checked = document.getElementById('redeem-check').checked;
        const wrap = document.getElementById('redeem-input-wrapper');
        if (checked) {
            wrap.classList.remove('hidden');
            useMaxPoints();
        } else {
            wrap.classList.add('hidden');
            document.getElementById('redeem-amount').value = 0;
            calculateTotals();
        }
    }

    function useMaxPoints() {
        if (!selectedMember) return;
        let subtotal = 0;
        cart.forEach(i => subtotal += i.price * i.qty);
        const maxPointsNeeded = Math.ceil(subtotal / 50);
        const maxAffordable = Math.min(selectedMember.total_poin, maxPointsNeeded);
        document.getElementById('redeem-amount').value = maxAffordable;
        calculateTotals();
    }

    function changePayment(radio) {
        document.querySelectorAll('.pay-btn').forEach(btn => {
            btn.classList.remove('border-primary', 'bg-primary/10', 'text-primary');
            btn.classList.add('border-slate-200', 'text-slate-600');
        });
        const parent = radio.closest('.pay-btn');
        if (parent) {
            parent.classList.add('border-primary', 'bg-primary/10', 'text-primary');
            parent.classList.remove('border-slate-200', 'text-slate-600');
        }

        const val = radio.value;
        const cashPanel = document.getElementById('cash-panel');
        const qrisPanel = document.getElementById('qris-panel');
        const debitPanel = document.getElementById('debit-panel');

        if (cashPanel) cashPanel.classList.toggle('hidden', val !== 'cash');
        if (qrisPanel) qrisPanel.classList.toggle('hidden', val !== 'qris');
        if (debitPanel) debitPanel.classList.toggle('hidden', val !== 'debit');

        if (val === 'cash') {
            calculateChange();
        }
    }

    // Submit Transaction
    function submitTransaction() {
        if (cart.length === 0) {
            showToast('Silakan pilih minimal 1 menu ke dalam keranjang pesanan!', 'error');
            return;
        }

        const payment = document.querySelector('input[name="payment_method"]:checked').value;
        const total = getCurrentTotal();

        if (payment === 'cash') {
            const tendered = parseInt(document.getElementById('cash-tendered').value) || 0;
            if (tendered > 0 && tendered < total) {
                showToast(`Uang tunai kurang Rp ${(total - tendered).toLocaleString('id-ID')}!`, 'error');
                return;
            }
        }

        const btn = document.getElementById('btn-submit-trx');
        btn.disabled = true;
        btn.innerHTML = '<span class="material-icons animate-spin text-lg">sync</span> Memproses...';

        const poinRedeem = document.getElementById('redeem-check').checked ? parseInt(document.getElementById('redeem-amount').value) || 0 : 0;

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('member_id', selectedMember ? selectedMember.id : 0);
        formData.append('payment', payment);
        formData.append('poin_redeemed', poinRedeem);
        formData.append('items', JSON.stringify(cart));

        fetch('{{ route("pos.checkout") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = '<span class="material-icons text-lg">check_circle</span> PROSES TRANSAKSI';

            if (res.success) {
                showToast(`Transaksi ${res.invoice} berhasil disimpan!`, 'success');
                showReceiptModal(res);
            } else {
                showToast('Gagal memproses transaksi: ' + (res.msg || 'Error'), 'error');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<span class="material-icons text-lg">check_circle</span> PROSES TRANSAKSI';
            showToast('Terjadi kesalahan koneksi server.', 'error');
            console.error(err);
        });
    }

    function showReceiptModal(data) {
        document.getElementById('rec-inv').textContent = data.invoice;
        document.getElementById('rec-date').textContent = data.date;
        document.getElementById('rec-member').textContent = selectedMember ? (selectedMember.name + ' (' + selectedMember.member_code + ')') : 'Pelanggan Umum';

        let itemsHtml = '';
        cart.forEach(i => {
            itemsHtml += `
                <div class="py-1 flex justify-between">
                    <span>${i.qty}x ${i.name}</span>
                    <span>Rp ${(i.qty * i.price).toLocaleString('id-ID')}</span>
                </div>
            `;
        });
        document.getElementById('rec-items').innerHTML = itemsHtml;

        document.getElementById('rec-subtotal').textContent = 'Rp ' + data.subtotal.toLocaleString('id-ID');
        if (data.diskon > 0) {
            document.getElementById('rec-diskon-row').style.display = 'flex';
            document.getElementById('rec-diskon').textContent = '- Rp ' + data.diskon.toLocaleString('id-ID');
        } else {
            document.getElementById('rec-diskon-row').style.display = 'none';
        }
        document.getElementById('rec-total').textContent = 'Rp ' + data.total.toLocaleString('id-ID');
        document.getElementById('rec-poin').textContent = '+' + data.poin_earned + ' pts';

        document.getElementById('receipt-modal').classList.remove('hidden');
    }

    function closeReceiptAndNew() {
        document.getElementById('receipt-modal').classList.add('hidden');
        resetCart();
        clearMemberSearch();
        document.getElementById('cash-tendered').value = '';
        calculateChange();
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('receipt-modal');
            if (modal && !modal.classList.contains('hidden')) {
                closeReceiptAndNew();
            }
        }
    });
</script>
@endsection
