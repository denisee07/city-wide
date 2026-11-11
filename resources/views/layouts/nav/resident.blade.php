@php
    $c = $navCounts ?? [];

    /* Residents came here to do four things. The panel now says so, instead
       of showing ten destinations and three headings at once and hoping the
       first-time visitor spots the right one. Every service is still exactly
       one click from the dashboard, and none was removed. */
    $mine = request()->routeIs('resident.complaints.*', 'resident.documents.*', 'resident.appointments.*', 'resident.queue.*');
    $community = request()->routeIs('resident.announcements.*', 'resident.freedom-wall.*', 'resident.lost-found.*', 'resident.jobs.*');
@endphp

<nav class="nav">
    <x-nav-item :href="route('resident.dashboard')" label="Dashboard" icon="grid"
        pattern="resident.dashboard" />

    <x-nav-group label="My requests" icon="clipboard" id="mine" :active="$mine">
        <x-nav-item :href="route('resident.complaints.index')" label="Complaints" icon="alert"
            pattern="resident.complaints.*" :count="$c['complaints'] ?? null" />
        <x-nav-item :href="route('resident.documents.index')" label="Documents & Certificates" icon="file"
            pattern="resident.documents.*" :count="$c['documents'] ?? null" />
        <x-nav-item :href="route('resident.appointments.index')" label="Appointments" icon="calendar"
            pattern="resident.appointments.*" :count="$c['appointments'] ?? null" />
        <x-nav-item :href="route('resident.queue.index')" label="Queueing" icon="ticket"
            pattern="resident.queue.*" />
    </x-nav-group>

    <x-nav-group label="Community" icon="compass" id="community" :active="$community">
        <x-nav-item :href="route('resident.announcements.index')" label="Announcements" icon="bell"
            pattern="resident.announcements.*" />
        <x-nav-item :href="route('resident.freedom-wall.index')" label="Freedom Wall" icon="chat"
            pattern="resident.freedom-wall.*" />
        <x-nav-item :href="route('resident.lost-found.index')" label="Lost & Found" icon="box"
            pattern="resident.lost-found.*" />
        <x-nav-item :href="route('resident.jobs.index')" label="Livelihood & Jobs" icon="briefcase"
            pattern="resident.jobs.*" />
    </x-nav-group>

    {{-- Same call as the admin map: a place residents look for is not
         something to file under a heading they must first open. --}}
    <x-nav-item :href="route('resident.map.index')" label="Barangay Map" icon="map"
        pattern="resident.map.*" />

    <x-nav-item :href="route('resident.profile.edit')" label="My Profile" icon="user"
        pattern="resident.profile.*" />
</nav>
