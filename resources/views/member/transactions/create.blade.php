@extends('member.template.main')

@section('main-content')
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Tambah Pesanan</h1>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <form action="{{ route('member.transactions.store') }}" method="POST">
            @csrf

            <!-- Barang -->
            <div class="form-group">
                <label for="item">Barang</label>
                <select name="item_id" id="item" class="form-control @error('item_id') is-invalid @enderror">
                    <option value="1" {{ $selectedItem == 1 ? 'selected' : '' }}>Baju</option>
                    <option value="2" {{ $selectedItem == 2 ? 'selected' : '' }}>Celana</option>
                </select>
                @error('item_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Servis -->
            <div class="form-group">
                <label for="service">Servis</label>
                <select name="service_id" id="service" class="form-control @error('service_id') is-invalid @enderror">
                    <option value="1" {{ $selectedService == 1 ? 'selected' : '' }}>Cuci</option>
                    <option value="2" {{ $selectedService == 2 ? 'selected' : '' }}>Setrika</option>
                </select>
                @error('service_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Kategori -->
            <div class="form-group">
                <label for="category">Kategori</label>
                <select name="category_id" id="category" class="form-control @error('category_id') is-invalid @enderror">
                    <option value="1" {{ $selectedCategory == 1 ? 'selected' : '' }}>Satuan</option>
                    <option value="2" {{ $selectedCategory == 2 ? 'selected' : '' }}>Kiloan</option>
                </select>
                @error('category_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Jumlah -->
            <div class="form-group">
                <label for="quantity">Banyak</label>
                <input type="number" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror"
                    min="1" value="{{ $quantity ?? 1 }}">
                @error('quantity')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary">Simpan Pesanan</button>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Initialize Select2 for the item, service, and category dropdowns
        $('#item').select2({
            placeholder: 'Pilih Barang',
            allowClear: true
        });

        $('#service').select2({
            placeholder: 'Pilih Servis',
            allowClear: true
        });

        $('#category').select2({
            placeholder: 'Pilih Kategori',
            allowClear: true
        });
    });
</script>
@endsection
