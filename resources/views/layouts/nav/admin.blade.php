@php
    $c = $navCounts ?? [];

    /* The console can be narrowed to one barangay with ?barangay=<id>; carry
       that choice through the sidebar so drilling down doesn't reset it. */
    $scope = \App\Support\BarangayFilter::params();
    $link = fn (string $route) => route($route, $scope);

    /* Twelve destinations on screen at once read as a wall. They are grouped
       into what an officer actually comes here to do, so the panel starts as
       six rows and the rest opens on demand. Nothing was dropped: every
       section that was reachable before is still one click away. */
    $cases = request()->routeIs('admin.complaints.*', 'admin.map.*', 'admin.requests.*', 'admin.appointments.*');
    $publishing = request()->routeIs('admin.announcements.*', 'admin.lost-found.*', 'admin.jobs.*');
    $managing = request()->routeIs('admin.barangays.*', 'admin.users.*');
@endphp

<nav class="nav">
    <x-nav-item :href="route('admin.dashboard')" label="Dashboard" icon="grid"
        pattern="admin.dashboard" />

    <x-nav-group label="Cases & requests" icon="layers" id="cases" :active="$cases">
        <x-nav-item :href="$link('admin.complaints.index')" label="Complaints" icon="alert"
            pattern="admin.complaints.*" :count="$c['open_complaints'] ?? null" />
        <x-nav-item :href="$link('admin.requests.index')" label="Document Requests" icon="file"
            pattern="admin.requests.*" :count="$c['pending_docs'] ?? null" />
        <x-nav-item :href="$link('admin.appointments.index')" label="Appointments" icon="calendar"
            pattern="admin.appointments.*" :count="$c['pending_appts'] ?? null" />
        {{-- The map plots complaints, so it lives with them rather than filed
           under records, where a first-time officer would not look. --}}
        <x-nav-item :href="$link('admin.map.index')" label="Complaint Map" icon="pin"
            pattern="admin.map.*" />
    </x-nav-group>

    <x-nav-item :href="$link('admin.queue.index')" label="Queueing" icon="ticket"
        pattern="admin.queue.*" :count="$c['waiting'] ?? null" />

    <x-nav-group label="Publishing" icon="megaphone" id="publishing" :active="$publishing">
        <x-nav-item :href="$link('admin.announcements.index')" label="Announcements" icon="bell"
            pattern="admin.announcements.*" />
        <x-nav-item :href="$link('admin.lost-found.index')" label="Lost & Found" icon="box"
            pattern="admin.lost-found.*" />
        <x-nav-item :href="$link('admin.jobs.index')" label="Livelihood & Jobs" icon="briefcase"
            pattern="admin.jobs.*" />
    </x-nav-group>

    <x-nav-group label="City & accounts" icon="users" id="managing" :active="$managing">
        <x-nav-item :href="$link('admin.barangays.index')" label="Barangays" icon="map"
            pattern="admin.barangays.*" :count="$c['barangays'] ?? null" />
        <x-nav-item :href="$link('admin.users.index')" label="Accounts" icon="users"
            pattern="admin.users.*" />
    </x-nav-group>

    <x-nav-item :href="route('admin.settings.edit')" label="Settings" icon="sliders"
        pattern="admin.settings.*" />
</nav>
