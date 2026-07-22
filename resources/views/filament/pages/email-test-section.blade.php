<div class="mt-4 p-4 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-xl space-y-4">
    <div class="flex items-center gap-x-3 pb-3 border-b border-gray-200 dark:border-gray-800">
        <div class="p-1.5 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 rounded-lg">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0l-7.5-4.615a2.25 2.25 0 0 1-1.07-1.916V6.75" />
            </svg>
        </div>
        <div>
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Uji Coba Pengiriman Email</h4>
            <p class="text-xs text-gray-500 dark:text-gray-400">Kirim email pengujian untuk memverifikasi konfigurasi Anda.</p>
        </div>
    </div>
    <div class="flex flex-col sm:flex-row gap-3 items-end">
        <div class="flex-1 max-w-md space-y-1">
            <label for="test_email_recipient" class="text-xs font-medium text-gray-700 dark:text-gray-300">
                Alamat Email Penerima:
            </label>
            <input 
                type="email" 
                id="test_email_recipient" 
                wire:model="test_email_recipient" 
                placeholder="Contoh: admin@perusahaan.com" 
                class="block w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500" 
            />
        </div>
        <button 
            type="button" 
            wire:click="sendTestEmail" 
            class="inline-flex items-center justify-center gap-1.5 px-3 py-2 text-sm font-semibold text-white bg-indigo-600 dark:bg-indigo-500 hover:bg-indigo-500 dark:hover:bg-indigo-400 rounded-lg shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition cursor-pointer"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0l-7.5-4.615a2.25 2.25 0 0 1-1.07-1.916V6.75" />
            </svg>
            Kirim Email Uji Coba
        </button>
    </div>
</div>
