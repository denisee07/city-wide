@php
    $c = $navCounts ?? [];

    /* The console can be narrowed to one barangay with ?barangay=<id>; carry
       that choice through the sidebar so drilling down doesn't reset it. */
    $scope = \App\Support\BarangayFilter::params();
    $link = fn (string $route) => route($route, $scope);

    /* Five sections, in the order the console is actually used: the case
       boards, the counter, what goes out to residents, the city roster, and
       the paper those generate. The active group opens itself; the rest stay
       folded. */
    $cases = request()->routeIs('admin.complaints.*', 'admin.requests.*', 'admin.appointments.*');
    $publishing = request()->routeIs('admin.announcements.*', 'admin.textblast.*', 'admin.events.*', 'admin.jobs.*', 'admin.assistance.*', 'admin.lost-found.*');
    $managing = request()->routeIs('admin.barangays.*', 'admin.users.*', 'admin.census.*', 'admin.emergency.*');
@endphp

<nav class="nav">
    <x-nav-item :href="route('admin.dashboard')" label="Dashboard" icon="grid"
        pattern="admin.dashboard" />

    {{-- 1st — the case boards --}}
    <x-nav-group label="Cases & Requests" icon="layers" id="cases" :active="$cases">
        <x-nav-item :href="$link('admin.complaints.index')" label="Complaints" icon="alert"
            pattern="admin.complaints.*" :count="$c['open_complaints'] ?? null" />
        <x-nav-item :href="$link('admin.requests.index')" label="Document Requests" icon="file"
            pattern="admin.requests.*" :count="$c['pending_docs'] ?? null" />
        <x-nav-item :href="$link('admin.appointments.index')" label="Appointments" icon="calendar"
            pattern="admin.appointments.*" :count="$c['pending_appts'] ?? null" />
    </x-nav-group>

    {{-- 2nd — the counter --}}
    <x-nav-item :href="$link('admin.queue.index')" label="Queueing" icon="ticket"
        pattern="admin.queue.*" :count="$c['waiting'] ?? null" />

    {{-- 3rd — what goes out to residents --}}
    <x-nav-group label="Publishing" icon="megaphone" id="publishing" :active="$publishing">
        <x-nav-item :href="$link('admin.announcements.index')" label="Announcements" icon="bell"
            pattern="admin.announcements.*" />
        <x-nav-item :href="$link('admin.events.index')" label="Events" icon="calendar"
            pattern="admin.events.*" />
        <x-nav-item :href="$link('admin.jobs.index')" label="Livelihood & Jobs" icon="briefcase"
            pattern="admin.jobs.*" />
        <x-nav-item :href="$link('admin.assistance.index')" label="Assistance & Ayuda" icon="gift"
            pattern="admin.assistance.*" />
        <x-nav-item :href="$link('admin.lost-found.index')" label="Lost & Found" icon="box"
            pattern="admin.lost-found.*" />
        <x-nav-item :href="$link('admin.textblast.index')" label="Text Blast" icon="send"
            pattern="admin.textblast.*" />
    </x-nav-group>

    {{-- 4th — the city roster --}}
    <x-nav-group label="City Management" icon="users" id="managing" :active="$managing">
        <x-nav-item :href="$link('admin.barangays.index')" label="Barangays" icon="map"
            pattern="admin.barangays.*" :count="$c['barangays'] ?? null" />
        <x-nav-item :href="$link('admin.users.index')" label="Accounts" icon="users"
            pattern="admin.users.*" />
        <x-nav-item :href="$link('admin.census.index')" label="Census" icon="users"
            pattern="admin.census.*" />
        <x-nav-item :href="$link('admin.emergency.index')" label="Emergency / DRRM" icon="alert"
            pattern="admin.emergency.*" />
    </x-nav-group>

    {{-- 5th — the paper it all produces --}}
    <x-nav-item :href="route('admin.reports.index')" label="Reports & Records" icon="printer"
        pattern="admin.reports.*" />

    <x-nav-item :href="route('admin.settings.edit')" label="Settings" icon="sliders"
        pattern="admin.settings.*" />
</nav>