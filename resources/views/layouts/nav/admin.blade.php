@php
    $c = $navCounts ?? [];

    /* The console can be narrowed to one barangay with ?barangay=<id>; carry
       that choice through the sidebar so drilling down doesn't reset it. */
    $scope = \App\Support\BarangayFilter::params();
    $link = fn (string $route) => route($route, $scope);

    /* Grouped so the panel opens as a handful of decisions, not a wall of
       links. The active group opens itself; the rest stay folded. */
    $cases = request()->routeIs('admin.complaints.*', 'admin.map.*');    $documents = request()->routeIs('admin.requests.*', 'admin.appointments.*');
    $publishing = request()->routeIs('admin.announcements.*', 'admin.textblast.*', 'admin.lost-found.*', 'admin.jobs.*');
    $managing = request()->routeIs('admin.barangays.*', 'admin.users.*', 'admin.census.*', 'admin.emergency.*');
    $programs = request()->routeIs('admin.assistance.*', 'admin.events.*', 'admin.reports.*');
@endphp

<nav class="nav">
    <x-nav-item :href="route('admin.dashboard')" label="Dashboard" icon="grid"
        pattern="admin.dashboard" />

    {{-- Complaints is a plain link: the map moved into the page itself, so
         the group would only wrap a single row. --}}
    <x-nav-item :href="$link('admin.complaints.index')" label="Complaints" icon="alert"
        pattern="admin.complaints.*" :count="$c['open_complaints'] ?? null" />

    {{-- Document requests and appointments are both counter bookings, so they
         share one section instead of two competing ones. --}}
    <x-nav-group label="Documents & Appointments" icon="file" id="documents" :active="$documents">
        <x-nav-item :href="$link('admin.requests.index')" label="Document Requests" icon="file"
            pattern="admin.requests.*" :count="$c['pending_docs'] ?? null" />
        <x-nav-item :href="$link('admin.appointments.index')" label="Appointments" icon="calendar"
            pattern="admin.appointments.*" :count="$c['pending_appts'] ?? null" />
    </x-nav-group>

    {{-- Queueing closes the front-of-house section. --}}
    <x-nav-item :href="$link('admin.queue.index')" label="Queueing" icon="ticket"
        pattern="admin.queue.*" :count="$c['waiting'] ?? null" />

    <x-nav-group label="Announcements" icon="megaphone" id="publishing" :active="$publishing">
        <x-nav-item :href="$link('admin.announcements.index')" label="Announcements" icon="bell"
            pattern="admin.announcements.*" />
        <x-nav-item :href="$link('admin.textblast.index')" label="Text Blast" icon="send"
            pattern="admin.textblast.*" />
        <x-nav-item :href="$link('admin.lost-found.index')" label="Lost & Found" icon="box"
            pattern="admin.lost-found.*" />
        <x-nav-item :href="$link('admin.jobs.index')" label="Livelihood & Jobs" icon="briefcase"
            pattern="admin.jobs.*" />
    </x-nav-group>

    <x-nav-group label="City management" icon="users" id="managing" :active="$managing">
        <x-nav-item :href="$link('admin.barangays.index')" label="Barangays" icon="map"
            pattern="admin.barangays.*" :count="$c['barangays'] ?? null" />
        <x-nav-item :href="$link('admin.users.index')" label="Accounts" icon="users"
            pattern="admin.users.*" />
        <x-nav-item :href="$link('admin.census.index')" label="Census" icon="users"
            pattern="admin.census.*" />
        <x-nav-item :href="$link('admin.emergency.index')" label="Emergency / DRRM" icon="alert"
            pattern="admin.emergency.*" />
    </x-nav-group>

    <x-nav-group label="Programs & services" icon="gift" id="programs" :active="$programs">
        <x-nav-item :href="$link('admin.assistance.index')" label="Assistance & Ayuda" icon="gift"
            pattern="admin.assistance.*" />
        <x-nav-item :href="$link('admin.events.index')" label="Events" icon="calendar"
            pattern="admin.events.*" />
        <x-nav-item :href="$link('admin.reports.index')" label="Reports" icon="file"
            pattern="admin.reports.*" />
    </x-nav-group>

    <x-nav-item :href="route('admin.settings.edit')" label="Settings" icon="sliders"
        pattern="admin.settings.*" />
</nav>