<div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-200 pt-5">
    @isset($cancel)
        <a href="{{ $cancel }}" class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</a>
    @endisset
    <button type="submit" class="rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
        {{ $label ?? 'Simpan' }}
    </button>
</div>
