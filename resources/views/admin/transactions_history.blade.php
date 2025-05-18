@extends('admin.template.main')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base_url" content="{{ url('admin') }}">
    <link href="{{ asset('vendor/datatables-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/datatables-responsive/css/responsive.bootstrap4.min.css') }}" rel="stylesheet">
@endsection

@section('main-content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Riwayat Transaksi</h1>
            </div>
        </div>
    </div>
</div>

<div class="content">
<div class="container-fluid">

    {{-- Filter --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="" method="get">
                        <div class="form-group row">
                            <label for="tahun" class="col-auto col-form-label">Tahun</label>
                            <div class="col-auto">
                                <select class="form-control" id="tahun" name="year">
                                    @foreach ($years as $year)
                                        <option value="{{ $year->Tahun }}" {{ $year->Tahun == $currentYear ? 'selected' : '' }}>
                                            {{ $year->Tahun }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <label for="bulan" class="col-auto col-form-label">Bulan</label>
                            <div class="col-auto">
                                <select class="form-control" id="bulan" name="month">
                                    @for ($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ $i == $currentMonth ? 'selected' : '' }}>
                                            {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-success">Filter</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Loop tabel: Priority, Regular, Selesai --}}
    @foreach ([
        ['label' => 'Transaksi Berjalan (Priority Service)', 'data' => $ongoingPriorityTransactions, 'id' => 'tbl-transaksi-priority'],
        ['label' => 'Transaksi Berjalan (Regular Service)', 'data' => $ongoingTransactions, 'id' => 'tbl-transaksi-belum'],
        ['label' => 'Transaksi Selesai', 'data' => $finishedTransactions, 'id' => 'tbl-transaksi-selesai'],
    ] as $section)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3">{{ $section['label'] }}</h4>
                    <table id="{{ $section['id'] }}" class="table dt-responsive nowrap" style="width: 100%">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Tanggal</th>
                                <th>Nama Member</th>
                                <th>Status</th>
                                <th>Biaya Servis</th>
                                <th>Total</th>
                                <th>Dibuat Oleh</th>
                                <th>Metode</th>
                                <th>Bukti Transfer</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($section['data'] as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ date('d F Y', strtotime($item->created_at)) }}</td>
                                <td>{{ $item->member->name ?? '-' }}</td>
                                <td>
                                    @if ($item->status_id == 3)
                                        <span class="text-success">Selesai</span>
                                    @else
                                        <select class="select-status" data-id="{{ $item->id }}" data-val="{{ $item->status_id }}">
                                            @foreach ($status as $s)
                                                <option value="{{ $s->id }}" {{ $item->status_id == $s->id ? 'selected' : '' }}>
                                                    {{ $s->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                </td>
                                <td>{{ $item->getFormattedServiceCost() }}</td>
                                <td>{{ $item->getFormattedTotal() }}</td>
                                <td>
                                    @if ($item->created_by === 'admin')
                                        <span class="badge badge-primary">Admin</span>
                                    @else
                                        <span class="badge badge-success">Member</span><br>
                                        <small>{{ $item->member->name ?? '-' }}</small>
                                    @endif
                                </td>
                                <td>{{ ucfirst($item->payment_method ?? '-') }}</td>
                                <td>
                                    @if ($item->bukti_transfer)
                                        <a href="{{ asset('storage/' . $item->bukti_transfer) }}" target="_blank">Lihat</a>
                                    @else
                                        <em>-</em>
                                    @endif
                                </td>
                                <td>
                                    <a href="#" class="badge badge-info btn-detail" data-toggle="modal"
                                        data-target="#transactionDetailModal" data-id="{{ $item->id }}">Detail</a>
                                    <a href="{{ route('admin.transactions.print.index', ['transaction' => $item->id]) }}"
                                        class="badge badge-primary" target="_blank">Cetak</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endforeach

</div>
</div>
@endsection

@section('modals')
    <x-admin.modals.transaction-detail-modal />
@endsection

@section('scripts')
<script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('vendor/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('vendor/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('js/ajax.js') }}"></script>
<script>
    $(document).ready(function () {
        $('#tbl-transaksi-selesai').DataTable();
        $('#tbl-transaksi-belum').DataTable();
        $('#tbl-transaksi-priority').DataTable();
    });
</script>
@endsection
