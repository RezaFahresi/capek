@extends('member.template.main')

@section('css')
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
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table id="tbl-riwayat" class="table dataTable dt-responsive nowrap" style="width:100%">
                            <thead class="thead-light">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Metode</th>
                                    <th>Bukti</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transactions as $transaction)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ date('d F Y', strtotime($transaction->created_at)) }}</td>
                                    <td>
                                        @if ($transaction->status_id == 3)
                                            <span class="text-success">{{ $transaction->status->name }}</span>
                                        @elseif ($transaction->status_id == 2)
                                            <span class="text-warning">Menunggu Validasi</span>
                                        @else
                                            <span class="text-primary">{{ $transaction->status->name }}</span>
                                        @endif
                                    </td>
                                    <td>{{ ucfirst($transaction->payment_method ?? '-') }}</td>
                                    <td>
                                        @if ($transaction->bukti_transfer)
                                            <a href="{{ asset('storage/' . $transaction->bukti_transfer) }}" target="_blank">Lihat</a>
                                        @else
                                            <em>-</em>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('member.transactions.show', ['transaction' => $transaction->id]) }}"
                                            class="badge badge-success">Lihat Detail ></a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('vendor/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('vendor/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#tbl-riwayat').DataTable();
    });
</script>
@endsection
