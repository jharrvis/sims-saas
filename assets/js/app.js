// Database Mock for Server-Side Datatable Simulation
const mockDatabase = [
    { id: 1, name: 'Ahmad Fauzi', nis: '102938', level: 'Kelas I', date: '12 Okt 2026', status: 'Aktif', color: '#3c50e0' },
    { id: 2, name: 'Siti Nurhaliza', nis: '102939', level: 'Kelas Ia', date: '11 Okt 2026', status: 'Pending', color: '#219653' },
    { id: 3, name: 'Reza Pratama', nis: '102940', level: 'Kelas II', date: '10 Okt 2026', status: 'Aktif', color: '#d34053' },
    { id: 4, name: 'Budi Santoso', nis: '102941', level: 'Kelas IIa', date: '09 Okt 2026', status: 'Lulus', color: '#f0950c' },
    { id: 5, name: 'Dewi Lestari', nis: '102942', level: 'Kelas III', date: '08 Okt 2026', status: 'Aktif', color: '#8b5cf6' },
    { id: 6, name: 'Andi Wijaya', nis: '102943', level: 'Kelas IIIa', date: '07 Okt 2026', status: 'Aktif', color: '#06b6d4' },
    { id: 7, name: 'Citra Kirana', nis: '102944', level: 'Kelas IV', date: '06 Okt 2026', status: 'Pending', color: '#ec4899' },
    { id: 8, name: 'Dian Sastro', nis: '102945', level: 'Kelas IVa', date: '05 Okt 2026', status: 'Aktif', color: '#14b8a6' },
    { id: 9, name: 'Eka Putra', nis: '102946', level: 'Kelas V', date: '04 Okt 2026', status: 'Aktif', color: '#f59e0b' },
    { id: 10, name: 'Fahri Hamzah', nis: '102947', level: 'Kelas Va', date: '03 Okt 2026', status: 'Lulus', color: '#6366f1' },
    { id: 11, name: 'Gita Gutawa', nis: '102948', level: 'Kelas VI', date: '02 Okt 2026', status: 'Aktif', color: '#10b981' },
    { id: 12, name: 'Hasan Basri', nis: '102949', level: 'Kelas VIa', date: '01 Okt 2026', status: 'Aktif', color: '#ef4444' },
];

