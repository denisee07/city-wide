@php $portal = 'admin'; @endphp
@extends('layouts.portal')

@section('title', $program->title.' — '.config('app.name'))
@section('topbar-title', 'Assistance & Ayuda')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">Ayuda</span><h1>{{ $program->title }}</h1><p class="sub">{{ \App\Models\AssistanceProgram::types()[$program->type] ?? $program->type }} · {{ $program->barangay?->name ?? 'Citywide' }}</p></div>
        <a href="{{ route('admin.assistance.index') }}" class="btn btn-ghost">Back</a>
    </div>

    <div class="grid-2">
        <section class="card">
            <h3>Program details</h3>
            <form method="POST" action="{{ route('admin.assistance.update', $program) }}">
                @csrf @method('PUT')
                <div class="field"><label>Title</label><input type="text" name="title" class="input" value="{{ $program->title }}" required></div>
                <div class="field"><label>Type</label>
                    <select name="type" class="input">
                        @foreach (\App\Models\AssistanceProgram::types() as $k => $v)
                            <option value="{{ $k }}" @selected($program->type===$k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field"><label>Status</label>
                    <select name="status" class="input">
                        @foreach (\App\Models\AssistanceProgram::statuses() as $k => $v)
                            <option value="{{ $k }}" @selected($program->status===$k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field"><label>Distribution date</label><input type="date" name="distribution_date" class="input" value="{{ optional($program->distribution_date)->format('Y-m-d') }}"></div>
                <div class="field"><label>Location</label><input type="text" name="location" class="input" value="{{ $program->location }}"></div>
                <div class="field"><label>Barangay</label>
                    <select name="barangay_id" class="input">
                        <option value="">Citywide</option>
                        @foreach (\App\Models\Barangay::ordered()->get() as $b)
                            <option value="{{ $b->id }}" @selected($program->barangay_id===$b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field"><label>Beneficiary limit</label><input type="number" name="beneficiary_limit" class="input" value="{{ $program->beneficiary_limit }}" min="1"></div>
                <div class="field"><label>Description</label><textarea name="description" class="input" rows="2">{{ $program->description }}</textarea></div>
                <div class="field"><label>Eligibility</label><textarea name="eligibility" class="input" rows="2">{{ $program->eligibility }}</textarea></div>
                <button type="submit" class="btn btn-primary">Save</button>
            </form>
            <form method="POST" action="{{ route('admin.assistance.destroy', $program) }}" onsubmit="return confirm('Delete program?')" style="margin-top:.5rem">
                @csrf @method('DELETE')<button class="btn btn-danger btn-sm">Delete program</button>
            </form>
        </section>

        <section class="card">
            <h3>Add beneficiary</h3>
            <form method="POST" action="{{ route('admin.assistance.beneficiaries.add', $program) }}">
                @csrf
                <div class="field">
                    <label>Resident</label>
                    <select name="user_id" class="input">
                        @foreach ($residents as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Add & generate QR</button>
            </form>

            <h3 style="margin-top:1rem">Beneficiaries ({{ $program->beneficiaries->count() }})</h3>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Resident</th><th>Reference</th><th>Status</th><th>QR</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($program->beneficiaries as $b)
                            <tr>
                                <td>{{ $b->resident?->name }}</td>
                                <td>{{ $b->reference_no }}</td>
                                <td>{{ $b->status }}</td>
                                <td class="tiny">{{ $b->qr_code }}</td>
                                <td>
                                    @if ($b->status !== 'claimed')
                                        <form method="POST" action="{{ route('admin.assistance.beneficiaries.remove', $b) }}" onsubmit="return confirm('Remove?')">
                                            @csrf @method('DELETE')<button class="btn btn-sm btn-danger">Remove</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection