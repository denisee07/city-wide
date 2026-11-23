@php $portal = 'admin'; @endphp
@extends('layouts.portal')

@section('title', 'Add census record — '.config('app.name'))
@section('topbar-title', 'Census Management')

@section('content')
    <div class="page-head">
        <div><span class="eyebrow">Population</span><h1>Add census record</h1></div>
        <a href="{{ route('admin.census.index') }}" class="btn btn-ghost">Back</a>
    </div>

    <section class="card" style="max-width:760px">
        <form method="POST" action="{{ route('admin.census.store') }}">
            @csrf
            <div class="form-grid">
                <div class="field">
                    <label>Barangay *</label>
                    <select name="barangay_id" class="input" required>
                        @foreach (\App\Models\Barangay::ordered()->get() as $b)
                            <option value="{{ $b->id }}" @selected(old('barangay_id') == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Linked resident (optional)</label>
                    <select name="user_id" class="input">
                        <option value="">— none —</option>
                        @foreach (\App\Models\User::where('role','resident')->orderBy('name')->get() as $u)
                            <option value="{{ $u->id }}" @selected(old('user_id') == $u->id)>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Full name *</label>
                    <input type="text" name="full_name" class="input" value="{{ old('full_name') }}" required>
                </div>
                <div class="field">
                    <label>Household no.</label>
                    <input type="text" name="household_no" class="input" value="{{ old('household_no') }}">
                </div>
                <div class="field">
                    <label>Sex</label>
                    <select name="sex" class="input">
                        <option value="">—</option>
                        <option value="male" @selected(old('sex')==='male')>Male</option>
                        <option value="female" @selected(old('sex')==='female')>Female</option>
                    </select>
                </div>
                <div class="field">
                    <label>Birth date</label>
                    <input type="date" name="birth_date" class="input" value="{{ old('birth_date') }}">
                </div>
                <div class="field">
                    <label>Age</label>
                    <input type="number" name="age" class="input" value="{{ old('age') }}" min="0" max="130">
                </div>
                <div class="field">
                    <label>Household size *</label>
                    <input type="number" name="household_size" class="input" value="{{ old('household_size', 1) }}" min="1" max="50" required>
                </div>
                <div class="field">
                    <label>Purok</label>
                    <input type="text" name="purok" class="input" value="{{ old('purok') }}">
                </div>
                <div class="field">
                    <label>Occupation</label>
                    <input type="text" name="occupation" class="input" value="{{ old('occupation') }}">
                </div>
                <div class="field">
                    <label>Income bracket</label>
                    <select name="income_bracket" class="input">
                        <option value="">—</option>
                        @foreach (\App\Models\CensusRecord::incomeBrackets() as $k => $v)
                            <option value="{{ $k }}" @selected(old('income_bracket')===$k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Civil status</label>
                    <input type="text" name="civil_status" class="input" value="{{ old('civil_status') }}">
                </div>
                <div class="field">
                    <label>Education</label>
                    <input type="text" name="education" class="input" value="{{ old('education') }}">
                </div>
                <div class="field">
                    <label>Religion</label>
                    <input type="text" name="religion" class="input" value="{{ old('religion') }}">
                </div>
                <div class="field" style="grid-column:1/-1">
                    <label>Address</label>
                    <textarea name="address" class="input" rows="2">{{ old('address') }}</textarea>
                </div>
                <div class="field">
                    <label><input type="checkbox" name="is_head" value="1" @checked(old('is_head'))> Household head</label>
                </div>
                <div class="field">
                    <label><input type="checkbox" name="is_pwd" value="1" @checked(old('is_pwd'))> PWD</label>
                </div>
                <div class="field">
                    <label><input type="checkbox" name="is_senior" value="1" @checked(old('is_senior'))> Senior citizen</label>
                </div>
            </div>
            <div class="row" style="margin-top:1rem">
                <button type="submit" class="btn btn-primary">Save record</button>
                <a href="{{ route('admin.census.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </section>
@endsection