// Global Alpine Data
function appData() {
    return {
        isDarkMode: false,
        sidebarOpen: false,
        sidebarCollapsed: false,
        globalLoading: true,
        contentLoading: false,

        // Global Search / Command Palette
        searchOpen: false,
        searchQuery: '',
        searchResults: [],
        searchLinks: [
            { icon: 'layout-dashboard', label: 'Dashboard', desc: 'Ringkasan operasional sekolah', href: 'index.html', color: 'bg-primary/10 text-primary' },
            { icon: 'users', label: 'Data Siswa', desc: 'Kelola data siswa per kelas', href: 'data-siswa.html', color: 'bg-blue-100 dark:bg-blue-900/20 text-blue-600' },
            { icon: 'contact', label: 'Data Guru', desc: 'Manajemen guru dan staf', href: 'data-guru.html', color: 'bg-purple-100 dark:bg-purple-900/20 text-purple-600' },
            { icon: 'check-square', label: 'Absensi Siswa', desc: 'Input dan rekap kehadiran', href: 'absensi.html', color: 'bg-success/10 text-success' },
            { icon: 'file-bar-chart', label: 'Data Nilai', desc: 'Leger dan rekap nilai semester', href: 'nilai.html', color: 'bg-warning/10 text-warning' },
            { icon: 'wallet', label: 'Keuangan SPP', desc: 'Tagihan dan pembayaran siswa', href: 'keuangan.html', color: 'bg-emerald-100 dark:bg-emerald-900/20 text-emerald-600' },
            { icon: 'clipboard-list', label: 'PPDB Online', desc: 'Penerimaan peserta didik baru', href: 'ppdb.html', color: 'bg-orange-100 dark:bg-orange-900/20 text-orange-500' },
            { icon: 'printer', label: 'Laporan', desc: 'Export Excel dan PDF', href: 'laporan.html', color: 'bg-red-100 dark:bg-red-900/20 text-red-500' },
            { icon: 'settings', label: 'Pengaturan', desc: 'Profil sekolah dan sistem', href: 'pengaturan.html', color: 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300' },
        ],
        openSearch() {
            this.searchOpen = true;
            this.searchQuery = '';
            this.searchResults = this.searchLinks;
            this.$nextTick(() => { const el = document.getElementById('global-search-input'); if (el) el.focus(); });
        },
        closeSearch() { this.searchOpen = false; },
        filterSearch() {
            const q = this.searchQuery.toLowerCase();
            this.searchResults = q ? this.searchLinks.filter(l => l.label.toLowerCase().includes(q) || l.desc.toLowerCase().includes(q)) : this.searchLinks;
        },

        // Modals
        loginModalOpen: false,
        demoModalOpen: false,
        confirmModalOpen: false,

        // Toast
        toasts: [],
        addToast(title, message, type = 'success') {
            const id = Date.now();
            this.toasts.push({ id, title, message, type });
            setTimeout(() => {
                this.removeToast(id);
            }, 3000);
        },
        removeToast(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        },

        initApp() {
            // Check local storage or system preference for dark mode
            if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                this.isDarkMode = true;
                document.documentElement.classList.add('dark');
            } else {
                this.isDarkMode = false;
                document.documentElement.classList.remove('dark');
            }

            // Simulate Initial App Load
            setTimeout(() => {
                this.globalLoading = false;
                lucide.createIcons();
                this.initCharts();
            }, 800);
        },

        toggleDarkMode() {
            this.isDarkMode = !this.isDarkMode;
            if (this.isDarkMode) {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            }
            this.initCharts(); // Re-render charts for dark mode
        },

        refreshData() {
            this.contentLoading = true;
            setTimeout(() => {
                this.contentLoading = false;
                window.dispatchEvent(new CustomEvent('refresh-datatable'));
            }, 1200);
        },

        initCharts() {
            if(typeof Chart === 'undefined') return;

            Chart.defaults.font.family = 'Inter, sans-serif';
            Chart.defaults.color = this.isDarkMode ? '#94a3b8' : '#64748b'; 

            const barChartEl = document.getElementById('attendanceChart');
            if(barChartEl) {
                let chartStatus = Chart.getChart("attendanceChart");
                if (chartStatus != undefined) {
                  chartStatus.destroy();
                }
                const ctxBar = barChartEl.getContext('2d');
                new Chart(ctxBar, {
                    type: 'bar',
                    data: {
                        labels: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'],
                        datasets: [
                            {
                                label: 'Hadir',
                                data: [98, 95, 96, 92, 94],
                                backgroundColor: '#3c50e0',
                                borderRadius: 4,
                                barPercentage: 0.6,
                            },
                            {
                                label: 'Absen/Sakit',
                                data: [2, 5, 4, 8, 6],
                                backgroundColor: '#d34053',
                                borderRadius: 4,
                                barPercentage: 0.6,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                align: 'end',
                                labels: { usePointStyle: true, boxWidth: 8 }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                grid: { color: this.isDarkMode ? '#334155' : '#e2e8f0', drawBorder: false }, 
                            },
                            x: {
                                grid: { display: false, drawBorder: false }
                            }
                        }
                    }
                });
            }

            const pieChartEl = document.getElementById('studentPieChart');
            if(pieChartEl) {
                let chartStatus = Chart.getChart("studentPieChart");
                if (chartStatus != undefined) {
                  chartStatus.destroy();
                }
                const ctxPie = pieChartEl.getContext('2d');
                new Chart(ctxPie, {
                    type: 'doughnut',
                    data: {
                        labels: ['Kelas I - III', 'Kelas IV - VI'],
                        datasets: [{
                            data: [1800, 1656],
                            backgroundColor: ['#3b82f6', '#818cf8'],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '75%',
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }
        }
    }
}

// Datatable Component Logic
function datatableManager() {
    return {
        items: [],
        search: '',
        page: 1,
        perPage: 4,
        totalItems: 0,
        tableLoading: false,

        get totalPages() {
            return Math.ceil(this.totalItems / this.perPage);
        },
        get startRecord() {
            return this.totalItems === 0 ? 0 : (this.page - 1) * this.perPage + 1;
        },
        get endRecord() {
            return Math.min(this.page * this.perPage, this.totalItems);
        },

        init() {
            this.$watch('search', () => {
                this.page = 1;
                this.fetchData();
            });
            window.addEventListener('refresh-datatable', () => {
                this.fetchData();
            });
        },

        async fetchData() {
            this.tableLoading = true;
            await new Promise(resolve => setTimeout(resolve, 600));

            let filtered = mockDatabase.filter(item => {
                return item.name.toLowerCase().includes(this.search.toLowerCase()) || 
                       item.nis.includes(this.search);
            });

            this.totalItems = filtered.length;
            if (this.page > this.totalPages && this.totalPages > 0) this.page = this.totalPages;

            let start = (this.page - 1) * this.perPage;
            this.items = filtered.slice(start, start + this.perPage);

            this.tableLoading = false;
            setTimeout(() => lucide.createIcons(), 10);
        },

        nextPage() {
            if (this.page < this.totalPages) {
                this.page++;
                this.fetchData();
            }
        },
        
        prevPage() {
            if (this.page > 1) {
                this.page--;
                this.fetchData();
            }
        },
        
        goToPage(p) {
            if(p >= 1 && p <= this.totalPages) {
                this.page = p;
                this.fetchData();
            }
        }
    }
}
