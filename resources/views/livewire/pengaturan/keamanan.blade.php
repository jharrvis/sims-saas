<div class="max-w-3xl">
    @can('sekolah.view')
        @include('livewire.pengaturan._tabs')
    @endcan

    @if ($info)
        <div class="mb-4 rounded-lg bg-success/10 text-success px-4 py-2.5 text-sm" role="status">{{ $info }}</div>
    @endif
    @if ($error)
        <div class="mb-4 rounded-lg bg-danger/10 text-danger px-4 py-2.5 text-sm" role="alert">{{ $error }}</div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white dark:bg-boxdark dark:border-slate-700 p-5 sm:p-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 shrink-0 rounded-2xl {{ $enabled ? 'bg-success/10 text-success' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }} flex items-center justify-center">
                <x-icon name="shield-check" class="w-6 h-6" />
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-base font-bold text-slate-800 dark:text-white">Autentikasi Dua Faktor (2FA)</h2>
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
                                       class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 px-3.5 text-center text-lg tracking-[0.4em] outline-none focus:border-primary dark:text-white">
                                @error('otpCode') <p class="text-xs text-danger">{{ $message }}</p> @enderror
                                <div class="flex gap-2">
                                    <button type="button" wire:click="batalNonaktifkan"
                                            class="flex-1 rounded-lg border border-slate-200 dark:border-slate-700 px-4 py-2.5 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
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
                        <a href="{{ route('2fa.setup') }}"
                           class="inline-flex items-center gap-2 rounded-lg bg-primary text-white px-4 py-2.5 text-sm font-semibold hover:bg-blue-700 transition-colors">
                            <x-icon name="shield" class="w-4 h-4" /> Aktifkan 2FA
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
