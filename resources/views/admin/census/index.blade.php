@php $portal = 'admin'; @endphp
@extends('layouts.portal')

@section('title', 'Census — '.config('app.name'))
@section('topbar-title', 'Census Management')

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Population</span>
            <h1>Census records</h1>
            <p class="sub">Household and demographic records per barangay.</p>
        </div>
        <div class="row">
            <a href="{{ route('admin.census.create') }}" class="btn btn-primary"><x-icon name="plus" size="15" /> Add record</a>
        </div>
    </div>

    {{-- stats --}}
    <div class="stats">
        <div class="stat"><div class="label">Total records</div><div class="value">{{ $stats['total'] }}</div></div>
        <div class="stat"><div class="label">Male</div><div class="value">{{ $stats['male'] }}</div></div>
        <div class="stat"><div class="label">Female</div><div class="value">{{ $stats['female'] }}</div></div>
        <div class="stat"><div class="label">Households</div><div class="value">{{ $stats['households'] }}</div></div>
    </div>

    <form method="GET" action="{{ route('admin.census.index') }}">
        <div class="toolbar">
            <div class="search-box grow">
                <input type="search" name="search" class="input" placeholder="Search name, household, purok…" value="{{ request('search') }}">
            </div>
            <select name="barangay" class="input">
                <option value="">All barangays</option>
                @foreach ($barangays as $b)
                    <option value="{{ $b->id }}" @selected(request('barangay') == $b->id)>{{ $b->name }}</option>
                @endforeach
            </select>
            <select name="sex" class="input">
                <option value="">All sexes</option>
                <option value="male" @selected(request('sex') === 'male')>Male</option>
                <option value="female" @selected(request('sex') === 'female')>Female</option>
            </select>
            <button type="submit" class="btn btn-primary">Apply</button>
            @if (request()->hasAny(['search','barangay','sex']))
                <a href="{{ route('admin.census.index') }}" class="btn btn-ghost">Reset</a>
            @endif
        </div>
    </form>

    <section class="card">
        @if ($records->isEmpty())
            <div class="empty"><h3>No census records</h3><p>Add records to build the population database.</p></div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th><th>Sex</th><th>Age</th><th>Barangay</th><th>Purok</th><th>Household</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $r)
                            <tr>
                                <td>{{ $r->full_name }}</td>
                                <td>{{ ucfirst($r->sex ?? '—') }}</td>
                                <td>{{ $r->age ?? '—' }}</td>
                                <td>{{ $r->barangay?->name ?? '—' }}</td>
                                <td>{{ $r->purok ?? '—' }}</td>
                                <td>{{ $r->household_no ?? '—' }}</td>
                                <td class="right">
                                    <a href="{{ route('admin.census.edit', $r) }}" class="btn btn-sm btn-info">Edit</a>
                                    <form method="POST" action="{{ route('admin.census.destroy', $r) }}" style="display:inline" onsubmit="return confirm('Delete this record?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-foot">{{ $records->links() }}</div>
        @endif
    </section>

    {{-- per-barangay summary --}}
    <section class="card" style="margin-top:1.5rem">
        <div class="card-head"><h2>Per-barangay census summary</h2></div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Barangay</th><th>Total</th><th>Male</th><th>Female</th><th>Households</th></tr></thead>
                <tbody>
                    @foreach ($byBarangay as $row)
                        <tr>
                            <td>{{ $row['barangay']->name }}</td>
                            <td>{{ $row['total'] }}</td>
                            <td>{{ $row['male'] }}</td>
                            <td>{{ $row['female'] }}</td>
                            <td>{{ $row['households'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection