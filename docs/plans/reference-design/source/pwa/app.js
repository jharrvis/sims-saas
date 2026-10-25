(() => {
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const wait = ms => new Promise(resolve => setTimeout(resolve, ms));
  const state = { method: 'otp', session: null, pendingRole: 'student', route: 'home', installPrompt: null, stream: null, scanTimer: null, otpTimer: null, otpSeconds: 120, navigationId: 0 };
  const icons = {
    calendar: '<svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4m8-4v4M3 10h18"/></svg>',
    check: '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>',
    book: '<svg viewBox="0 0 24 24"><path d="M12 7v14M3 18V4c3-1 6-.5 9 2 3-2.5 6-3 9-2v14c-3-1-6-.5-9 2-3-2.5-6-3-9-2Z"/></svg>',
    file: '<svg viewBox="0 0 24 24"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Z"/><path d="M13 2v7h7M8 14h8M8 18h6"/></svg>',
    wallet: '<svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="15" rx="2"/><path d="M16 13h5M3 9h18"/></svg>',
    trend: '<svg viewBox="0 0 24 24"><path d="m3 17 6-6 4 4 8-9"/><path d="M15 6h6v6"/></svg>',
    clock: '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
    receipt: '<svg viewBox="0 0 24 24"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2z"/><path d="M9 8h6M9 12h6"/></svg>',
    bell: '<svg viewBox="0 0 24 24"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>',
    award: '<svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="6"/><path d="m8 13-2 8 6-3 6 3-2-8"/></svg>',
    chevron: '<svg viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>',
    shield: '<svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>'
  };
  const profiles = {
    student: {
      role: 'student', name: 'Nadia Putri', title: 'Siswa · Kelas VII A', id: 'NIS 240017', initials: 'NP', attendance: 96, average: 87.6,
      grades: [['Matematika', '88', '+3', 'Sangat baik'], ['Bahasa Indonesia', '92', '+2', 'Sangat baik'], ['IPA Terpadu', '86', '+4', 'Baik'], ['Bahasa Inggris', '84', '+1', 'Baik'], ['IPS Terpadu', '89', '+2', 'Sangat baik'], ['Informatika', '91', '+5', 'Sangat baik']],
      assignments: [['Latihan Aljabar', 'Matematika · Besok, 20:00', 'Belum'], ['Esai Cerita Pendek', 'Bahasa Indonesia · 25 Okt', 'Dikerjakan'], ['Laporan Praktikum', 'IPA Terpadu · 28 Okt', 'Belum']],
      bills: [['SPP Oktober 2026', 'Jatuh tempo 28 Oktober', 450000], ['Kegiatan Semester', 'Jatuh tempo 10 November', 175000]],
      payments: [['SPP September 2026', '12 Sep 2026 · VA BCA', 450000, 'Lunas'], ['Buku Pendamping', '18 Agu 2026 · QRIS', 125000, 'Lunas'], ['SPP Agustus 2026', '10 Agu 2026 · VA BCA', 450000, 'Lunas']]
    },
    teacher: { role: 'teacher', name: 'Rina Wulandari', title: 'Guru · Matematika', id: 'NIP 19880412', initials: 'RW', attendance: 98 },
    staff: { role: 'staff', name: 'Arif Pratama', title: 'Pegawai · Administrasi', id: 'NIP 19910523', initials: 'AP', attendance: 97 }
  };
  const accounts = {
    nadia: ['siswa123', 'student'], '240017': ['siswa123', 'student'],
    rina: ['guru123', 'teacher'], '19880412': ['guru123', 'teacher'],
    operator: ['admin123', 'staff'], '19910523': ['admin123', 'staff']
  };
  const rupiah = value => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);

  function toast(message) {
    const el = $('#toast'); el.textContent = message; el.hidden = false;
    clearTimeout(toast.timer); toast.timer = setTimeout(() => { el.hidden = true; }, 3500);
  }
  async function databaseOperation(message, task, steps = ['Menghubungkan ke server sekolah', 'Mencocokkan data pengguna', 'Menyiapkan data terbaru']) {
    const overlay = $('#loading'); const progress = $('#loading-progress');
    $('#loading-text').textContent = message; overlay.hidden = false; progress.style.width = '12%';
    try {
      for (let i = 0; i < steps.length; i += 1) { $('#loading-detail').textContent = steps[i]; progress.style.width = `${24 + i * 32}%`; await wait(320); }
      const result = await task(); progress.style.width = '100%'; await wait(160); return result;
    } finally { overlay.hidden = true; progress.style.width = '0%'; }
  }
  const detectPhoneRole = phone => phone.endsWith('01') ? 'teacher' : phone.endsWith('02') ? 'staff' : 'student';
  function detectCodeRole(code) {
    const value = code.toUpperCase();
    return value.includes('GURU') || value.startsWith('T-') ? 'teacher' : value.includes('STAFF') || value.includes('PEGAWAI') || value.startsWith('P-') ? 'staff' : 'student';
  }
  function createSession(role, method) {
    const profile = profiles[role]; state.session = { role, method, name: profile.name, createdAt: new Date().toISOString() };
    localStorage.setItem('sims-pwa-session', JSON.stringify(state.session)); showApp();
    toast(`Selamat datang, ${profile.name}. Akses terdeteksi otomatis.`);
  }
  function setMethod(method) {
    state.method = method; stopScanner();
    $$('.method-tab').forEach(button => { const active = button.dataset.method === method; button.classList.toggle('active', active); button.setAttribute('aria-selected', String(active)); });
    $$('.auth-method').forEach(panel => { panel.hidden = panel.dataset.panel !== method; });
  }
  function configureNavigation(role) {
    const items = role === 'student' ? [['home', 'Beranda'], ['academics', 'Akademik'], ['finance', 'Keuangan'], ['profile', 'Profil']] : role === 'teacher' ? [['home', 'Beranda'], ['academics', 'Kelas'], ['finance', 'Penilaian'], ['profile', 'Profil']] : [['home', 'Beranda'], ['academics', 'Layanan'], ['finance', 'Keuangan'], ['profile', 'Profil']];
    $$('#bottom-nav a').forEach((link, index) => { link.href = `#${items[index][0]}`; link.dataset.route = items[index][0]; $('span', link).textContent = items[index][1]; });
  }
  function showApp() {
    $('#login-screen').hidden = true; $('#app-screen').hidden = false; $('#bottom-nav').hidden = false; configureNavigation(state.session.role);
    const requested = location.hash.slice(1); state.route = ['home', 'academics', 'finance', 'profile'].includes(requested) ? requested : 'home';
    if (location.hash !== `#${state.route}`) history.replaceState(null, '', `#${state.route}`); navigate(state.route, false);
  }
  function showLogin() {
    stopScanner(); $('#login-screen').hidden = false; $('#app-screen').hidden = true; $('#bottom-nav').hidden = true;
    history.replaceState(null, '', location.pathname + location.search);
  }
  function skeletonTemplate() {
    return `<div class="page-skeleton" aria-label="Memuat halaman" aria-busy="true"><div class="skeleton skeleton-title"></div><div class="skeleton skeleton-subtitle"></div><div class="skeleton skeleton-hero"></div><div class="skeleton-row"><div class="skeleton skeleton-box"></div><div class="skeleton skeleton-box"></div></div><div class="skeleton skeleton-line"></div><div class="skeleton skeleton-list"></div><div class="skeleton skeleton-list"></div></div>`;
  }
  async function navigate(route, simulate = true) {
    if (!state.session) return; const id = ++state.navigationId; state.route = route;
    $$('#bottom-nav a').forEach(link => link.classList.toggle('active', link.dataset.route === route));
    if (simulate) { $('#page-content').innerHTML = skeletonTemplate(); await wait(520 + Math.random() * 280); if (id !== state.navigationId) return; }
    renderRoute(); window.scrollTo({ top: 0, behavior: simulate ? 'smooth' : 'auto' });
  }
  const sectionHeading = (title, action = '') => `<div class="section-head"><h3>${title}</h3>${action ? `<button type="button">${action}</button>` : ''}</div>`;

  function studentHome(p) {
    return `<div class="screen-enter"><div class="mobile-heading"><div><span class="date-label">JUMAT, 23 OKTOBER</span><h1>Halo, Nadia</h1><p>Semangat belajar hari ini.</p></div><button class="notification-button" type="button" aria-label="Notifikasi">${icons.bell}<i></i></button></div>
      <section class="performance-hero"><div class="hero-orb one"></div><div class="hero-orb two"></div><div class="hero-top"><span>Performa semester</span><span class="semester-pill">Ganjil 2026</span></div><div class="score-layout"><div><strong>${p.average}</strong><small>Rata-rata nilai</small></div><div class="radial-progress" style="--value:${p.attendance}"><span>${p.attendance}%<small>Kehadiran</small></span></div></div><div class="hero-trend">${icons.trend}<span>Naik <strong>3.2 poin</strong> dari semester lalu</span></div></section>
      <div class="stat-grid"><button class="mini-stat" type="button" data-go="academics"><span class="mini-icon violet">${icons.award}</span><span><strong>6</strong><small>Nilai terbaru</small></span>${icons.chevron}</button><button class="mini-stat" type="button" data-go="finance"><span class="mini-icon amber">${icons.wallet}</span><span><strong>Rp625.000</strong><small>Tagihan aktif</small></span>${icons.chevron}</button></div>
      ${sectionHeading('Jadwal berikutnya', 'Lihat semua')}<section class="next-class-card"><div class="class-time"><strong>07:00</strong><small>40 menit</small></div><div class="class-accent"></div><div class="class-info"><span class="live-badge"><i></i> SEDANG BERLANGSUNG</span><h3>Matematika</h3><p>Ruang 201 · Ibu Rina Wulandari</p></div>${icons.chevron}</section>
      ${sectionHeading('Tugas prioritas')}<section class="surface-card compact-list">${p.assignments.slice(0, 2).map((item, i) => `<button class="data-row" type="button"><span class="row-icon ${i ? 'blue' : 'coral'}">${i ? icons.file : icons.book}</span><span><strong>${item[0]}</strong><small>${item[1]}</small></span><span class="status-chip ${i ? 'info' : 'warning'}">${item[2]}</span></button>`).join('')}</section></div>`;
  }
  function gradeChart() {
    return `<section class="surface-card chart-card"><div class="card-title-row"><div><span class="overline">PERKEMBANGAN</span><h3>Tren nilai akademik</h3></div><div class="trend-badge">${icons.trend} +3.2</div></div><div class="chart-tabs" role="group" aria-label="Pilih semester"><button class="active" data-term="ganjil">Ganjil</button><button data-term="genap">Genap</button></div><div class="chart-wrap"><svg id="grade-chart" viewBox="0 0 320 140" role="img" aria-describedby="chart-summary"><defs><linearGradient id="chartFill" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#6366f1" stop-opacity=".28"/><stop offset="1" stop-color="#6366f1" stop-opacity="0"/></linearGradient></defs><path class="chart-grid" d="M10 20H310M10 70H310M10 120H310"/><path id="chart-area" class="chart-area" d="M10 105 L70 91 L130 98 L190 69 L250 58 L310 37 L310 130 L10 130Z"/><path id="chart-line" class="chart-line" d="M10 105 L70 91 L130 98 L190 69 L250 58 L310 37"/><g id="chart-points" class="chart-points">${[105,91,98,69,58,37].map((y,i)=>`<circle cx="${10+i*60}" cy="${y}" r="4"/>`).join('')}</g></svg><div class="chart-labels">${['Jul','Agu','Sep','Okt','Nov','Des'].map(v=>`<span>${v}</span>`).join('')}</div></div><p id="chart-summary" class="sr-only">Nilai meningkat dari 82 menjadi 88.</p></section>`;
  }
  function studentAcademics(p) {
    return `<div class="screen-enter"><div class="page-title"><span class="overline">AKADEMIK</span><h1>Perkembangan belajar</h1><p>Pantau nilai, tugas, dan pencapaian dalam satu tempat.</p></div><div class="academic-summary"><div><span>Rata-rata</span><strong>${p.average}</strong><small>Peringkat 5 dari 32 siswa</small></div><div class="summary-divider"></div><div><span>Tugas selesai</span><strong>18/21</strong><small>3 tugas perlu diselesaikan</small></div></div>${gradeChart()}${sectionHeading('Nilai mata pelajaran', 'Semester Ganjil')}<section class="surface-card grade-list">${p.grades.map(([subject,score,change,label])=>`<button class="grade-row" type="button"><span class="subject-mark">${subject.slice(0,2).toUpperCase()}</span><span><strong>${subject}</strong><small>${label}</small></span><span class="grade-score"><strong>${score}</strong><small>↑ ${change}</small></span>${icons.chevron}</button>`).join('')}</section>${sectionHeading('Tugas aktif')}<section class="surface-card compact-list">${p.assignments.map(([title,note,status])=>`<button class="data-row" type="button"><span class="row-icon blue">${icons.file}</span><span><strong>${title}</strong><small>${note}</small></span><span class="status-chip ${status==='Belum'?'warning':'info'}">${status}</span></button>`).join('')}</section></div>`;
  }
  function studentFinance(p) {
    const total = p.bills.reduce((sum, bill) => sum + bill[2], 0);
    return `<div class="screen-enter"><div class="page-title"><span class="overline">KEUANGAN SISWA</span><h1>Tagihan & pembayaran</h1><p>Transaksi tercatat langsung di sistem sekolah.</p></div><section class="finance-hero"><div class="finance-head"><span>Total perlu dibayar</span><span class="secure-label">${icons.shield} Terverifikasi</span></div><strong>${rupiah(total)}</strong><p>${p.bills.length} tagihan aktif · Diperbarui hari ini, 09:42</p><button type="button" data-pay-all>Bayar semua tagihan</button></section><div class="finance-stats"><div><span class="mini-icon green">${icons.check}</span><span><strong>Rp1.025.000</strong><small>Dibayar semester ini</small></span></div><div><span class="mini-icon amber">${icons.clock}</span><span><strong>5 hari</strong><small>Jatuh tempo terdekat</small></span></div></div>${sectionHeading('Tagihan aktif')}<section class="bill-stack">${p.bills.map(([title,note,amount],i)=>`<article class="bill-card"><div class="bill-icon">${icons.receipt}</div><div class="bill-main"><span class="status-chip warning">Menunggu pembayaran</span><h3>${title}</h3><p>${note}</p><strong>${rupiah(amount)}</strong></div><button type="button" data-pay="${i}">Bayar</button></article>`).join('')}</section>${sectionHeading('Riwayat pembayaran', 'Lihat semua')}<section class="surface-card compact-list">${p.payments.map(([title,note,amount,status])=>`<div class="payment-row"><span class="row-icon green">${icons.check}</span><span><strong>${title}</strong><small>${note}</small></span><span><strong>${rupiah(amount)}</strong><small class="paid-label">${status}</small></span></div>`).join('')}</section></div>`;
  }
  function genericHome(p) {
    const teacher = p.role === 'teacher'; const items = teacher ? [['Kelas VII A','07:00 · Matematika','Berlangsung'],['Koreksi latihan aljabar','18 dari 32 siswa','56%'],['Rapat guru','13:00 · Ruang rapat','Hari ini']] : [['Verifikasi siswa baru','3 berkas perlu diperiksa','Prioritas'],['Pembayaran SPP','5 transaksi masuk','Baru'],['Surat keterangan','8 permintaan layanan','Diproses']];
    return `<div class="screen-enter"><div class="mobile-heading"><div><span class="date-label">JUMAT, 23 OKTOBER</span><h1>Halo, ${p.name.split(' ')[0]}</h1><p>${p.title}</p></div><div class="avatar">${p.initials}</div></div><section class="performance-hero generic"><div class="hero-top"><span>Ringkasan hari ini</span><span class="semester-pill">Aktif</span></div><div class="score-layout"><div><strong>${teacher?'4':'18'}</strong><small>${teacher?'Kelas terjadwal':'Layanan menunggu'}</small></div><div class="radial-progress" style="--value:${p.attendance}"><span>${p.attendance}%<small>Kehadiran</small></span></div></div></section>${sectionHeading(teacher?'Agenda mengajar':'Aktivitas layanan')}<section class="surface-card compact-list">${items.map(([title,note,status],i)=>`<button class="data-row" type="button"><span class="row-icon ${i?'blue':'violet'}">${icons[i?'file':'calendar']}</span><span><strong>${title}</strong><small>${note}</small></span><span class="status-chip info">${status}</span></button>`).join('')}</section></div>`;
  }
  function genericSection(p, route) {
    const teacher = p.role === 'teacher'; const title = route === 'academics' ? (teacher ? 'Kelas & jadwal' : 'Layanan administrasi') : (teacher ? 'Penilaian siswa' : 'Keuangan sekolah');
    const items = teacher ? [['VII A · Matematika','32 siswa · Jam pertama','Aktif'],['VII B · Matematika','30 siswa · Jam ketiga','Aktif'],['VIII A · Pengayaan','28 siswa · Kamis','Terjadwal']] : [['Permintaan surat','8 permintaan belum selesai','Diproses'],['Perubahan data','3 data siswa menunggu','Baru'],['Arsip digital','126 dokumen bulan ini','Tersimpan']];
    return `<div class="screen-enter"><div class="page-title"><span class="overline">SIMS MOBILE</span><h1>${title}</h1><p>Data simulasi siap dihubungkan dengan endpoint Laravel.</p></div><section class="surface-card compact-list">${items.map(([name,note,status])=>`<button class="data-row" type="button"><span class="row-icon violet">${icons.file}</span><span><strong>${name}</strong><small>${note}</small></span><span class="status-chip info">${status}</span></button>`).join('')}</section></div>`;
  }
  function profileTemplate(p) {
    return `<div class="screen-enter"><div class="page-title"><span class="overline">AKUN</span><h1>Profil pengguna</h1><p>Identitas dan keamanan akun sekolah.</p></div><section class="profile-hero"><div class="profile-avatar">${p.initials}</div><h2>${p.name}</h2><p>${p.title}</p><span>${p.id}</span></section><section class="surface-card settings-list">${[['Memuat data identitas...',icons.file,'Data pribadi','Identitas dan kontak terdaftar','violet'],['Memeriksa keamanan akun...',icons.shield,'Keamanan akun','Password dan perangkat aktif','green'],['Menyinkronkan preferensi...',icons.bell,'Notifikasi','WhatsApp dan pengingat aplikasi','blue']].map(x=>`<button type="button" data-db-action="${x[0]}"><span class="row-icon ${x[4]}">${x[1]}</span><span><strong>${x[2]}</strong><small>${x[3]}</small></span>${icons.chevron}</button>`).join('')}</section><button class="logout-button" type="button" id="logout-button">Keluar dari akun</button></div>`;
  }
  function renderRoute() {
    if (!state.session) return; const p = profiles[state.session.role];
    const markup = state.route === 'profile' ? profileTemplate(p) : p.role === 'student' && state.route === 'home' ? studentHome(p) : p.role === 'student' && state.route === 'academics' ? studentAcademics(p) : p.role === 'student' && state.route === 'finance' ? studentFinance(p) : state.route === 'home' ? genericHome(p) : genericSection(p, state.route);
    $('#page-content').innerHTML = markup; $('#page-content').focus({ preventScroll: true });
  }
  function startOtpCountdown() {
    clearInterval(state.otpTimer); state.otpSeconds = 120; $('#resend-otp').disabled = true;
    state.otpTimer = setInterval(() => { state.otpSeconds -= 1; const m=String(Math.floor(state.otpSeconds/60)).padStart(2,'0'); const s=String(state.otpSeconds%60).padStart(2,'0'); $('#otp-timer').textContent=`Kode berlaku ${m}:${s}`; if(state.otpSeconds<=0){clearInterval(state.otpTimer);$('#otp-timer').textContent='Kode telah kedaluwarsa';$('#resend-otp').disabled=false;} }, 1000);
  }
  async function sendOtp() {
    const phone = $('#phone').value.replace(/\D/g,''); if(phone.length<9){toast('Masukkan nomor WhatsApp yang valid.');$('#phone').focus();return;}
    await databaseOperation('Mencari akun sekolah...', async()=>{state.pendingRole=detectPhoneRole(phone);$('#otp-verification').hidden=false;startOtpCountdown();$('#otp-code').focus();toast('OTP simulasi telah dikirim. Gunakan kode 123456.');}, ['Mencari nomor pada data induk','Akun ditemukan','Mengirim OTP WhatsApp']);
  }
  async function verifyOtp() {
    if($('#otp-code').value!=='123456'){toast('Kode OTP tidak sesuai. Gunakan 123456.');return;}
    await databaseOperation('Memverifikasi identitas...',async()=>createSession(state.pendingRole,'whatsapp_otp'),['Memvalidasi OTP','Mendeteksi peran pengguna','Mengambil profil dan hak akses']);
  }
  async function passwordLogin() {
    const username=$('#username').value.trim().toLowerCase(); const account=accounts[username];
    await databaseOperation('Memeriksa kredensial...',async()=>{if(!account||account[0]!==$('#password').value){toast('Username atau kata sandi tidak sesuai.');$('#username').focus();return;}createSession(account[1],'username_password');},['Mencari akun di database','Memvalidasi kata sandi','Menentukan peran dan akses']);
  }
  async function startScanner() {
    if(!navigator.mediaDevices?.getUserMedia){toast('Kamera tidak tersedia. Gunakan kode manual.');return;}
    try{state.stream=await navigator.mediaDevices.getUserMedia({video:{facingMode:{ideal:'environment'}},audio:false});const video=$('#scanner-video');video.srcObject=state.stream;await video.play();$('#scanner-placeholder').hidden=true;$('#start-scan').textContent='Kamera aktif';if('BarcodeDetector'in window){const detector=new BarcodeDetector({formats:['qr_code','code_128','code_39']});state.scanTimer=setInterval(async()=>{try{const codes=await detector.detect(video);if(codes[0]?.rawValue){const role=detectCodeRole(codes[0].rawValue);stopScanner();await databaseOperation('Membaca kartu identitas...',async()=>createSession(role,'barcode'));}}catch(_){}},500);}else toast('Pemindaian otomatis tidak didukung. Gunakan kode manual.');}catch(_){toast('Izin kamera ditolak. Gunakan kode manual.');}
  }
  function stopScanner(){clearInterval(state.scanTimer);state.scanTimer=null;state.stream?.getTracks().forEach(track=>track.stop());state.stream=null;const video=$('#scanner-video');if(video)video.srcObject=null;const placeholder=$('#scanner-placeholder');if(placeholder)placeholder.hidden=false;const button=$('#start-scan');if(button)button.textContent='Aktifkan kamera';}
  function updateChart(term){const ys=term==='ganjil'?[105,91,98,69,58,37]:[92,85,76,82,54,46];const points=ys.map((y,i)=>`${10+i*60} ${y}`);const line=`M${points.join(' L')}`;$('#chart-line').setAttribute('d',line);$('#chart-area').setAttribute('d',`${line} L310 130 L10 130Z`);$$('#chart-points circle').forEach((p,i)=>p.setAttribute('cy',ys[i]));$$('.chart-tabs button').forEach(b=>b.classList.toggle('active',b.dataset.term===term));$('#chart-summary').textContent=term==='ganjil'?'Nilai meningkat dari 82 menjadi 88.':'Nilai semester genap meningkat secara stabil.';}
  function updateNetwork(){const online=navigator.onLine;const badge=$('#network-badge');badge.textContent=online?'Online':'Offline';badge.classList.toggle('offline',!online);if(!online)toast('Mode offline aktif. Data terakhir tetap dapat dilihat.');}
  function bindEvents(){
    $$('.method-tab').forEach(b=>b.addEventListener('click',()=>setMethod(b.dataset.method)));$('#otp-form').addEventListener('submit',e=>{e.preventDefault();sendOtp();});$('#verify-otp').addEventListener('click',verifyOtp);$('#resend-otp').addEventListener('click',sendOtp);$('#start-scan').addEventListener('click',startScanner);
    $('#manual-login').addEventListener('click',async()=>{const code=$('#manual-code').value.trim();if(code.length<6)return toast('Masukkan kode kartu yang valid.');await databaseOperation('Memeriksa kartu sekolah...',async()=>createSession(detectCodeRole(code),'barcode_manual'),['Membaca identitas kartu','Mencocokkan data induk','Menyiapkan hak akses']);});
    $('#toggle-password').addEventListener('click',()=>{const input=$('#password');const visible=input.type==='text';input.type=visible?'password':'text';$('#toggle-password').textContent=visible?'Lihat':'Sembunyi';});$('#password-form').addEventListener('submit',e=>{e.preventDefault();passwordLogin();});
    $('#bottom-nav').addEventListener('click',e=>{const link=e.target.closest('a[data-route]');if(!link)return;e.preventDefault();if(state.route!==link.dataset.route)location.hash=link.dataset.route;});
    document.addEventListener('click',async e=>{const go=e.target.closest('[data-go]');if(go)location.hash=go.dataset.go;const term=e.target.closest('[data-term]');if(term)updateChart(term.dataset.term);const action=e.target.closest('[data-db-action]');if(action)await databaseOperation(action.dataset.dbAction,async()=>toast('Data berhasil dimuat dari simulasi database.'));const payment=e.target.closest('[data-pay], [data-pay-all]');if(payment)await databaseOperation('Menyiapkan pembayaran...',async()=>toast('Kanal pembayaran simulasi siap digunakan.'),['Memeriksa status tagihan','Membuat kode pembayaran','Menyimpan transaksi sementara']);if(e.target.closest('[data-demo="reset"]'))toast('Tautan pemulihan akan dikirim ke WhatsApp.');if(e.target.closest('#logout-button'))await databaseOperation('Mengakhiri sesi...',async()=>{localStorage.removeItem('sims-pwa-session');state.session=null;showLogin();toast('Anda telah keluar dengan aman.');},['Menyimpan aktivitas terakhir','Menghapus token perangkat','Menutup sesi']);});
    window.addEventListener('hashchange',()=>{if(!state.session)return;const route=location.hash.slice(1)||'home';if(['home','academics','finance','profile'].includes(route))navigate(route);});window.addEventListener('online',updateNetwork);window.addEventListener('offline',updateNetwork);window.addEventListener('beforeinstallprompt',e=>{e.preventDefault();state.installPrompt=e;$('#install-button').hidden=false;});$('#install-button').addEventListener('click',async()=>{if(!state.installPrompt)return;state.installPrompt.prompt();await state.installPrompt.userChoice;state.installPrompt=null;$('#install-button').hidden=true;});
  }
  async function init(){bindEvents();updateNetwork();try{state.session=JSON.parse(localStorage.getItem('sims-pwa-session'));}catch(_){state.session=null;}if(state.session&&profiles[state.session.role])showApp();else showLogin();if('serviceWorker'in navigator)navigator.serviceWorker.register('./sw.js').catch(()=>toast('Mode offline belum dapat diaktifkan.'));}
  document.addEventListener('DOMContentLoaded',init);
})();
