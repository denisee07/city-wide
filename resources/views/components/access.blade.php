{{--
    Display & accessibility control.

    Rendered on every surface a person can land on: both portals' top bar, the
    public site nav, and the two auth screens — because someone who needs high
    contrast needs it *before* signing in, not after.

    Real radio inputs and checkboxes rather than styled divs: the arrow keys,
    screen readers and Windows Magnifier then work here for free. public/
    js/access.js reads the data-access-key attributes and owns the state.

    The panel is closed with the `hidden` attribute, not display:none in a
    stylesheet, so a page with JavaScript switched off simply shows a button
    that does nothing, instead of a permanently open panel nobody asked for.
--}}
@props([])

@php
    $id = 'access-'.(\Illuminate\Support\Facades\Request::path() === '/' ? 'home' : str_replace('/', '-', \Illuminate\Support\Facades\Request::path()));
@endphp

<div class="access-wrap" data-access>
    <button type="button"
            class="access-btn"
            data-access-toggle
            aria-expanded="false"
            aria-haspopup="true"
            aria-controls="{{ $id }}">
        <x-icon name="eye" size="15" />
        <span class="access-btn-label">Display</span>
    </button>

    <div class="access-panel" id="{{ $id }}" role="group" aria-label="Display and accessibility" data-access-panel hidden>
        <fieldset>
            <legend>Theme</legend>
            <div class="access-opts">
                <label><input type="radio" name="{{$id}}-theme" data-access-key="theme" value="default"><span>City (default)</span></label>
                <label><input type="radio" name="{{$id}}-theme" data-access-key="theme" value="terminal"><span>Terminal</span></label>
            </div>
        </fieldset>

        <fieldset>
            <legend>Text size</legend>
            <div class="access-opts">
                <label><input type="radio" name="{{$id}}-text" data-access-key="text" value="s"><span>Small</span></label>
                <label><input type="radio" name="{{$id}}-text" data-access-key="text" value="normal"><span>Normal</span></label>
                <label><input type="radio" name="{{$id}}-text" data-access-key="text" value="l"><span>Large</span></label>
                <label><input type="radio" name="{{$id}}-text" data-access-key="text" value="xl"><span>Extra large</span></label>
            </div>
        </fieldset>

        <fieldset>
            <legend>Contrast</legend>
            <div class="access-opts">
                <label><input type="radio" name="{{$id}}-contrast" data-access-key="contrast" value="normal"><span>Standard</span></label>
                <label><input type="radio" name="{{$id}}-contrast" data-access-key="contrast" value="more"><span>High contrast</span></label>
            </div>
        </fieldset>

        <fieldset>
            <legend>Motion</legend>
            <div class="access-opts">
                <label><input type="radio" name="{{$id}}-motion" data-access-key="motion" value="auto"><span>Automatic</span></label>
                <label><input type="radio" name="{{$id}}-motion" data-access-key="motion" value="reduce"><span>Reduce</span></label>
                <label><input type="radio" name="{{$id}}-motion" data-access-key="motion" value="full"><span>Allow</span></label>
            </div>
        </fieldset>

        <fieldset>
            <legend>Links &amp; buttons</legend>
            <div class="access-opts">
                <label><input type="checkbox" data-access-key="links" value="on"><span>Underline links</span></label>
                <label><input type="radio" name="{{$id}}-targets" data-access-key="targets" value="large"><span>Bigger tap targets</span></label>
            </div>
        </fieldset>

        <button type="button" class="access-reset" data-access-reset>Reset everything to default</button>
    </div>
</div>
