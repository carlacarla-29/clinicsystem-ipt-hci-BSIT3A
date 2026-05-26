<x-app-layout>
    <x-slot name="header">
        <div class="medicines-header">
            <div>
                <h1>Medicine Inventory</h1>
                <p>Manage medicine stock and inventory.</p>
            </div>

            <a href="{{ route('medicines.create') }}" class="medicines-add-button">
                <span>+</span>
                <span>Add New Medicine</span>
            </a>
        </div>
    </x-slot>

    <div class="medicines-page">
        <div class="medicines-container">
            @if(session('success'))
                <div class="medicines-alert medicines-alert-success">{{ session('success') }}</div>
            @endif

            <div class="medicines-stat-grid">
                <div class="medicines-stat-card">
                    <div class="medicines-stat-icon medicines-stat-teal">Rx</div>
                    <div>
                        <p>Total Medicines</p>
                        <strong>{{ number_format($totalMedicines) }}</strong>
                        <span>All items</span>
                    </div>
                </div>

                <div class="medicines-stat-card">
                    <div class="medicines-stat-icon medicines-stat-amber">!</div>
                    <div>
                        <p>Low Stock Items</p>
                        <strong>{{ number_format($lowStockCount) }}</strong>
                        <span>Needs attention</span>
                    </div>
                </div>

                <div class="medicines-stat-card">
                    <div class="medicines-stat-icon medicines-stat-green">OK</div>
                    <div>
                        <p>In Stock</p>
                        <strong>{{ number_format($inStockCount) }}</strong>
                        <span>Sufficient stock</span>
                    </div>
                </div>

                <div class="medicines-stat-card">
                    <div class="medicines-stat-icon medicines-stat-purple">#</div>
                    <div>
                        <p>Dispensed Today</p>
                        <strong>{{ number_format($dispensedToday) }}</strong>
                        <span>Total items</span>
                    </div>
                </div>
            </div>

            @if($lowStock->isNotEmpty())
                <div class="medicines-low-stock-note">
                    <strong>Low stock medicines:</strong>
                    @foreach($lowStock->take(4) as $medicine)
                        <a href="{{ route('medicines.edit', $medicine) }}">{{ $medicine->name }} ({{ $medicine->quantity }} left)</a>
                    @endforeach
                </div>
            @endif

            <div class="medicines-panel">
                <form method="GET" action="{{ route('medicines.index') }}" class="medicines-filter-bar">
                    <label class="medicines-search">
                        <span class="medicines-search-icon">Q</span>
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search medicine by name or unit..."
                        >
                    </label>

                    <label class="medicines-select-wrap">
                        <span>Unit</span>
                        <select name="unit">
                            <option value="">All Units</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit }}" @selected(request('unit') === $unit)>{{ ucfirst($unit) }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="medicines-select-wrap">
                        <span>Stock Status</span>
                        <select name="stock_status">
                            <option value="">All Statuses</option>
                            <option value="in_stock" @selected(request('stock_status') === 'in_stock')>In Stock</option>
                            <option value="low_stock" @selected(request('stock_status') === 'low_stock')>Low Stock</option>
                            <option value="out_of_stock" @selected(request('stock_status') === 'out_of_stock')>Out of Stock</option>
                        </select>
                    </label>

                    <button type="submit" class="medicines-filter-button">Filter</button>
                    <a href="{{ route('medicines.index') }}" class="medicines-reset-button">Reset</a>
                </form>

                <div class="medicines-table-wrap">
                    <table class="medicines-table">
                        <thead>
                            <tr>
                                <th>Medicine Name</th>
                                <th>Unit</th>
                                <th>Quantity</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($medicines as $medicine)
                                @php
                                    $stockStatus = $medicine->quantity <= 0
                                        ? 'out-of-stock'
                                        : ($medicine->quantity <= 10 ? 'low-stock' : 'in-stock');
                                    $stockLabel = $medicine->quantity <= 0
                                        ? 'Out of Stock'
                                        : ($medicine->quantity <= 10 ? 'Low Stock' : 'In Stock');
                                    $iconColors = ['medicines-row-blue', 'medicines-row-amber', 'medicines-row-purple', 'medicines-row-pink', 'medicines-row-teal'];
                                @endphp
                                <tr>
                                    <td>
                                        <div class="medicines-name-cell">
                                            <div class="medicines-row-icon {{ $iconColors[$loop->index % count($iconColors)] }}">+</div>
                                            <div>
                                                <strong>{{ $medicine->name }}</strong>
                                                <span>{{ ucfirst($medicine->unit) }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ ucfirst($medicine->unit) }}</td>
                                    <td>{{ number_format($medicine->quantity) }}</td>
                                    <td>
                                        <span class="medicines-status medicines-status-{{ $stockStatus }}">{{ $stockLabel }}</span>
                                    </td>
                                    <td>
                                        <div class="medicines-actions">
                                            <a href="{{ route('medicines.show', $medicine) }}" title="View">View</a>
                                            <a href="{{ route('medicines.edit', $medicine) }}" title="Edit">Edit</a>
                                            <form method="POST" action="{{ route('medicines.destroy', $medicine) }}" onsubmit="return confirm('Remove this medicine from inventory?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Delete">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="medicines-empty">No medicines found. Add one to begin tracking inventory.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="medicines-pagination">
                    <p>Showing {{ $medicines->firstItem() ?? 0 }} to {{ $medicines->lastItem() ?? 0 }} of {{ $medicines->total() }} results</p>
                    <div>{{ $medicines->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
