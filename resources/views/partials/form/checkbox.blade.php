@php($id = $id ?? str_replace(['[', ']'], '_', $name))
<label class="checkbox-row" for="{{ $id }}">
    <input id="{{ $id }}" type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $checked ?? false))>
    <span style="font-weight:700;color:#334155;">{{ $label }}</span>
</label>
