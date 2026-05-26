{{-- _partials/setting_toggle.blade.php --}}
{{-- $key, $label, $value (bool) --}}
<div class="fh-setting-row">
    <div class="fh-setting-row__label">
        <strong>{{ $label }}</strong>
    </div>
    <div class="fh-setting-row__control">
        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
            <input type="checkbox"
                   {{ $value ? 'checked' : '' }}
                   @change="saveSetting('{{ $key }}', $event.target.checked)"
                   style="width:1.1rem;height:1.1rem;">
            <span style="font-size:.875rem">{{ $value ? 'Enabled' : 'Disabled' }}</span>
        </label>
    </div>
</div>
