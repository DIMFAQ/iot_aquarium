<?php
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Dashboard Akuarium IoT Premium</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    :root {
      --color-primary: #00bcd4;
      --color-primary-dark: #008c9f;
      --color-success: #28a745;
      --color-warning: #ffc107;
      --color-danger: #dc3545;
      --bg-color-fallback: #1e3758;
      --card-bg-alpha: rgba(255, 255, 255, 0.85);
      --shadow-soft: 0 8px 20px rgba(0,0,0,0.06);
    }
    body{
        font-family: 'Poppins', 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        background: var(--bg-color-fallback);
        background-image: url('https://source.unsplash.com/1920x1080/?aquarium,water,blue');
        background-size: cover;
        background-attachment: fixed;
        background-position: center;
        color: #343a40;
    }
    .container {
        padding-top: 30px;
        padding-bottom: 30px;
    }
    .navbar{
        background-color: var(--card-bg-alpha) !important;
        border-bottom: 5px solid var(--color-primary);
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .card-kpi{
      border-radius:15px;
      box-shadow:var(--shadow-soft);
      border:none;
      transition: all 0.3s ease;
      background: var(--card-bg-alpha);
      backdrop-filter: blur(2px);
      min-height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      overflow: hidden;
    }
    .card-kpi:hover{
      box-shadow: 0 15px 30px rgba(0,0,0,0.15);
      transform: translateY(-3px);
    }
    .kpi-title{
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--color-primary-dark);
        text-transform: uppercase;
        margin-bottom: 10px;
        letter-spacing: 0.5px;
    }
    .kpi-value{
        font-weight: 800;
        font-size: 2.2rem;
        line-height: 1.1;
        color: #1a1a1a;
    }
    .kpi-icon-container{
        font-size: 3.5rem;
        color: var(--color-primary);
        opacity: 0.8;
    }
    .kpi-detail{
        font-size: 0.75rem;
        color: #555;
        margin-top: 15px;
        line-height: 1.4;
    }
    .text-optimal { color: var(--color-success) !important; }
    .text-dingin { color: var(--color-primary) !important; }
    .text-panas { color: var(--color-danger) !important; }
    .text-jernih { color: var(--color-success) !important; }
    .text-sedang { color: var(--color-warning) !important; }
    .text-keruh { color: var(--color-danger) !important; }
    .card-control .badge-state{
        font-size: 1rem;
        padding: 0.7rem 1.5rem;
        border-radius: 50px;
        font-weight: 700;
        background-color: #5a6268 !important;
    }
    .card-control .btn{
        font-weight: 600;
    }
    .chart-container {
        padding: 20px;
        background: var(--card-bg-alpha);
        border-radius: 15px;
        box-shadow: var(--shadow-soft);
    }
    .table-striped > tbody > tr:nth-of-type(odd) > * {
      --bs-table-accent-bg: rgba(0, 188, 212, 0.08);
    }
    .table thead th {
        border-bottom: 2px solid var(--color-primary);
        color: #343a40;
        font-weight: 600;
    }
    h4.text-muted {
        color: #fff !important;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.7);
        font-weight: 700;
    }
    .btn-refresh {
      display:inline-flex; align-items:center; gap:.6rem;
      border-radius:10px; padding: .45rem .85rem; font-weight:700;
      background: linear-gradient(90deg,var(--color-primary), #06a6f7);
      color:white; border:none; box-shadow: 0 8px 26px rgba(6,166,204,0.12);
    }
    .btn-refresh:disabled{ opacity:0.8; cursor:default; }
  </style>
</head>
<body>
<nav class="navbar navbar-light shadow-sm mb-5">
  <div class="container d-flex justify-content-between align-items-center">
    <span class="navbar-brand mb-0 h1">
        <i class="fa-solid fa-water" style="color:var(--color-primary);"></i> Dasbor Kontrol Akuarium
    </span>
    <div>
      <button id="btn-refresh" class="btn-refresh" title="Refresh Data Sekarang">
        <span id="refresh-icon"><i class="fa-solid fa-arrows-rotate"></i></span>
        <span id="refresh-label">Refresh Data</span>
      </button>
    </div>
  </div>
</nav>

<div class="container">
  <h4 class="mb-4 text-muted">Kondisi Akuarium Saat Ini</h4>
  <div class="row g-4 mb-5">
    <div class="col-md-3">
      <div class="card card-kpi p-4">
        <div>
            <div class="kpi-title">KONDISI SUHU</div>
            <div class="d-flex justify-content-between align-items-center">
                <div class="kpi-value" id="kpi-temp-condition">-</div>
                <div class="kpi-icon-container text-danger"><i class="fa-solid fa-temperature-three-quarters"></i></div>
            </div>
        </div>
        <div class="kpi-detail" id="kpi-temp-raw">Angka Mentah: - °C</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card card-kpi p-4">
        <div>
            <div class="kpi-title">KONDISI KEKERUHAN</div>
            <div class="d-flex justify-content-between align-items-center">
                <div class="kpi-value" id="kpi-turb-condition">-</div>
                <div class="kpi-icon-container text-info"><i class="fa-solid fa-droplet"></i></div>
            </div>
        </div>
        <div class="kpi-detail" id="kpi-turb-raw">Angka Mentah: - (Raw)</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card card-kpi p-4">
        <div>
            <div class="kpi-title">KETINGGIAN AIR</div>
            <div class="d-flex justify-content-between align-items-center">
                <div class="kpi-value" id="kpi-dist">-</div>
                <div class="kpi-icon-container text-primary"><i class="fa-solid fa-ruler-vertical"></i></div>
            </div>
        </div>
        <div class="kpi-detail" id="kpi-dist-time">Diukur: -</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card card-kpi card-control p-4 text-center">
        <div class="kpi-title">PENGONTROL PAKAN</div>
        <div id="kpi-feed" class="badge-state bg-secondary text-white mx-auto mt-3">-</div>
        <div class="mt-3 d-flex justify-content-center gap-2">
          <button class="btn btn-success" id="btn-feed-start"><i class="fa-solid fa-bowl-food"></i> Beri Pakan</button>
          <button class="btn btn-danger" id="btn-feed-stop"><i class="fa-solid fa-stop"></i> Hentikan</button>
        </div>
      </div>
    </div>
  </div>
  <h4 class="mb-3 text-muted">Tren Data Sensor</h4>
  <div class="row g-4 mb-5">
      <div class="col-md-6">
        <div class="chart-container" style="height:350px">
          <canvas id="chartTemp"></canvas>
        </div>
      </div>
      <div class="col-md-6">
        <div class="chart-container" style="height:350px">
          <canvas id="chartTurb"></canvas>
        </div>
      </div>
  </div>
  <h4 class="mb-3 text-muted">Riwayat Detail Sensor (50 Terbaru)</h4>
  <div class="card p-4">
    <div style="max-height:450px; overflow:auto;">
      <table class="table table-striped table-hover">
        <thead><tr><th>Waktu (UTC)</th><th>Suhu (°C)</th><th>Kekeruhan (Raw)</th><th>Ketinggian (cm)</th><th>Pakan</th></tr></thead>
        <tbody id="history-body"></tbody>
      </table>
    </div>
  </div>
</div>

<script>
const POLL = 5000;
let labels = [], tempData = [], turbData = [];
const TEMP_OPT_MIN = 24.0;
const TEMP_OPT_MAX = 28.0;

function getTempCondition(temp) {
    if (temp === null || isNaN(temp)) return { text: 'TIDAK TERDETEKSI', class: 'text-secondary' };
    if (temp < TEMP_OPT_MIN) return { text: 'DINGIN', class: 'text-dingin' };
    if (temp > TEMP_OPT_MAX) return { text: 'PANAS', class: 'text-panas' };
    return { text: 'OPTIMAL', class: 'text-optimal' };
}

function getTurbCondition(turb) {
    if (turb === null || isNaN(turb)) return { text: 'TIDAK TERDETEKSI', class: 'text-secondary' };
    if (turb >= 700) return { text: 'SANGAT JERNIH', class: 'text-success' };
    if (turb >= 600) return { text: 'JERNIH', class: 'text-success' };
    if (turb >= 400) return { text: 'AGAK KERUH', class: 'text-warning' };
    if (turb >= 200) return { text: 'KERUH', class: 'text-danger' };
    return { text: 'SANGAT KERUH', class: 'text-danger' };
}

const ctxT = document.getElementById('chartTemp');
const ctxU = document.getElementById('chartTurb');

const chartTemp = new Chart(ctxT, {
  type:'line',
  data:{ labels: labels, datasets:[{ label:'Suhu (°C)', data: tempData, tension:0.3, borderWidth:3, borderColor: '#dc3545', backgroundColor: 'rgba(220, 53, 69, 0.1)', fill: true, pointRadius: 4 }]},
  options: {
    responsive:true,
    maintainAspectRatio: false,
    plugins: { title: { display: true, text: 'Grafik Suhu Air', font: { size: 16 } }, legend: { display: false } },
    scales: { y: { beginAtZero: false, title: { display: true, text: 'Suhu (°C)' } } }
  }
});

const chartTurb = new Chart(ctxU, {
  type:'line',
  data:{ labels: labels, datasets:[{ label:'Kekeruhan (Raw)', data: turbData, tension:0.3, borderWidth:3, borderColor: '#00bcd4', backgroundColor: 'rgba(0, 188, 212, 0.2)', fill: true, pointRadius: 4 }]},
  options: {
    responsive:true,
    maintainAspectRatio: false,
    plugins: { title: { display: true, text: 'Grafik Kekeruhan Air', font: { size: 16 } }, legend: { display: false } },
    scales: { y: { beginAtZero: true, title: { display: true, text: 'Kekeruhan (Raw)' } } }
  }
});

function setRefreshLoading(isLoading){
  const btn = document.getElementById('btn-refresh');
  const icon = document.getElementById('refresh-icon');
  const label = document.getElementById('refresh-label');
  btn.disabled = isLoading;
  if(isLoading){
    icon.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
    label.innerText = 'Memuat...';
  } else {
    icon.innerHTML = '<i class="fa-solid fa-arrows-rotate"></i>';
    label.innerText = 'Refresh Data';
  }
}

async function refreshData(){
  try{
    setRefreshLoading(true);
    await Promise.all([ loadLatest(), loadHistory() ]);
  }catch(err){
    console.error('Gagal refresh:', err);
    alert('Gagal memuat data. Cek koneksi atau server API.');
  }finally{
    setRefreshLoading(false);
  }
}

async function loadLatest() {
  try {
    const r = await fetch('api_latest.php', { cache: 'no-store' });
    if(!r.ok) throw new Error('HTTP ' + r.status);
    const d = await r.json();
    if(!d) return;

    const tempCond = getTempCondition(Number(d.temp_c));
    const turbCond = getTurbCondition(Number(d.turbidity_raw));

    const elTemp = document.getElementById('kpi-temp-condition');
    elTemp.innerText = tempCond.text;
    elTemp.className = `kpi-value ${tempCond.class}`;
    document.getElementById('kpi-temp-raw').innerHTML = `Angka Mentah: ${d.temp_c ?? '-'} °C<br>Diukur: ${d.created_at ?? '-'}`;

    const elTurb = document.getElementById('kpi-turb-condition');
    elTurb.innerText = turbCond.text;
    elTurb.className = `kpi-value ${turbCond.class}`;
    document.getElementById('kpi-turb-raw').innerHTML = `Angka Mentah: ${d.turbidity_raw ?? '-'} (Raw)<br>Diukur: ${d.created_at ?? '-'}`;

    document.getElementById('kpi-dist').innerText = (d.distance_cm ?? '-') + ' cm';
    document.getElementById('kpi-dist-time').innerHTML = `Jarak: ${d.distance_cm ?? '-'} cm<br>Diukur: ${d.created_at ?? '-'}`;

    const feedEl = document.getElementById('kpi-feed');
    feedEl.innerText = d.feed_event==1 ? 'SEDANG MEMBERI PAKAN':'SIAP PAKAN';
    feedEl.className = d.feed_event==1 ? 'badge-state bg-success text-white' : 'badge-state bg-secondary text-white';

    labels.push(new Date(d.created_at).toLocaleTimeString('id-ID'));
    tempData.push(Number(d.temp_c));
    turbData.push(Number(d.turbidity_raw));
    if(labels.length>24){ labels.shift(); tempData.shift(); turbData.shift(); }
    chartTemp.update();
    chartTurb.update();

    const tbody = document.getElementById('history-body');
    const tr = document.createElement('tr');
    const feedStatus = d.feed_event==1 ? 'SEDANG MEMBERI PAKAN' : 'IDLE';
    tr.innerHTML = `<td>${d.created_at}</td><td>${d.temp_c ?? '-'}</td><td>${d.turbidity_raw ?? '-'}</td><td>${d.distance_cm ?? '-'}</td><td>${feedStatus}</td>`;
    tbody.insertBefore(tr, tbody.firstChild);
    while(tbody.children.length>50) tbody.removeChild(tbody.lastChild);

  } catch (e) {
    console.error('Gagal memuat data terbaru:', e);
    throw e;
  }
}

async function loadHistory() {
  try {
    const r = await fetch('api_history.php?limit=50', { cache: 'no-store' });
    if(!r.ok) throw new Error('HTTP ' + r.status);
    const arr = await r.json();
    const tbody = document.getElementById('history-body');
    tbody.innerHTML = '';
    arr.forEach(rw=>{
      const feedStatus = rw.feed_event==1 ? 'SEDANG MEMBERI PAKAN' : 'IDLE';
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${rw.created_at}</td><td>${rw.temp_c ?? '-'}</td><td>${rw.turbidity_raw ?? '-'}</td><td>${rw.distance_cm ?? '-'}</td><td>${feedStatus}</td>`;
      tbody.appendChild(tr);
    });

    if(labels.length === 0 && Array.isArray(arr) && arr.length){
      labels = arr.slice(-24).map(a => new Date(a.created_at).toLocaleTimeString('id-ID'));
      tempData = arr.slice(-24).map(a => Number(a.temp_c));
      turbData = arr.slice(-24).map(a => Number(a.turbidity_raw));
      chartTemp.data.labels = labels; chartTemp.data.datasets[0].data = tempData;
      chartTurb.data.labels = labels; chartTurb.data.datasets[0].data = turbData;
      chartTemp.update();
      chartTurb.update();
    }
  } catch (e) {
    console.error('Gagal memuat riwayat:', e);
    throw e;
  }
}

document.getElementById('btn-feed-start').addEventListener('click', async ()=>{
  const btn = document.getElementById('btn-feed-start');
  btn.disabled = true;
  const old = btn.innerHTML;
  btn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengirim`;
  try {
    await fetch('control.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'device=nodemcu&feed=start' });
    alert('Perintah START PAKAN telah dikirim (enqueue).');
    setTimeout(()=>loadLatest(), 800);
  } catch (e) {
    alert('Gagal mengirim perintah.');
  } finally {
    btn.disabled = false;
    btn.innerHTML = old;
  }
});
document.getElementById('btn-feed-stop').addEventListener('click', async ()=>{
  const btn = document.getElementById('btn-feed-stop');
  btn.disabled = true;
  const old = btn.innerHTML;
  btn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengirim`;
  try {
    await fetch('control.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'device=nodemcu&feed=stop' });
    alert('Perintah STOP PAKAN telah dikirim (enqueue).');
    setTimeout(()=>loadLatest(), 800);
  } catch (e) {
    alert('Gagal mengirim perintah.');
  } finally {
    btn.disabled = false;
    btn.innerHTML = old;
  }
});

document.addEventListener('DOMContentLoaded', async ()=>{
  setRefreshLoading(true);
  try {
    await Promise.all([ loadHistory(), loadLatest() ]);
  } catch(e){
    console.warn('Initial load error', e);
  } finally {
    setRefreshLoading(false);
  }
  document.getElementById('btn-refresh').addEventListener('click', refreshData);
  setInterval(()=>{ loadLatest(); }, POLL);
});
</script>
</body>
</html>
