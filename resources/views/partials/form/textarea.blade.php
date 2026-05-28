<label class="block">
    <span class="text-sm font-semibold text-slate-700">{{ $label }} @if($required ?? false)<span class="text-red-500">*</span>@endif</span>
    <textarea name="{{ $name }}" rows="5" placeholder="{{ $placeholder ?? '' }}" @required($required ?? false)
        class="mt-1 w-full rounded-2xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old($name, $value ?? null) }}</textarea>
</label>
