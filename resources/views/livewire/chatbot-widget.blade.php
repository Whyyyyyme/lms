<div class="rounded-2xl border border-slate-200 bg-white shadow-sm" x-data x-on:chatbot-message-sent.window="$nextTick(() => { const box = $refs.chatBox; if (box) box.scrollTop = box.scrollHeight })">
    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
        <div>
            <h2 class="text-base font-semibold text-slate-900">AI Chatbot Praktikum</h2>
            <p class="text-sm text-slate-500">Tanyakan materi, tugas, deadline, atau minta penjelasan sederhana.</p>
        </div>

        @if (count($histories) > 0)
            <button
                type="button"
                wire:click="clearHistory"
                wire:confirm="Hapus semua riwayat chat?"
                class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50"
            >
                Hapus Riwayat
            </button>
        @endif
    </div>

    @if ($errorMessage)
        <div class="mx-5 mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
            {{ $errorMessage }}
        </div>
    @endif

    <div x-ref="chatBox" class="h-[460px] space-y-4 overflow-y-auto px-5 py-5" wire:poll.15s="loadHistories">
        @forelse ($histories as $history)
            @php($isUser = $history['role'] === 'user')

            <div class="flex {{ $isUser ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[85%] rounded-2xl px-4 py-3 text-sm leading-6 {{ $isUser ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-800' }}">
                    <div class="mb-1 text-xs font-semibold {{ $isUser ? 'text-indigo-100' : 'text-slate-500' }}">
                        {{ $isUser ? 'Kamu' : 'AI Praktikum' }} · {{ $history['created_at'] }}
                    </div>
                    <div class="whitespace-pre-line">{{ $history['message'] }}</div>
                </div>
            </div>
        @empty
            <div class="flex h-full items-center justify-center text-center">
                <div>
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-indigo-50 text-xl">🤖</div>
                    <p class="font-medium text-slate-800">Belum ada percakapan.</p>
                    <p class="mt-1 text-sm text-slate-500">Contoh: “Tugas apa yang belum saya kumpulkan?”</p>
                </div>
            </div>
        @endforelse

        @if ($isSending)
            <div class="flex justify-start">
                <div class="rounded-2xl bg-slate-100 px-4 py-3 text-sm text-slate-600">
                    AI sedang menyiapkan jawaban...
                </div>
            </div>
        @endif
    </div>

    <form wire:submit="sendMessage" class="border-t border-slate-200 p-4">
        <div class="flex gap-3">
            <textarea
                wire:model.defer="message"
                rows="2"
                class="block w-full resize-none rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="Tulis pertanyaan kamu di sini..."
            ></textarea>

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="sendMessage"
                class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
            >
                Kirim
            </button>
        </div>

        @error('message')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </form>
</div>
