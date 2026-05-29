@if ($errors->any())
    <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
        <p class="font-bold">Ada data yang perlu diperbaiki:</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
