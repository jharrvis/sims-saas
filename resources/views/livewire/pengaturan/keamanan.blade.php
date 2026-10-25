<div class="page-stack">
    <div class="page-header management-page-header">
        <div>
            <span class="eyebrow">Pengaturan</span>
            <h2 class="management-page-title">Keamanan Akun</h2>
            <p>Autentikasi dua faktor dan password cadangan untuk akun Anda.</p>
        </div>
    </div>

    @can('sekolah.view')
        @include('livewire.pengaturan._tabs')
    @endcan

    @if ($info)
        <div class="management-alert management-alert-success" role="status" aria-live="polite">
            <span>{{ $info }}</span>
        </div>
    @endif
    @if ($error)
        <div class="rounded-xl border border-danger/30 bg-red-50 dark:bg-red-900/20 p-4 text-sm text-danger" role="alert">{{ $error }}</div>
    @endif

    <section class="card" aria-labelledby="keamanan-2fa-title">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 shrink-0 rounded-2xl {{ $enabled ? 'bg-success/10 text-success' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }} flex items-center justify-center">
                <x-icon name="shield-check" class="w-6 h-6" />
            </div>
            <div class="flex-1 min-w-0">
                <h2 id="keamanan-2fa-title" class="text-base font-bold">Autentikasi Dua Faktor (2FA)</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Status:
                    @if ($enabled)
                        <span class="font-semibold text-success">Aktif</span>
                    @else
                        <span class="font-semibold text-slate-500">Nonaktif</span>
                    @endif
                </p>

                <p class="text-xs text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                    Dengan 2FA, selain kode OTP WhatsApp Anda juga memasukkan kode 6 digit dari
                    aplikasi autentikator (mis. Google Authenticator) saat masuk. Fitur ini
                    <strong>opsional</strong> dan bisa Anda nonaktifkan kapan saja.
                </p>

                @if ($required && ! $enabled)
                    <p class="text-xs text-warning mt-2">
                        Akun operator platform Anda diwajibkan mengaktifkan 2FA oleh kebijakan sistem.
                    </p>
                @endif

                <div class="mt-5">
                    @if ($enabled)
                        @if ($disableStep === 0)
                            <button type="button" wire:click="mulaiNonaktifkan"
                                    class="rounded-lg border border-danger/40 text-danger px-4 py-2.5 text-sm font-medium hover:bg-danger/5 transition-colors">
                                Nonaktifkan 2FA
                            </button>
                        @else
                            <div class="space-y-3 max-w-sm">
                                <p class="text-xs text-slate-600 dark:text-slate-300">
                                    Masukkan kode OTP yang dikirim ke WhatsApp Anda untuk menonaktifkan 2FA.
                                </p>
                                <input type="text" inputmode="numeric" maxlength="6" wire:model="otpCode" placeholder="••••••"
                                       class="field text-center text-lg tracking-[0.4em]">
                                @error('otpCode') <p class="management-field-error" role="alert">{{ $message }}</p> @enderror
                                <div class="flex gap-2">
                                    <button type="button" wire:click="batalNonaktifkan" class="btn btn-outline flex-1">
                                        Batal
                                    </button>
                                    <button type="button" wire:click="nonaktifkan" wire:loading.attr="disabled"
                                            class="flex-1 rounded-lg bg-danger text-white px-4 py-2.5 text-sm font-semibold hover:opacity-90 transition-colors disabled:opacity-60">
                                        Konfirmasi
                                    </button>
                                </div>
                            </div>
                        @endif
                    @else
                        <a href="{{ route('2fa.setup') }}" class="btn btn-primary">
                            <x-icon name="shield" class="w-4 h-4" /> Aktifkan 2FA
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Password akun: cadangan bila login WhatsApp bermasalah. --}}
    <livewire:pengaturan.password-setup />
</div>
