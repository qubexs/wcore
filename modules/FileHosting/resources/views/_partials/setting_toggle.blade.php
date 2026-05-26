{{-- modules/FileHosting/resources/views/_partials/setting_toggle.blade.php --}}
@props(['key', 'label', 'value'])

<div class="fh-setting-row fh-setting-row--toggle">
    <label class="fh-setting-row__label" for="setting-{{ $key }}">
        {{ $label }}
    </label>
    <button
        type="button"
        id="setting-{{ $key }}"
        class="fh-toggle"
        x-data="{ on: {{ $value ? 'true' : 'false' }} }"
        :class="{ 'fh-toggle--active': on }"
        @click="on = !on; saveSetting('{{ $key }}', on)"
        :aria-pressed="on"
    >
        <span class="fh-toggle__track">
            <span class="fh-toggle__thumb" :class="{ 'fh-toggle__thumb--on': on }"></span>
        </span>
    </button>
</div>
