@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Selamat Datang</h4>
    <div class="alert alert-info mt-3">
        Selamat datang di Sistem Informasi Akuntansi Pratama.<br>
        Silakan gunakan menu di samping untuk mengelola data.
    </div>

    @if(isset($reminder) && count($reminder))
        <div class="alert alert-warning">
            <b>Reminder Bahan Kadaluarsa/Hampir Expired:</b>
            <ul class="mb-0">
                @foreach($reminder as $row)
                    @php
                        $exp = \Carbon\Carbon::parse($row->tanggal_exp);
                        $diff = \Carbon\Carbon::today()->diffInDays($exp, false);
                    @endphp
                    <li>
                        <b>{{ $row->nama_bahan }}</b> expired {{ $exp->format('d M Y') }}
                        @if($diff < 0)
                            <span class="badge bg-danger">Kadaluarsa</span>
                        @elseif($diff == 0)
                            <span class="badge bg-warning text-dark">Expired Hari Ini</span>
                        @else
                            <span class="badge bg-warning text-dark">H-{{ $diff }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@endsection