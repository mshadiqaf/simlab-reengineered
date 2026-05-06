import { computed, ref } from 'vue';

// ── Interfaces ────────────────────────────────────────────────────────────────

export interface ChatMessage {
    role: 'user' | 'assistant';
    content: string;
}

export interface SuggestionItem {
    value: string;
    label: string;
}

export interface Suggestion {
    type: 'options' | 'confirm';
    items: SuggestionItem[];
}

export interface ChatFormData {
    tipe_pengajuan: 'ruangan' | 'alat' | 'pengujian';
    nomor_hp?: string | null;
    judul_proyek?: string | null;
    tujuan_penggunaan?: string | null;
    dosen_pembimbing?: string | null;
    email_dosen?: string | null;
    // Ruangan
    ruangan_id?: number | null;
    tanggal_mulai?: string | null;
    tanggal_selesai?: string | null;
    waktu_mulai?: string | null;
    waktu_selesai?: string | null;
    jumlah_pengguna?: number | null;
    nama_pengguna_lainnya?: string | null;
    catatan_alat_bahan?: string | null;
    // Alat
    alat_id?: number | null;
    jumlah_dipinjam?: number | null;
    tanggal_mulai_alat?: string | null;
    tanggal_selesai_alat?: string | null;
    keperluan_spesifik?: string | null;
    durasi_jam?: number | null;
    // Pengujian
    jenis_pengujian_id?: number | null;
    nama_sampel?: string | null;
    jumlah_sampel?: number | null;
    keterangan_tambahan?: string | null;
}

// ── Session helpers ───────────────────────────────────────────────────────────

const FORM_KEY   = 'simlab_ai_form_data';
const PROMPT_KEY = 'simlab_ai_prompt';

export function saveFormDataToSession(data: ChatFormData): void {
    sessionStorage.setItem(FORM_KEY, JSON.stringify(data));
}

export function loadFormDataFromSession(): ChatFormData | null {
    const raw = sessionStorage.getItem(FORM_KEY);
    if (!raw) return null;
    try { return JSON.parse(raw) as ChatFormData; } catch { return null; }
}

export function clearSessionFormData(): void {
    sessionStorage.removeItem(FORM_KEY);
}

export function saveOriginalPrompt(text: string): void {
    sessionStorage.setItem(PROMPT_KEY, text);
}

export function loadOriginalPrompt(): string | null {
    return sessionStorage.getItem(PROMPT_KEY);
}

export function clearOriginalPrompt(): void {
    sessionStorage.removeItem(PROMPT_KEY);
}

// ── Composable ────────────────────────────────────────────────────────────────

const INITIAL_MESSAGE = 'Halo! Saya SIMLAB AI Assistant. Saya dapat membantu Anda memesan ruangan, meminjam alat, atau mengajukan layanan pengujian. Silakan ketik permintaan Anda!';

export function useChat() {
    const messages          = ref<ChatMessage[]>([{ role: 'assistant', content: INITIAL_MESSAGE }]);
    const loading           = ref(false);
    const error             = ref<string | null>(null);
    const pendingFormData   = ref<ChatFormData | null>(null);
    const currentSuggestions = ref<Suggestion | null>(null);

    // True once the user has sent at least one message
    const hasStarted = computed(() => messages.value.some(m => m.role === 'user'));

    const sendMessage = async (text: string): Promise<void> => {
        if (!text.trim() || loading.value) return;

        messages.value.push({ role: 'user', content: text });
        loading.value = true;
        error.value = null;
        currentSuggestions.value = null; // clear chips while waiting

        // Send last 10 turns as history (excluding the message we just added)
        const history = messages.value.slice(0, -1).slice(-10);

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            const res = await fetch('/api/chat/message', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                },
                credentials: 'same-origin',
                body: JSON.stringify({ message: text, history }),
            });

            if (!res.ok) {
                let errMsg = 'Gagal menghubungi server.';
                try {
                    const errData = await res.json();
                    errMsg = errData.message ?? errMsg;
                } catch { /* ignore */ }
                throw new Error(errMsg);
            }

            const data = await res.json();
            const reply: string        = data.reply       ?? 'Maaf, tidak ada respons.';
            const formData             = data.formData    ?? null;
            const suggestions: Suggestion | null = data.suggestions ?? null;

            messages.value.push({ role: 'assistant', content: reply });

            if (formData) {
                pendingFormData.value = formData;
                saveFormDataToSession(formData);
                currentSuggestions.value = null; // summary card takes over
            } else {
                currentSuggestions.value = suggestions;
            }
        } catch (e: any) {
            error.value = e.message ?? 'Terjadi kesalahan.';
            messages.value.push({ role: 'assistant', content: 'Maaf, terjadi kesalahan. Silakan coba lagi.' });
        } finally {
            loading.value = false;
        }
    };

    const clearChat = () => {
        messages.value          = [{ role: 'assistant', content: INITIAL_MESSAGE }];
        pendingFormData.value   = null;
        currentSuggestions.value = null;
        error.value             = null;
        clearSessionFormData();
        clearOriginalPrompt();
    };

    return {
        messages,
        loading,
        error,
        pendingFormData,
        currentSuggestions,
        hasStarted,
        sendMessage,
        clearChat,
    };
}
