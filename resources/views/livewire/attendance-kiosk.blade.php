<div class="min-h-screen bg-gray-100 flex flex-col items-center justify-center p-4">

    <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-gray-800">Absensi Karyawan</h1>
        <p class="text-gray-500">{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>

    @if($message)
        <div class="mb-6 p-4 rounded-lg w-full max-w-md text-center font-bold {{ $status == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
            {{ $message }}
        </div>
    @endif

    @if(!$selectedEmployee)
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 w-full max-w-2xl">
            @foreach($employees as $emp)
                <button wire:click="selectEmployee({{ $emp->id }})" 
                    class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg hover:bg-blue-50 transition transform hover:-translate-y-1 flex flex-col items-center gap-3 border border-gray-200">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center text-2xl font-bold text-blue-600">
                        {{ substr($emp->name, 0, 1) }}
                    </div>
                    <span class="font-semibold text-gray-700">{{ $emp->name }}</span>
                    <span class="text-xs text-gray-400 uppercase">{{ $emp->position }}</span>
                </button>
            @endforeach
        </div>
    @endif

    @if($selectedEmployee)
        <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-sm text-center">
            <h2 class="text-xl font-bold mb-2">Halo, {{ $selectedEmployee->name }}</h2>
            <p class="text-gray-400 mb-6">Masukkan PIN Anda</p>

            <input type="password" wire:model="pin" maxlength="6" autofocus
                class="w-full text-center text-3xl tracking-widest border-b-2 border-gray-300 focus:border-blue-500 focus:outline-none py-2 mb-6"
                placeholder="••••••">

            <div class="flex gap-2">
                <button wire:click="$set('selectedEmployee', null)" class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-lg font-semibold">Batal</button>
                <button wire:click="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-semibold shadow-lg">Absen</button>
            </div>
        </div>
    @endif
</div>