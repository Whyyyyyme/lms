<div class="form-actions">
    @isset($cancel)
        <a href="{{ $cancel }}" class="btn">Batal</a>
    @endisset
    <button type="submit" class="btn btn-primary">{{ $label ?? 'Simpan' }}</button>
</div>
