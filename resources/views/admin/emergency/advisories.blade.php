@php $portal = 'admin'; @endphp
@extends('layouts.portal')

@section('title', 'Emergency advisories — '.config('app.name'))
@section('topbar-title', 'Emergency Monitoring')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">DRRM</span><h1>Emergency advisories</h1></div>
        <a href="{{ route('admin.emergency.index') }}" class="btn btn-ghost">Monitoring</a>
    </div>

    <section class="card" style="max-width:760px">
        <h3>Publish new advisory</h3>
        <form method="POST" action="{{ route('admin.emergency.advisories.store') }}">
            @csrf
            <div class="form-grid">
                <div class="field" style="grid-column:1/-1">
                    <label>Title *</label>
                    <input type="text" name="title" class="input" required>
                </div>
                <div class="field" style="grid-column:1/-1">
                    <label>Body *</label>
                    <textarea name="body" class="input" rows="4" required></textarea>
                </div>
                <div class="field">
                    <label>Category</label>
                    <select name="category" class="input">
                        @foreach (\App\Models\EmergencyAdvisory::categories() as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Scope</label>
                    <select name="scope" class="input">
                        <option value="citywide">Citywide</option>
                        <option value="barangay">Barangay</option>
                    </select>
                </div>
                <div class="field">
                    <label>Barangay (if scoped)</label>
                    <select name="barangay_id" class="input">
                        <option value="">—</option>
                        @foreach (\App\Models\Barangay::ordered()->get() as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label><input type="checkbox" name="is_critical" value="1"> Critical</label>
                </div>
                <div class="field">
                    <label>Publish at (optional)</label>
                    <input type="date" name="published_at" class="input">
                </div>
                <div class="field">
                    <label>Expires at (optional)</label>
                    <input type="date" name="expires_at" class="input">
                </div>
            </div>
            <div class="row" style="margin-top:1rem">
                <button type="submit" class="btn btn-primary">Publish</button>
            </div>
        </form>
    </section>

    <section class="card" style="margin-top:1.5rem">
        <h3>Published advisories</h3>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Title</th><th>Category</th><th>Scope</th><th>Critical</th><th>Published</th><th></th></tr></thead>
                <tbody>
                    @foreach ($advisories as $a)
                        <tr>
                            <td>{{ $a->title }}</td>
                            <td>{{ \App\Models\EmergencyAdvisory::categories()[$a->category] ?? $a->category }}</td>
                            <td>{{ $a->scope }}</td>
                            <td>{{ $a->is_critical ? 'Yes' : 'No' }}</td>
                            <td>{{ optional($a->published_at)->format('M j, Y') }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.emergency.advisories.destroy', $a) }}" onsubmit="return confirm('Delete?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-foot">{{ $advisories->links() }}</div>
    </section>
@endsection