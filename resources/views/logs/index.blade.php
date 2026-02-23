@extends('layouts.template')

@section('content')
    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">Logs Sensor Data</h1>

        <!-- Success/Error Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if (isset($error))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ $error }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Filter Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row align-items-end">
                    <div class="col-md-2">
                        <label for="start_date" class="form-label">Tanggal Mulai:</label>
                        <input type="date" name="start_date" id="start_date" value="{{ $startDate }}"
                            class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label for="end_date" class="form-label">Tanggal Selesai:</label>
                        <input type="date" name="end_date" id="end_date" value="{{ $endDate }}"
                            class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label for="per_page" class="form-label">Data per Halaman:</label>
                        <select name="per_page" id="per_page" class="form-control">
                            <option value="10" {{ request('per_page', 10) == 10 ?  'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
                            <option value="500" {{ request('per_page', 10) == 500 ?  'selected' : '' }}>500</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('logs.export', request()->all()) }}" class="btn btn-success mr-2">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </a>
                        <button type="button" class="btn btn-danger" id="delete-logs-btn">
                            <i class="fas fa-trash"></i> Hapus Data
                        </button>
                        <a href="{{ route('logs.index') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset Filter
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    @if (isset($pagination) && $pagination->total() > 0)
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-database"></i> 
                            Menampilkan {{ $pagination->firstItem() }} - {{ $pagination->lastItem() }} 
                            dari {{ number_format($pagination->total()) }} data
                        </h6>
                    @else
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-database"></i> Total: 0 data
                        </h6>
                    @endif
                </div>
                @if (isset($pagination) && $pagination->total() > 0)
                    <div class="text-muted small">
                        Halaman {{ $pagination->currentPage() }} dari {{ $pagination->lastPage() }}
                    </div>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>Waktu</th>
                            <th>Time Device</th>
                            <th>Battery A (V)</th>
                            <th>Battery B (V)</th>
                            <th>Battery C (V)</th>
                            <th>Battery D (V)</th>
                            <th>Temp 1 (°C)</th>
                            <th>Temp 2 (°C)</th>
                            <th>PLN Volt (V)</th>
                            <th>PLN Current (A)</th>
                            <th>PLN Power (W)</th>
                            <th>Relay 1</th>
                            <th>Relay 2</th>
                            <!-- Tambahan kolom baru -->
                            <!--<th>Server Volt (V)</th>-->
                            <th>Temp1 Threshold</th>
                            <th>Temp2 Threshold</th>
                            <th>Hysteresis</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $index => $log)
                            <tr>
                                <td>{{ $pagination->firstItem() + $index }}</td>
                                <td>{{ $log->created_at->format('d-m-Y H:i:s') }}</td>
                                <td>{{ $log->timeDevice }}</td>
                                <td>{{ number_format($log->battery_a, 2) }}</td>
                                <td>{{ number_format($log->battery_b, 2) }}</td>
                                <td>{{ number_format($log->battery_c, 2) }}</td>
                                <td>{{ number_format($log->battery_d, 2) }}</td>
                                <td>{{ number_format($log->temperature_1, 2) }}</td>
                                <td>{{ number_format($log->temperature_2, 2) }}</td>
                                <td>{{ number_format($log->pln_volt, 2) }}</td>
                                <td>{{ number_format($log->pln_current, 2) }}</td>
                                <td>{{ number_format($log->power, 2) }}</td>
                                <td>{{ $log->relay_1 }}</td>
                                <td>{{ $log->relay_2 }}</td>
                                <!-- Tambahan data baru -->
                                <!--<td>{{ $log->server_voltage ? number_format($log->server_voltage, 2) : '-' }}</td>-->
                                <td>{{ $log->temp1_threshold ? number_format($log->temp1_threshold, 2) : '-' }}</td>
                                <td>{{ $log->temp2_threshold ? number_format($log->temp2_threshold, 2) : '-' }}</td>
                                <td>{{ $log->hysteresis ? number_format($log->hysteresis, 2) : '-' }}</td>
                            </tr>
                        @empty
                            <td colspan="18" class="text-center">Tidak ada data yang tersedia</td>
                        @endforelse
                    </tbody>
                    </table>
                </div>

                <!-- Enhanced Pagination -->
                @if (isset($pagination) && $pagination->hasPages())
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p class="text-muted small">
                                Menampilkan {{ $pagination->firstItem() }} sampai {{ $pagination->lastItem() }} 
                                dari {{ number_format($pagination->total()) }} data
                            </p>
                        </div>
                        <div class="col-md-6">
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-end mb-0">
                                    {{-- Previous Page Link --}}
                                    @if ($pagination->onFirstPage())
                                        <li class="page-item disabled">
                                            <span class="page-link"><i class="fas fa-angle-double-left"></i> First</span>
                                        </li>
                                        <li class="page-item disabled">
                                            <span class="page-link"><i class="fas fa-angle-left"></i></span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $pagination->url(1) }}" rel="first">
                                                <i class="fas fa-angle-double-left"></i> First
                                            </a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $pagination->previousPageUrl() }}" rel="prev">
                                                <i class="fas fa-angle-left"></i>
                                            </a>
                                        </li>
                                    @endif

                                    {{-- Pagination Elements --}}
                                    @php
                                        $start = max($pagination->currentPage() - 2, 1);
                                        $end = min($pagination->currentPage() + 2, $pagination->lastPage());
                                    @endphp

                                    @if ($start > 1)
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    @endif

                                    @for ($page = $start; $page <= $end; $page++)
                                        @if ($page == $pagination->currentPage())
                                            <li class="page-item active">
                                                <span class="page-link">{{ $page }}</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $pagination->url($page) }}">{{ $page }}</a>
                                            </li>
                                        @endif
                                    @endfor

                                    @if ($end < $pagination->lastPage())
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    @endif

                                    {{-- Next Page Link --}}
                                    @if ($pagination->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $pagination->nextPageUrl() }}" rel="next">
                                                <i class="fas fa-angle-right"></i>
                                            </a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $pagination->url($pagination->lastPage()) }}" rel="last">
                                                Last <i class="fas fa-angle-double-right"></i>
                                            </a>
                                        </li>
                                    @else
                                        <li class="page-item disabled">
                                            <span class="page-link"><i class="fas fa-angle-right"></i></span>
                                        </li>
                                        <li class="page-item disabled">
                                            <span class="page-link">Last <i class="fas fa-angle-double-right"></i></span>
                                        </li>
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#delete-logs-btn').click(function() {
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();

                let confirmMessage = 'Apakah Anda yakin ingin menghapus data log';
                if (startDate || endDate) {
                    confirmMessage += ' untuk periode yang dipilih';
                } else {
                    confirmMessage += ' SEMUA';
                }
                confirmMessage += '? Tindakan ini tidak dapat dibatalkan!';

                if (!confirm(confirmMessage)) return;

                $.ajax({
                    url: '{{ route('logs.destroy') }}',
                    type: 'DELETE',
                    data: {
                        start_date: startDate,
                        end_date: endDate,
                        _token: '{{ csrf_token() }}'
                    },
                    beforeSend: function() {
                        $('#delete-logs-btn').prop('disabled', true).html(
                            '<i class="fas fa-spinner fa-spin"></i> Menghapus...');
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            const currentParams = new URLSearchParams(window.location.search);
                            window.location.href = '{{ route('logs.index') }}?' + currentParams
                                .toString();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        let message = 'Gagal menghapus data';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        alert('Error: ' + message);
                        console.error('Delete error:', xhr);
                    },
                    complete: function() {
                        $('#delete-logs-btn').prop('disabled', false).html(
                            '<i class="fas fa-trash"></i> Hapus Data');
                    }
                });
            });

            // Auto dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
    </script>
@endpush