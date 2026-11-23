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
        {{-- Directly under Complaints, and the group counts the map as one of
             its own so it is open whenever the map is on screen. It was filed
             at the bottom of the group first, which is how officers lost it. --}}
        <x-nav-item :href="$link('admin.map.index')" label="Complaint Map" icon="pin"
            pattern="admin.map.*" />
        <x-nav-item :href="$link('admin.requests.index')" label="Document Requests" icon="file"
            pattern="admin.requests.*" :count="$c['pending_docs'] ?? null" />
        <x-nav-item :href="$link('admin.appointments.index')" label="Appointments" icon="calendar"
            pattern="admin.appointments.*" :count="$c['pending_appts'] ?? null" />
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

    <x-nav-group label="City management" icon="users" id="managing" :active="request()->routeIs('admin.barangays.*','admin.users.*','admin.census.*','admin.emergency.*')">
        <x-nav-item :href="$link('admin.barangays.index')" label="Barangays" icon="map"
            pattern="admin.barangays.*" :count="$c['barangays'] ?? null" />
        <x-nav-item :href="$link('admin.users.index')" label="Accounts" icon="users"
            pattern="admin.users.*" />
        <x-nav-item :href="$link('admin.census.index')" label="Census" icon="users"
            pattern="admin.census.*" />
        <x-nav-item :href="$link('admin.emergency.index')" label="Emergency / DRRM" icon="alert"
            pattern="admin.emergency.*" />
    </x-nav-group>

    <x-nav-group label="Programs & services" icon="gift" id="programs" :active="request()->routeIs('admin.assistance.*','admin.events.*','admin.textblast.*','admin.reports.*','admin.notifications.*')">
        <x-nav-item :href="$link('admin.assistance.index')" label="Assistance & Ayuda" icon="gift"
            pattern="admin.assistance.*" />
        <x-nav-item :href="$link('admin.events.index')" label="Events" icon="calendar"
            pattern="admin.events.*" />
        <x-nav-item :href="$link('admin.textblast.index')" label="Text Blast" icon="megaphone"
            pattern="admin.textblast.*" />
        <x-nav-item :href="$link('admin.reports.index')" label="Reports" icon="file"
            pattern="admin.reports.*" />
        <x-nav-item :href="route('admin.notifications.index')" label="Notifications" icon="bell"
            pattern="admin.notifications.*" />
    </x-nav-group>

    <x-nav-item :href="route('admin.settings.edit')" label="Settings" icon="sliders"
        pattern="admin.settings.*" />
</nav>
