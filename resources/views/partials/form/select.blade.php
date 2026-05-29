@php($id = $id ?? str_replace(['[', ']'], '_', $name))
<label class="form-group" for="{{ $id }}">
    <span class="form-label">{{ $label }} @if($required ?? false)<span class="required">*</span>@endif</span>
    <select id="{{ $id }}" name="{{ $name }}" @required($required ?? false) class="form-control">
