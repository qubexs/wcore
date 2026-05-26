{{-- _partials/setting_row.blade.php --}}
{{-- $key, $label, $value, $type, $hint (optional) --}}
<div class="fh-setting-row">
    <div class="fh-setting-row__label">
        <strong>{{ $label }}</strong>
        @if(isset($hint))<small>{{ $hint }}</small>@endif
    </div>
    <div class="fh-setting-row__control">
        <input type="{{ $type ?? 'text' }}"
               class="fh-input"
               value="{{ $value }}"
               @change="saveSetting('{{ $key }}', $event.target.value)">
    </div>
</div>
