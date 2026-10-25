/* SIMS demo dataset. Ganti data ini dengan Laravel API / Blade-provided JSON. */
var SIMS = (window.SIMS = window.SIMS || {});
SIMS.menuGroups = [
 {label:'UTAMA',items:[{id:'dashboard',title:'Dashboard',icon:'layout-dashboard'},{id:'platform',title:'Panel Platform',icon:'shield-check'}]},
 {label:'DATA MASTER',items:[{id:'students',title:'Data Siswa',icon:'users'},{id:'guardians',title:'Data Wali',icon:'user-round-check'},{id:'teachers',title:'Data Guru',icon:'contact-round'},{id:'classes',title:'Data Kelas',icon:'school'},{id:'subjects',title:'Mata Pelajaran',icon:'book-open'}]},
 {label:'AKADEMIK',items:[{id:'schedule',title:'Jadwal Pelajaran',icon:'calendar-days'},{id:'attendance',title:'Absensi Siswa',icon:'clipboard-check'},{id:'grades',title:'Data Nilai',icon:'award'},{id:'gradecomponents',title:'Komponen Nilai',icon:'chart-pie'}]},
 {label:'MANAJEMEN',items:[{id:'studentaffairs',title:'Kesiswaan',icon:'heart-handshake'},{id:'finance',title:'Keuangan',icon:'wallet',badge:'8'},{id:'facilities',title:'Sarana & Layanan',icon:'building-2'}]},
 {label:'INFORMASI & SISTEM',items:[{id:'calendar',title:'Kalender Akademik',icon:'calendar-range'},{id:'announcements',title:'Pengumuman',icon:'megaphone',badge:'3'},{id:'reports',title:'Laporan',icon:'file-bar-chart-2'},{id:'designsystem',title:'Design System',icon:'component'},{id:'guide',title:'Panduan Integrasi',icon:'book-text'}]}
];
SIMS.quickActions=[{title:'Tambah Siswa',icon:'user-plus',page:'students',new:true},{title:'Input Nilai',icon:'file-plus',page:'grades',new:true},{title:'Absensi Hari Ini',icon:'clipboard-check',page:'attendance'},{title:'Cetak Laporan',icon:'printer',page:'reports'}];
SIMS.notices=[{title:'Ujian Tengah Semester dimulai pekan depan',time:'22 Okt 2026 · 08:30',icon:'calendar-days',tag:'Akademik'},{title:'Pengumpulan berkas administrasi siswa',time:'21 Okt 2026 · 13:10',icon:'file-text',tag:'Administrasi'},{title:'Pembaruan jadwal kelas VIII',time:'20 Okt 2026 · 10:15',icon:'clock',tag:'Jadwal'},{title:'Pembayaran SPP Oktober',time:'19 Okt 2026 · 09:20',icon:'wallet',tag:'Keuangan'}];
SIMS.entityConfigs={
 students:{title:'Data Siswa',desc:'Kelola identitas, status, dan penempatan kelas seluruh peserta didik.',icon:'users',singular:'Siswa',columns:[['nis','NIS'],['name','Nama Siswa'],['class','Kelas'],['gender','Gender'],['guardian','Wali Siswa'],['status','Status']],fields:[['nis','Nomor Induk Siswa','text'],['name','Nama Lengkap','text'],['class','Kelas','select:VII A|VII B|VIII A|VIII B|IX A|IX B'],['gender','Jenis Kelamin','select:Laki-laki|Perempuan'],['guardian','Nama Wali','text'],['status','Status','select:Aktif|Nonaktif|Pindah']]},
 guardians:{title:'Data Wali',desc:'Informasi orang tua, wali, kontak, dan relasi dengan siswa.',icon:'user-round-check',singular:'Wali',columns:[['name','Nama Wali'],['student','Siswa'],['relation','Hubungan'],['phone','Telepon'],['profession','Pekerjaan'],['status','Status']],fields:[['name','Nama Wali','text'],['student','Nama Siswa','text'],['relation','Hubungan','select:Ayah|Ibu|Wali'],['phone','Telepon','tel'],['profession','Pekerjaan','text'],['status','Status','select:Aktif|Nonaktif']]},
 teachers:{title:'Data Guru',desc:'Administrasi tenaga pendidik, bidang studi, dan status kepegawaian.',icon:'contact-round',singular:'Guru',columns:[['nip','NIP'],['name','Nama Guru'],['subject','Bidang Studi'],['phone','Telepon'],['status','Status']],fields:[['nip','NIP','text'],['name','Nama Guru','text'],['subject','Bidang Studi','text'],['phone','Telepon','tel'],['status','Status','select:Aktif|Cuti|Nonaktif']]},
 classes:{title:'Data Kelas',desc:'Atur rombongan belajar, wali kelas, dan kapasitas siswa.',icon:'school',singular:'Kelas',columns:[['name','Nama Kelas'],['grade','Tingkat'],['teacher','Wali Kelas'],['students','Jumlah Siswa'],['room','Ruangan'],['status','Status']],fields:[['name','Nama Kelas','text'],['grade','Tingkat','select:VII|VIII|IX'],['teacher','Wali Kelas','text'],['students','Jumlah Siswa','number'],['room','Ruang','text'],['status','Status','select:Aktif|Nonaktif']]},
 subjects:{title:'Mata Pelajaran',desc:'Daftar mata pelajaran beserta kode, pengampu, dan beban jam.',icon:'book-open',singular:'Mata Pelajaran',columns:[['code','Kode'],['name','Mata Pelajaran'],['teacher','Guru Pengampu'],['hours','JP / Minggu'],['group','Kelompok'],['status','Status']],fields:[['code','Kode Mapel','text'],['name','Nama Mapel','text'],['teacher','Guru Pengampu','text'],['hours','Jam Pelajaran','number'],['group','Kelompok','select:Wajib|Muatan Lokal|Pilihan'],['status','Status','select:Aktif|Nonaktif']]},
 grades:{title:'Data Nilai',desc:'Input, koreksi, dan pantau pencapaian akademik peserta didik.',icon:'award',singular:'Nilai',columns:[['student','Nama Siswa'],['class','Kelas'],['subject','Mapel'],['assessment','Penilaian'],['score','Nilai'],['status','Status']],fields:[['student','Nama Siswa','text'],['class','Kelas','select:VII A|VII B|VIII A|VIII B|IX A'],['subject','Mata Pelajaran','text'],['assessment','Jenis Penilaian','select:Tugas|UH|UTS|UAS'],['score','Nilai (0 - 100)','number'],['status','Status','select:Tuntas|Remedial']]},
 gradecomponents:{title:'Komponen Nilai',desc:'Kelola bobot penilaian dan skema perhitungan rapor.',icon:'chart-pie',singular:'Komponen',columns:[['name','Komponen'],['subject','Mata Pelajaran'],['weight','Bobot (%)'],['min','KKM'],['semester','Semester'],['status','Status']],fields:[['name','Komponen','text'],['subject','Mata Pelajaran','text'],['weight','Bobot (%)','number'],['min','KKM','number'],['semester','Semester','select:Ganjil|Genap'],['status','Status','select:Aktif|Nonaktif']]},
 finance:{title:'Keuangan Sekolah',desc:'Pantau pembayaran, tagihan siswa, dan rekonsiliasi transaksi.',icon:'wallet',singular:'Tagihan',columns:[['invoice','No. Tagihan'],['student','Nama Siswa'],['type','Jenis'],['amount','Jumlah'],['due','Jatuh Tempo'],['status','Status']],fields:[['invoice','Nomor Tagihan','text'],['student','Nama Siswa','text'],['type','Jenis Tagihan','select:SPP|Kegiatan|Seragam|Lainnya'],['amount','Nominal (Rp)','number'],['due','Jatuh Tempo','date'],['status','Status','select:Lunas|Belum Bayar|Sebagian']]},
 facilities:{title:'Sarana & Layanan',desc:'Inventaris ruang, aset sekolah, kondisi, dan pemeliharaan.',icon:'building-2',singular:'Inventaris',columns:[['code','Kode Aset'],['name','Nama Aset'],['category','Kategori'],['location','Lokasi'],['condition','Kondisi'],['status','Status']],fields:[['code','Kode Aset','text'],['name','Nama Aset','text'],['category','Kategori','select:Elektronik|Meubel|Alat Lab|Bangunan|Lainnya'],['location','Lokasi','text'],['condition','Kondisi','select:Baik|Perlu Perbaikan|Rusak'],['status','Status','select:Aktif|Nonaktif']]}
};
SIMS.rows={
 students:[
 {nis:'2601001',name:'Aditya Pratama',class:'VII A',gender:'Laki-laki',guardian:'Hendra Pratama',status:'Aktif'},
 {nis:'2601002',name:'Nadia Putri',class:'VII A',gender:'Perempuan',guardian:'Yuni Lestari',status:'Aktif'},
 {nis:'2601003',name:'Rizky Ramadhan',class:'VIII B',gender:'Laki-laki',guardian:'Supriyadi',status:'Aktif'},
 {nis:'2601004',name:'Salsabila Zahra',class:'IX A',gender:'Perempuan',guardian:'Sri Wahyuni',status:'Aktif'},
 {nis:'2601005',name:'Bima Mahendra',class:'VIII A',gender:'Laki-laki',guardian:'Dwi Mahendra',status:'Aktif'},
 {nis:'2601006',name:'Alya Maharani',class:'IX B',gender:'Perempuan',guardian:'Maharani Sari',status:'Aktif'},
 {nis:'2601007',name:'Fajar Nugroho',class:'VII B',gender:'Laki-laki',guardian:'Budi Nugroho',status:'Nonaktif'},
 {nis:'2601008',name:'Keysha Amalia',class:'VII A',gender:'Perempuan',guardian:'Reni Amalia',status:'Aktif'},
 {nis:'2601009',name:'Farhan Akbar',class:'IX A',gender:'Laki-laki',guardian:'Dedi Akbar',status:'Aktif'},
 {nis:'2601010',name:'Intan Permata',class:'VIII B',gender:'Perempuan',guardian:'Siti Nuraini',status:'Aktif'},
 {nis:'2601011',name:'Putra Wijaya',class:'VII B',gender:'Laki-laki',guardian:'Andi Wijaya',status:'Aktif'},
 {nis:'2601012',name:'Citra Lestari',class:'IX B',gender:'Perempuan',guardian:'Tri Lestari',status:'Pindah'}],
 guardians:[{name:'Hendra Pratama',student:'Aditya Pratama',relation:'Ayah',phone:'0812-3401-2701',profession:'Wiraswasta',status:'Aktif'},{name:'Yuni Lestari',student:'Nadia Putri',relation:'Ibu',phone:'0812-3401-2702',profession:'Guru',status:'Aktif'},{name:'Supriyadi',student:'Rizky Ramadhan',relation:'Ayah',phone:'0812-3401-2703',profession:'Karyawan',status:'Aktif'},{name:'Sri Wahyuni',student:'Salsabila Zahra',relation:'Ibu',phone:'0812-3401-2704',profession:'Wiraswasta',status:'Aktif'}],
 teachers:[{nip:'198504122010011001',name:'Rina Wulandari, S.Pd.',subject:'Matematika',phone:'0813-4001-1102',status:'Aktif'},{nip:'198210202009011002',name:'Budi Santoso, S.Pd.',subject:'Bahasa Indonesia',phone:'0813-4001-1103',status:'Aktif'},{nip:'199002112017031006',name:'Dewi Anggraini, S.Pd.',subject:'IPA',phone:'0813-4001-1104',status:'Aktif'},{nip:'198707102014011009',name:'Arif Hidayat, S.Pd.',subject:'Bahasa Inggris',phone:'0813-4001-1105',status:'Cuti'}],
 classes:[{name:'VII A',grade:'VII',teacher:'Rina Wulandari',students:32,room:'R. 201',status:'Aktif'},{name:'VII B',grade:'VII',teacher:'Budi Santoso',students:31,room:'R. 202',status:'Aktif'},{name:'VIII A',grade:'VIII',teacher:'Dewi Anggraini',students:32,room:'R. 301',status:'Aktif'},{name:'VIII B',grade:'VIII',teacher:'Arif Hidayat',students:30,room:'R. 302',status:'Aktif'},{name:'IX A',grade:'IX',teacher:'Rina Wulandari',students:30,room:'R. 401',status:'Aktif'},{name:'IX B',grade:'IX',teacher:'Budi Santoso',students:29,room:'R. 402',status:'Aktif'}],
 subjects:[{code:'MAT-01',name:'Matematika',teacher:'Rina Wulandari',hours:5,group:'Wajib',status:'Aktif'},{code:'BIN-02',name:'Bahasa Indonesia',teacher:'Budi Santoso',hours:6,group:'Wajib',status:'Aktif'},{code:'IPA-03',name:'IPA Terpadu',teacher:'Dewi Anggraini',hours:5,group:'Wajib',status:'Aktif'},{code:'ING-04',name:'Bahasa Inggris',teacher:'Arif Hidayat',hours:4,group:'Wajib',status:'Aktif'},{code:'BJR-05',name:'Bahasa Jawa',teacher:'Lina Sari',hours:2,group:'Muatan Lokal',status:'Aktif'}],
 grades:[{student:'Aditya Pratama',class:'VII A',subject:'Matematika',assessment:'UTS',score:86,status:'Tuntas'},{student:'Nadia Putri',class:'VII A',subject:'IPA',assessment:'Tugas',score:94,status:'Tuntas'},{student:'Rizky Ramadhan',class:'VIII B',subject:'Bahasa Inggris',assessment:'UH',score:72,status:'Remedial'},{student:'Salsabila Zahra',class:'IX A',subject:'Bahasa Indonesia',assessment:'UTS',score:92,status:'Tuntas'},{student:'Bima Mahendra',class:'VIII A',subject:'Matematika',assessment:'UAS',score:80,status:'Tuntas'}],
 gradecomponents:[{name:'Tugas',subject:'Matematika',weight:30,min:75,semester:'Ganjil',status:'Aktif'},{name:'Ulangan Harian',subject:'Matematika',weight:25,min:75,semester:'Ganjil',status:'Aktif'},{name:'UTS',subject:'Matematika',weight:20,min:75,semester:'Ganjil',status:'Aktif'},{name:'UAS',subject:'Matematika',weight:25,min:75,semester:'Ganjil',status:'Aktif'}],
 finance:[{invoice:'INV-2026-101',student:'Aditya Pratama',type:'SPP',amount:350000,due:'2026-10-25',status:'Lunas'},{invoice:'INV-2026-102',student:'Nadia Putri',type:'SPP',amount:350000,due:'2026-10-25',status:'Belum Bayar'},{invoice:'INV-2026-103',student:'Rizky Ramadhan',type:'Kegiatan',amount:150000,due:'2026-10-30',status:'Sebagian'},{invoice:'INV-2026-104',student:'Salsabila Zahra',type:'SPP',amount:350000,due:'2026-10-25',status:'Lunas'},{invoice:'INV-2026-105',student:'Bima Mahendra',type:'Seragam',amount:250000,due:'2026-10-28',status:'Belum Bayar'}],
 facilities:[{code:'INV-1001',name:'Proyektor Epson',category:'Elektronik',location:'Kelas VII A',condition:'Baik',status:'Aktif'},{code:'INV-1002',name:'Laptop Administrasi',category:'Elektronik',location:'Tata Usaha',condition:'Baik',status:'Aktif'},{code:'INV-1003',name:'Meja Siswa',category:'Meubel',location:'Kelas VIII A',condition:'Perlu Perbaikan',status:'Aktif'},{code:'INV-1004',name:'Mikroskop',category:'Alat Lab',location:'Laboratorium',condition:'Baik',status:'Aktif'}]
};
SIMS.attendanceRows=SIMS.rows.students.slice(0,10).map((s,i)=>({...s,presence:i===2?'Izin':i===6?'Sakit':'Hadir'}));
SIMS.schedule=[{time:'07:00–07:40',subject:'Matematika',class:'VII A',teacher:'Rina Wulandari',room:'R. 201',color:'#4869ec'},{time:'07:40–08:20',subject:'Bahasa Indonesia',class:'VII B',teacher:'Budi Santoso',room:'R. 202',color:'#16ae7b'},{time:'08:40–09:20',subject:'Ilmu Pengetahuan Alam',class:'VIII A',teacher:'Dewi Anggraini',room:'Lab IPA',color:'#f2a63b'},{time:'09:20–10:00',subject:'Bahasa Inggris',class:'IX B',teacher:'Arif Hidayat',room:'R. 402',color:'#955fe9'},{time:'10:15–10:55',subject:'IPS',class:'VIII B',teacher:'Siti Rahmawati',room:'R. 302',color:'#e77191'}];
SIMS.icons={
 'layout-dashboard':'<rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>',
 'shield-check':'<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/>',
 'users':'<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
 'user-round-check':'<circle cx="9" cy="8" r="5"/><path d="M16 21v-2a7 7 0 0 0-14 0v2m15-10 2 2 4-4"/>',
 'contact-round':'<path d="M16 2v2M7 2v2M4 6h16v15H4z"/><circle cx="12" cy="12" r="3"/><path d="M8 19c0-2 8-2 8 0"/>',
 'school':'<path d="M4 10V21h16V10L12 3 4 10Z"/><path d="M9 21v-8h6v8M2 10l10-8 10 8"/>',
 'book-open':'<path d="M12 7v14M3 18V4c3-1 6-.5 9 2 3-2.5 6-3 9-2v14c-3-1-6-.5-9 2-3-2.5-6-3-9-2Z"/>',
 'calendar-days':'<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18M8 14h.01M12 14h.01M16 14h.01M8 18h.01"/>',
 'clipboard-check':'<rect x="5" y="4" width="14" height="18" rx="2"/><path d="M9 4V2h6v2m-7 9 2 2 5-5"/>',
 'award':'<circle cx="12" cy="8" r="6"/><path d="m8 14-1 8 5-3 5 3-1-8"/>',
 'chart-pie':'<path d="M21 12a9 9 0 1 1-9-9v9z"/><path d="M21 9h-6V3a9 9 0 0 1 6 6Z"/>',
 'heart-handshake':'<path d="M3 8c0-5 6-5 9-1 3-4 9-4 9 1 0 4-9 12-9 12S3 12 3 8Z"/><path d="M8 12h8"/>',
 'wallet':'<rect x="3" y="5" width="18" height="15" rx="2"/><path d="M16 13h5M3 9h18"/>',
 'building-2':'<rect x="4" y="3" width="16" height="18" rx="1"/><path d="M9 21v-5h6v5M8 7h1m6 0h1M8 11h1m6 0h1"/>',
 'calendar-range':'<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M8 2v4m8-4v4M3 10h18M8 15h8"/>',
 'megaphone':'<path d="M3 11v3h4l12 5V6L7 11H3Zm4 3 2 6h3l-2-5"/>',
 'file-bar-chart-2':'<path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Z"/><path d="M13 2v7h7M8 18v-3m4 3v-6m4 6v-4"/>',
 'component':'<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="m17.5 13 4 4-4 4-4-4z"/>',
 'book-text':'<path d="M4 5c4-2 6-1 8 0 2-1 4-2 8 0v15c-4-2-6-1-8 0-2-1-4-2-8 0zM12 5v15M7 9h2m-2 4h2m6-4h2"/>',
 'settings':'<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l-1.8 1.8a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6h-2.5a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-1.8-1.8a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.6-1H4v-2.5a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9l1.8-1.8a1.7 1.7 0 0 0 1.9.3 1.7 1.7 0 0 0 1-1.6h2.5a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l1.8 1.8a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.6 1V14a1.7 1.7 0 0 0-1.1 1Z"/>',
 'panel-left-open':'<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 4v16m6-12-4 4 4 4"/>',
 'panel-left-close':'<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 4v16m4-12 4 4-4 4"/>',
 'chevrons-up-down':'<path d="m7 10 5-5 5 5m-10 4 5 5 5-5"/>',
 'chevron-right':'<path d="m9 18 6-6-6-6"/>','chevron-left':'<path d="m15 18-6-6 6-6"/>','chevron-down':'<path d="m6 9 6 6 6-6"/>','chevron-up':'<path d="m6 15 6-6 6 6"/>',
 'search':'<circle cx="11" cy="11" r="7"/><path d="m16 16 5 5"/>',
 'moon':'<path d="M20.5 14A9 9 0 0 1 10 3.5 9 9 0 1 0 20.5 14Z"/>',
 'sun':'<circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32 1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>',
 'bell':'<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/>',
 'plus':'<path d="M12 5v14M5 12h14"/>','x':'<path d="M18 6 6 18M6 6l12 12"/>','check':'<path d="m4 12 5 5L20 6"/>',
 'check-circle':'<circle cx="12" cy="12" r="10"/><path d="m7 12 3 3 7-7"/>',
 'alert-triangle':'<path d="m12 2 10 18H2L12 2Z"/><path d="M12 9v4m0 4h.01"/>',
 'info':'<circle cx="12" cy="12" r="10"/><path d="M12 11v6m0-10h.01"/>',
 'user':'<circle cx="12" cy="8" r="4"/><path d="M5 21a7 7 0 0 1 14 0"/>',
 'user-plus':'<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6m-3-3h6"/>',
 'log-out':'<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4m7 14 5-5-5-5m5 5H9"/>',
 'arrow-right':'<path d="M5 12h14m-7-7 7 7-7 7"/>','arrow-up-right':'<path d="M7 17 17 7M7 7h10v10"/>',
 'trending-up':'<path d="m2 18 7-7 4 4 9-9m-7 0h7v7"/>','trending-down':'<path d="m2 6 7 7 4-4 9 9m-7 0h7v-7"/>',
 'bar-chart-3':'<path d="M3 3v18h18M7 16v-4m5 4V6m5 10V9"/>',
 'file-text':'<path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Z"/><path d="M13 2v7h7M8 13h8m-8 4h8"/>',
 'file-plus':'<path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Z"/><path d="M13 2v7h7M12 13v6m-3-3h6"/>',
 'download':'<path d="M12 3v12m-5-5 5 5 5-5M4 17v4h16v-4"/>','upload':'<path d="M12 21V9m-5 5 5-5 5 5M4 7V3h16v4"/>','filter':'<path d="M4 4h16l-6 7v8l-4 2V11Z"/>',
 'printer':'<path d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6z"/>',
 'edit-3':'<path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-5 1 1-5Z"/>','trash-2':'<path d="M3 6h18m-2 0-1 15H6L5 6m3 0V3h8v3M10 10v7m4-7v7"/>',
 'eye':'<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
 'more-horizontal':'<circle cx="5" cy="12" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/>',
 'clock':'<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>','map-pin':'<path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2"/>',
 'zap':'<path d="m13 2-9 11h7l-1 9 10-12h-7z"/>','sparkles':'<path d="m12 3 2 7 7 2-7 2-2 7-2-7-7-2 7-2z"/>',
 'menu':'<path d="M4 6h16M4 12h16M4 18h16"/>','corner-down-left':'<path d="M9 10 4 15l5 5M4 15h11a5 5 0 0 0 5-5V4"/>',
 'refresh-cw':'<path d="M20 7v5h-5M4 17v-5h5M5 9a8 8 0 0 1 14-2l1 5M4 12l1 5a8 8 0 0 0 14-2"/>',
 'check-square':'<rect x="3" y="3" width="18" height="18" rx="2"/><path d="m8 12 3 3 5-6"/>',
 'mail':'<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 7 10-7"/>',
 'database':'<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 4 18 4 18 0V5M3 12c0 4 18 4 18 0"/>',
 'lock':'<rect x="4" y="11" width="16" height="11" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
 'graduation-cap':'<path d="m2 9 10-5 10 5-10 5L2 9Z"/><path d="M6 11v5c4 3 8 3 12 0v-5M22 9v6"/>'
};
SIMS.ico=function(name,size=18){return `<svg aria-hidden="true" width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">${SIMS.icons[name]||SIMS.icons['info']}</svg>`;};
SIMS.escape=function(value){return String(value??'').replace(/[&<>"']/g,s=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));};
SIMS.money=n=>'Rp '+new Intl.NumberFormat('id-ID').format(Number(n)||0);
