@php($id = $id ?? str_replace(['[', ']'], '_', $name))
<label class="form-group" for="{{ $id }}">
    <span class="form-label">{{ $label }} @if($required ?? false)<span class="required">*</span>@endif</span>
    <textarea id="{{ $id }}" name="{{ $name }}" placeholder="{{ $placeholder ?? '' }}" @required($required ?? false) class="form-control">{{ old($name, $value ?? null) }}</textarea>
    @isset($help)<div class="form-help">{{ $help }}</div>@endisset
</label>
