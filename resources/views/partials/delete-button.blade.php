<form action="{{ $action }}" method="POST" style="display:inline" onsubmit="return confirm('{{ $confirm ?? 'Yakin ingin menghapus data ini?' }}')">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
</form>
