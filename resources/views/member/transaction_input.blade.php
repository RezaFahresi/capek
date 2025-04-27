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
                        <form id="form-transaksi" action="{{ route('admin.transactions.session.store') }}" method="POST">
                            @csrf

                            {{-- ID Member --}}
                            <div class="form-group row">
                                <label for="member_id" class="col-sm-2 col-form-label">ID Member</label>
                                <div class="col-sm-2">
                                    <input type="number" min="1" class="form-control" id="member_id" name="member_id"
                                        value="{{ old('member_id', $memberIdSessionTransaction ?? '') }}"
                                        @if (isset($memberIdSessionTransaction)) disabled title="Harap selesaikan transaksi yang ada untuk mengganti id member" @endif
                                        required>
                                    @if (isset($memberIdSessionTransaction))
                                        <input type="hidden" name="member_id" value="{{ $memberIdSessionTransaction }}">
                                    @endif
                                </div>
                            </div>

                            {{-- Barang --}}
                            <div class="form-group row">
                                <label for="item" class="col-sm-2 col-form-label">Barang</label>
                                <div class="col-sm-4">
                                    <select class="form-control" id="item" name="item">
                                        @foreach ($items as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Servis --}}
                            <div class="form-group row">
                                <label for="service" class="col-sm-2 col-form-label">Servis</label>
                                <div class="col-sm-4">
                                    <select class="form-control" id="service" name="service">
                                        @foreach ($services as $service)
                                            <option value="{{ $service->id }}">{{ $service->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Kategori --}}
                            <div class="form-group row">
                                <label for="category" class="col-sm-2 col-form-label">Kategori</label>
                                <div class="col-sm-4">
                                    <select class="form-control" id="category" name="category">
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Banyak (Quantity) --}}
                            <div class="form-group row">
                                <label for="quantity" class="col-sm-2 col-form-label">Banyak</label>
                                <div class="col-sm-2">
                                    <div class="input-group">
                                        <button type="button" class="btn btn-danger btn-number quantity-left-minus" data-type="minus">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="text" id="quantity" name="quantity" class="form-control input-number" value="1" min="1" max="100">
                                        <button type="button" class="btn btn-success btn-number quantity-right-plus" data-type="plus">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <button type="submit" class="btn btn-primary">Tambah Pesanan</button>
                                </div>
                            </div>
                        </form>

                        {{-- Tabel Pesanan --}}
                        <table id="tbl-input-transaksi" class="table mt-2 dt-responsive nowrap" style="width: 100%">
                            <thead class="thead-light">
                                <tr>
                                    <th>No</th>
                                    <th>Barang</th>
                                    <th>Servis</th>
                                    <th>Kategori</th>
                                    <th>Banyak</th>
                                    <th>Harga</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                        </table>

                        {{-- Tombol Bayar --}}
                        @if (isset($sessionTransaction))
                            <button id="btn-bayar" class="btn btn-success" data-toggle="modal" data-target="#paymentModal">Bayar</button>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('modals')
    <x-admin.modals.payment-modal 
        :$serviceTypes 
        :vouchers="$vouchers ?? []" 
        :totalPrice="$totalPrice ?? '0'" 
        :show="isset($sessionTransaction)" 
    />
@endsection

@section('scripts')
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <script>
        $(document).ready(function () {
            $('#tbl-input-transaksi').DataTable({
                searching: false,
                paging: false,
                lengthChange: false,
                filter: false,
                info: false
            });

            // Quantity plus minus
            $('.quantity-right-plus').click(function () {
                let quantity = parseInt($('#quantity').val());
                if (!isNaN(quantity)) $('#quantity').val(quantity + 1);
            });

            $('.quantity-left-minus').click(function () {
                let quantity = parseInt($('#quantity').val());
                if (!isNaN(quantity) && quantity > 1) $('#quantity').val(quantity - 1);
            });
        });
    </script>

    @if (session('id_trs'))
        <script>
            window.open('{{ route('admin.transactions.print.index', ['transaction' => session('id_trs')]) }}', '_blank');
        </script>
    @endif
@endsection
