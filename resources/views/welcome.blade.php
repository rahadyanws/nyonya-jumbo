<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Dinsum Nyonya Jumbo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800" x-data="shop()">

    <header class="bg-red-600 text-white sticky top-0 z-50 shadow-md">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold">🥢 Dinsum Nyonya Jumbo</h1>
                <p class="text-xs text-red-100">Enak, Halal, Murah!</p>
            </div>
            <button @click="openCart()" class="relative p-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <span x-show="cart.length > 0" class="absolute top-0 right-0 bg-yellow-400 text-red-800 text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center" x-text="cart.length"></span>
            </button>
        </div>
    </header>

    <div class="bg-white shadow-sm sticky top-[72px] z-40">
        <div class="container mx-auto px-4 py-3 overflow-x-auto whitespace-nowrap hide-scrollbar">
            <template x-for="cat in categories" :key="cat">
                <button 
                    @click="activeCategory = cat"
                    :class="activeCategory === cat ? 'bg-red-600 text-white border-red-600' : 'bg-white text-gray-600 border-gray-200'"
                    class="mr-2 px-4 py-1.5 rounded-full border text-sm font-medium transition-colors duration-200"
                    x-text="cat">
                </button>
            </template>
            <button @click="activeCategory = 'Semua'" 
                :class="activeCategory === 'Semua' ? 'bg-red-600 text-white border-red-600' : 'bg-white text-gray-600 border-gray-200'"
                class="mr-2 px-4 py-1.5 rounded-full border text-sm font-medium">
                Semua
            </button>
        </div>
    </div>

    <main class="container mx-auto px-4 py-6 pb-24">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($products as $product)
                <div x-show="activeCategory === 'Semua' || activeCategory === '{{ $product->category }}'" 
                     class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 flex flex-col h-full">
                    
                    <div class="h-40 bg-gray-200 relative overflow-hidden">
                        @if($product->image_path)
                            <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="flex items-center justify-center h-full text-gray-400">
                                <span class="text-xs">No Image</span>
                            </div>
                        @endif
                    </div>

                    <div class="p-3 flex flex-col flex-grow">
                        <span class="text-[10px] text-red-500 font-semibold uppercase tracking-wider">{{ $product->category }}</span>
                        <h3 class="font-bold text-gray-800 leading-tight mb-1">{{ $product->name }}</h3>
                        <p class="text-xs text-gray-500 line-clamp-2 mb-2">{{ $product->description }}</p>
                        
                        <div class="mt-auto flex justify-between items-center">
                            <span class="font-bold text-red-600">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                            <button @click="addToCart({{ $product }})" class="bg-gray-100 hover:bg-red-50 text-red-600 p-2 rounded-full transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </main>

    <div x-show="cart.length > 0" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-full opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         class="fixed bottom-0 left-0 w-full bg-white border-t p-4 shadow-2xl z-50">
        <div class="container mx-auto flex justify-between items-center">
            <div>
                <p class="text-xs text-gray-500">Total Pesanan</p>
                <p class="font-bold text-lg" x-text="formatRupiah(totalPrice)"></p>
                <p class="text-xs text-gray-400" x-text="cart.length + ' Item'"></p>
            </div>
            <button @click="checkoutWA()" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-bold shadow-lg flex items-center gap-2">
                <span>Pesan via WA</span>
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.463 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
            </button>
        </div>
    </div>

    <script>
        function shop() {
            return {
                products: @json($products),
                categories: @json($categories->values()),
                activeCategory: 'Semua',
                cart: JSON.parse(localStorage.getItem('dinsum_cart')) || [],
                
                get totalPrice() {
                    return this.cart.reduce((total, item) => total + (item.price * item.qty), 0);
                },

                addToCart(product) {
                    let existing = this.cart.find(item => item.id === product.id);
                    if (existing) {
                        existing.qty++;
                    } else {
                        this.cart.push({...product, qty: 1});
                    }
                    this.saveCart();
                    // Feedback getar di HP (Haptic)
                    if (navigator.vibrate) navigator.vibrate(50);
                },

                saveCart() {
                    localStorage.setItem('dinsum_cart', JSON.stringify(this.cart));
                },

                formatRupiah(number) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
                },

                checkoutWA() {
                    let text = "Halo Admin, saya mau pesan Dinsum:%0A";
                    this.cart.forEach(item => {
                        text += `- ${item.name} (${item.qty}x) : ${this.formatRupiah(item.price * item.qty)}%0A`;
                    });
                    text += `%0A*Total: ${this.formatRupiah(this.totalPrice)}*`;
                    
                    // Ganti nomor HP Client di sini
                    let phone = "6281234567890"; 
                    window.open(`https://wa.me/${phone}?text=${text}`, '_blank');
                    
                    // Opsional: Clear cart after checkout
                    // this.cart = []; this.saveCart();
                }
            }
        }
    </script>
</body>
</html>