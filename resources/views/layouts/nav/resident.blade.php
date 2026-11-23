@php
    $c = $navCounts ?? [];

    $mine = request()->routeIs('resident.complaints.*', 'resident.documents.*', 'resident.appointments.*', 'resident.queue.*', 'resident.emergency.*', 'resident.qr.*');
    $community = request()->routeIs('resident.announcements.*', 'resident.freedom-wall.*', 'resident.lost-found.*', 'resident.jobs.*', 'resident.events.*', 'resident.assistance.*');
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
        <x-nav-item :href="route('resident.emergency.index')" label="Emergency Assistance" icon="alert"
            pattern="resident.emergency.*" />
        <x-nav-item :href="route('resident.qr.show')" label="My QR Code" icon="grid"
            pattern="resident.qr.*" />
    </x-nav-group>

    <x-nav-group label="Community & programs" icon="compass" id="community" :active="$community">
        <x-nav-item :href="route('resident.announcements.index')" label="Announcements" icon="bell"
            pattern="resident.announcements.*" />
        <x-nav-item :href="route('resident.freedom-wall.index')" label="Freedom Wall" icon="chat"
            pattern="resident.freedom-wall.*" />
        <x-nav-item :href="route('resident.lost-found.index')" label="Lost & Found" icon="box"
            pattern="resident.lost-found.*" />
        <x-nav-item :href="route('resident.jobs.index')" label="Livelihood & Jobs" icon="briefcase"
            pattern="resident.jobs.*" />
        <x-nav-item :href="route('resident.events.index')" label="Events" icon="calendar"
            pattern="resident.events.*" />
        <x-nav-item :href="route('resident.assistance.index')" label="Assistance & Ayuda" icon="gift"
            pattern="resident.assistance.*" />
    </x-nav-group>

    <x-nav-item :href="route('resident.map.index')" label="Barangay Map" icon="map"
        pattern="resident.map.*" />

    <x-nav-item :href="route('resident.profile.edit')" label="My Profile" icon="user"
        pattern="resident.profile.*" />
</nav>