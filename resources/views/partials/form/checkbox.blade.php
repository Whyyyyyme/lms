<label class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
    <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $checked ?? false)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
    <span class="text-sm font-semibold text-slate-700">{{ $label }}</span>
</label>
