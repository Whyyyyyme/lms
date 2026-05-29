@if ($errors->any())
    <div class="alert alert-error">
        <strong>Ada data yang perlu diperbaiki:</strong>
        <ul style="margin:8px 0 0 18px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
