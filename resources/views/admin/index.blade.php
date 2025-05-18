@extends('admin.template.main')

@section('css')
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
@endsection

@section('main-content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Selamat Datang Admin, {{ $user->name }}</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">

            <!-- Info Cards -->
            <div class="row">
                <div class="col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <p>Jumlah Member</p>
                            <h3>{{ $membersCount }}</h3>
                        </div>
                        <div class="icon">
                            <i class="ion ion-ios-people"></i>
                        </div>
                        <a href="{{ route('admin.members.index') }}" class="small-box-footer">
                            Lihat member <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <p>Jumlah Transaksi</p>
                            <h3>{{ $transactionsCount }}</h3>
                        </div>
                        <div class="icon">
                            <i class="ion ion-bag"></i>
                        </div>
                        <a href="{{ route('admin.transactions.index') }}" class="small-box-footer">
                            Lihat transaksi <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Transaksi Priority -->
            <div class="row">
                <div class="col-12">

                    <div class="card mb-4">
                        <div class="card-body">
                            <h3 class="mb-3">Transaksi Berjalan (Priority Service): </h3>
                            <table class="table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($priorityTransactions as $transaction)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ date('d F Y', strtotime($transaction->created_at)) }}</td>
                                            <td>
                                                @if ($transaction->status_id != '3')
                                                    <span class="text-danger">{{ $transaction->status->name }}</span>
                                                @else
                                                    <span class="text-success">{{ $transaction->status->name }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Transaksi Regular -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h3 class="mb-3">Transaksi Berjalan (Regular Service): </h3>
                            <table class="table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentTransactions as $transaction)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ date('d F Y', strtotime($transaction->created_at)) }}</td>
                                            <td>
                                                @if ($transaction->status_id != '3')
                                                    <span class="text-danger">{{ $transaction->status->name }}</span>
                                                @else
                                                    <span class="text-success">{{ $transaction->status->name }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tabel Transaksi Harian -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h3 class="mb-3">Ringkasan Transaksi Harian</h3>
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Jumlah Transaksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transactionsPerDay as $date => $count)
                                        <tr>
                                            <td>{{ $date }}</td> <!-- Sudah diformat dari controller -->
                                            <td>{{ $count }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Grafik Transaksi Harian -->
                    <div class="card">
                        <div class="card-body">
                            <h3 class="mb-3">Grafik Transaksi Harian</h3>
                            <div style="height: 300px;">
                                <canvas id="transactionChart"></canvas>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const labels = {!! json_encode(array_keys($transactionsPerDay->toArray())) !!};
        const data = {!! json_encode(array_values($transactionsPerDay->toArray())) !!};

        if (labels.length === 0 || data.length === 0) {
            console.warn('Data grafik kosong!');
            return;
        }

        const ctx = document.getElementById('transactionChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Transaksi',
                    data: data,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 2,
                    tension: 0.4,
                    pointRadius: 5,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Grafik Jumlah Transaksi Harian'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
