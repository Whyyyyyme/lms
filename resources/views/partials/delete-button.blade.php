<form action="{{ $action }}" method="POST" onsubmit="return confirm('{{ $confirm ?? 'Yakin ingin menghapus data ini?' }}')" class="inline">
    @csrf
    @method('DELETE')
    <button type="submit" class="rounded-xl bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100">Hapus</button>
</form>
