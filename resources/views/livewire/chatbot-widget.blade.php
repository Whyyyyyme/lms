<div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">AI Chatbot Praktikum</h2>
            <p class="text-sm text-slate-500">Tanya materi, tugas, deadline, atau minta penjelasan sederhana.</p>
        </div>

        <button
            type="button"
            wire:click="clearHistory"
            class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
        >
            Hapus Riwayat
        </button>
    </div>

    <div class="h-[480px] space-y-4 overflow-y-auto bg-slate-50 p-5" wire:poll.15s="loadHistories">
        @forelse ($histories as $history)
            <div class="flex {{ $history['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[82%] rounded-2xl px-4 py-3 text-sm leading-relaxed {{ $history['role'] === 'user' ? 'bg-blue-600 text-white' : 'bg-white text-slate-800 shadow-sm border border-slate-200' }}">
                    <div class="whitespace-pre-wrap">{{ $history['message'] }}</div>
                    <div class="mt-2 text-[11px] opacity-70">{{ $history['time'] }}</div>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 bg-white p-6 text-center text-sm text-slate-500">
                Belum ada percakapan. Coba tanya: <strong>“Tugas apa yang belum saya kumpulkan?”</strong>
            </div>
        @endforelse
    </div>

    <form wire:submit.prevent="send" class="border-t border-slate-200 p-4">
        <div class="flex gap-3">
            <textarea
                wire:model.defer="message"
                rows="2"
                class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="Tulis pertanyaan kamu di sini..."
            ></textarea>

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="send"
                class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
            >
                <span wire:loading.remove wire:target="send">Kirim</span>
                <span wire:loading wire:target="send">...</span>
            </button>
        </div>

        @error('message')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </form>
</div>
