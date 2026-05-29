@php
    $type = $type ?? 'text';
    $id = $id ?? str_replace(['[', ']'], '_', $name);
    $currentValue = old($name, $value ?? null);
@endphp
<label class="form-group" for="{{ $id }}">
    <span class="form-label">{{ $label }} @if($required ?? false)<span class="required">*</span>@endif</span>
    <input
        id="{{ $id }}"
        type="{{ $type }}"
        name="{{ $name }}"
        @if($type !== 'file') value="{{ $currentValue }}" @endif
        placeholder="{{ $placeholder ?? '' }}"
        @required($required ?? false)
        class="form-control"
        {{ $attributes ?? '' }}
    >
    @isset($help)<div class="form-help">{{ $help }}</div>@endisset
</label>
