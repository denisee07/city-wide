{{--
    The name/avatar cell of a user row: initials, e-mail, the "You" badge and
    purok. Rendered by the board on first load and by UserController after an
    ajax role/status save, because the avatar colour is derived from the role
    and would otherwise disagree with the badge next to it.

    Contents only - the row wraps them in a <td data-identity-cell>.
--}}
<span class="user-pill">
    <span class="avatar {{ $user->isOfficial() ? 'violet' : 'neutral' }}">
        {{ $user->initials }}
    </span>
    <span>
        <span class="nm">
            {{ $user->name }}
            @if ($user->id === $me)
                <span class="badge badge-cyan">You</span>
            @endif
        </span>
        <span class="rl">{{ $user->email }}</span>
    </span>
</span>
@if ($user->purok)
    <div class="tiny dim" style="padding-left:.55rem">Purok: {{ $user->purok }}</div>
@endif
