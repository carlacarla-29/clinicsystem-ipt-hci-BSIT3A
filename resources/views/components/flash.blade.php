@if(session('success'))
    <div class="flash-message flash-success">
        {{ session('success') }}
    </div>
@endif
@if(session('info'))
    <div class="flash-message flash-info">
        {{ session('info') }}
    </div>
@endif
@if(session('error'))
    <div class="flash-message flash-error">
        {{ session('error') }}
    </div>
@endif


