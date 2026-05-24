@if(session('success'))
    <div class="mb-4 bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">
        {{ session('success') }}
    </div>
@endif
@if(session('info'))
    <div class="mb-4 bg-blue-100 border border-blue-400 text-blue-800 px-4 py-3 rounded">
        {{ session('info') }}
    </div>
@endif
@if(session('error'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded">
        {{ session('error') }}
    </div>
@endif


