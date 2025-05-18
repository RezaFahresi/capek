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
                <h1 class="m-0 text-dark">Tambah Pesanan</h1>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                @foreach (['error', 'warning', 'success'] as $msg)
                    @if (session($msg))
                        <div class="alert alert-{{ $msg }} alert-dismissible fade show" role="alert">
                            {{ session($msg) }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                @endforeach

                <div class="card">
                    <div class="card-body">
                        <form id="form-transaksi" action="{{ route('member.transactions.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            {{-- Barang --}}
                            <div class="form-group row">
                                <label for="item_id" class="col-sm-2 col-form-label">Barang</label>
                                <div class="col-sm-4">
                                    <select class="form-control" id="item_id" name="item_id" required>
                                        <option value="">-- Pilih Barang --</option>
                                        @foreach ($items as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Servis --}}
                            <div class="form-group row">
                                <label for="service_id" class="col-sm-2 col-form-label">Servis</label>
                                <div class="col-sm-4">
                                    <select class="form-control" id="service_id" name="service_id" required>
                                        <option value="">-- Pilih Servis --</option>
                                        @foreach ($services as $service)
                                            <option value="{{ $service->id }}">{{ $service->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Kategori --}}
                            <div class="form-group row">
                                <label for="category_id" class="col-sm-2 col-form-label">Kategori</label>
                                <div class="col-sm-4">
                                    <select class="form-control" id="category_id" name="category_id" required>
                                        <option value="">-- Pilih Kategori --</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Tipe Servis --}}
                            <div class="form-group row">
                                <label for="service_type_id" class="col-sm-2 col-form-label">Tipe Servis</label>
                                <div class="col-sm-4">
                                    <select class="form-control" id="service_type_id" name="service_type_id" required>
                                        <option value="">-- Pilih Tipe Servis --</option>
                                        @foreach ($serviceTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Quantity --}}
                            <div class="form-group row">
                                <label for="quantity" class="col-sm-2 col-form-label">Banyak</label>
                                <div class="col-sm-2">
                                    <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="1" required>
                                </div>
                            </div>

                            {{-- Metode Pembayaran --}}
                            <div class="form-group row">
                                <label for="payment_method" class="col-sm-2 col-form-label">Metode Pembayaran</label>
                                <div class="col-sm-4">
                                    <select class="form-control" id="payment_method" name="payment_method" required>
                                        <option value="">-- Pilih Metode --</option>
                                        <option value="cash">Cash</option>
                                        <option value="transfer">Transfer</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Bukti Transfer --}}
                            <div class="form-group row" id="bukti_transfer_group" style="display: none;">
                                <label for="bukti_transfer" class="col-sm-2 col-form-label">Bukti Transfer</label>
                                <div class="col-sm-4">
                                    <input type="file" class="form-control-file" name="bukti_transfer" id="bukti_transfer" accept="image/*">
                                </div>
                            </div>

                            <div class="form-group row mt-4">
                                <div class="col-sm-6 offset-sm-2">
                                    <button type="submit" class="btn btn-success">Simpan Pesanan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const metode = document.getElementById('payment_method');
            const buktiGroup = document.getElementById('bukti_transfer_group');
            const buktiInput = document.getElementById('bukti_transfer');

            metode.addEventListener('change', function () {
                if (this.value === 'transfer') {
                    buktiGroup.style.display = 'flex';
                    buktiInput.required = true;
                } else {
                    buktiGroup.style.display = 'none';
                    buktiInput.required = false;
                }
            });
        });
    </script>
@endsection
