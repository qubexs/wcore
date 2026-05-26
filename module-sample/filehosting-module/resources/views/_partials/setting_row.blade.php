{{-- modules/FileHosting/resources/views/_partials/setting_row.blade.php --}}
@props(['key', 'label', 'value', 'type' => 'text', 'hint' => null])

<div class="fh-setting-row">
    <label class="fh-setting-row__label" for="setting-{{ $key }}">
        {{ $label }}
        @if($hint)
            <span class="fh-setting-row__hint">{{ $hint }}</span>
        @endif
    </label>
    <input
        type="{{ $type }}"
        id="setting-{{ $key }}"
        class="fh-setting-row__input"
        value="{{ $value }}"
        @change="saveSetting('{{ $key }}', $event.target.value)"
    />
</div>
