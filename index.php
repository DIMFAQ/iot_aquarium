<?php
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Dashboard IoT Aquarium</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    body{background:#f6f8fb}
    .card{border-radius:10px; box-shadow:0 6px 16px rgba(20,30,50,0.06); border:none}
    .kpi{font-weight:700; font-size:1.5rem}
    .badge-state{padding:.4rem .7rem; border-radius:999px}
  </style>
</head>
<body>
<nav class="navbar navbar-light bg-white shadow-sm mb-4">
  <div class="container">
    <span class="navbar-brand mb-0 h1">IoT Aquarium — Dashboard</span>
    <div>
      <a class="btn btn-outline-secondary btn-sm" href="sandbox:/mnt/data/Laporan IOT - 5C.pdf" target="_blank">Laporan PDF</a>
    </div>
  </div>
</nav>

<div class="container">
  <div class="row g-3 mb-3">
    <div class="col-md-3">
      <div class="card p-3">
        <div class="small text-muted">Suhu (°C)</div>
        <div class="kpi" id="kpi-temp">-</div>
        <div class="small text-muted" id="kpi-temp-time">-</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3">
        <div class="small text-muted">Kekeruhan (raw)</div>
        <div class="kpi" id="kpi-turb">-</div>
        <div class="small text-muted" id="kpi-turb-time">-</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3">
        <div class="small text-muted">Tinggi Air (cm)</div>
        <div class="kpi" id="kpi-dist">-</div>
        <div class="small text-muted" id="kpi-dist-time">-</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3 text-center">
        <div class="small text-muted">Pompa</div>
        <div id="kpi-pump" class="badge-state bg-secondary text-white mt-2">-</div>
        <div class="mt-2">
          <button class="btn btn-primary btn-sm" id="btn-toggle-pump">Toggle Pompa</button>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3 text-center">
        <div class="small text-muted">Feed</div>
        <div id="kpi-feed" class="badge-state bg-secondary text-white mt-2">-</div>
        <div class="mt-2">
          <button class="btn btn-success btn-sm" id="btn-feed-start">Start Feed</button>
          <button class="btn btn-danger btn-sm" id="btn-feed-stop">Stop Feed</button>
        </div>
      </div>
    </div>
  </div>

  <div class="card p-3 mb-3">
    <div class="row">
      <div class="col-md-6" style="height:280px">
        <canvas id="chartTemp"></canvas>
      </div>
      <div class="col-md-6" style="height:280px">
        <canvas id="chartTurb"></canvas>
      </div>
    </div>
  </div>

  <div class="card p-3">
    <h6>History (50 terbaru)</h6>
    <div style="max-height:300px; overflow:auto;">
      <table class="table table-striped">
        <thead><tr><th>Waktu</th><th>Suhu</th><th>Kekeruhan</th><th>Tinggi</th><th>Pompa</th><th>Feed</th></tr></thead>
        <tbody id="history-body"></tbody>
      </table>
    </div>
  </div>
</div>

<script>
const POLL = 5000;
let labels = [], tempData = [], turbData = [];

const ctxT = document.getElementById('chartTemp');
const ctxU = document.getElementById('chartTurb');

const chartTemp = new Chart(ctxT, {
  type:'line',
  data:{ labels: labels, datasets:[{ label:'Suhu (°C)', data: tempData, tension:0.3, borderWidth:2 }]},
  options: { responsive:true }
});

const chartTurb = new Chart(ctxU, {
  type:'line',
  data:{ labels: labels, datasets:[{ label:'Kekeruhan', data: turbData, tension:0.3, borderWidth:2 }]},
  options: { responsive:true }
});

function loadLatest() {
  fetch('api_latest.php').then(r=>r.json()).then(d=>{
    if(!d) return;
    document.getElementById('kpi-temp').innerText = d.temp_c ?? '-';
    document.getElementById('kpi-temp-time').innerText = d.created_at ?? '-';
    document.getElementById('kpi-turb').innerText = d.turbidity_raw ?? '-';
    document.getElementById('kpi-turb-time').innerText = d.created_at ?? '-';
    document.getElementById('kpi-dist').innerText = d.distance_cm ?? '-';
    document.getElementById('kpi-dist-time').innerText = d.created_at ?? '-';

    // Feed status
    const feedEl = document.getElementById('kpi-feed');
    feedEl.innerText = d.feed_event==1 ? 'FEEDING':'IDLE';
    feedEl.className = d.feed_event==1 ? 'badge-state bg-success text-white' : 'badge-state bg-secondary text-white';

    // charts
    labels.push(new Date(d.created_at).toLocaleTimeString());
    tempData.push(Number(d.temp_c));
    turbData.push(Number(d.turbidity_raw));
    if(labels.length>24){ labels.shift(); tempData.shift(); turbData.shift(); }
    chartTemp.update(); chartTurb.update();

    // add to top of history table
    const tbody = document.getElementById('history-body');
    const tr = document.createElement('tr');
    tr.innerHTML = `<td>${d.created_at}</td><td>${d.temp_c ?? '-'}</td><td>${d.turbidity_raw ?? '-'}</td><td>${d.distance_cm ?? '-'}</td><td>${d.feed_event==1?'FEEDING':'IDLE'}</td>`;
    tbody.insertBefore(tr, tbody.firstChild);
    while(tbody.children.length>50) tbody.removeChild(tbody.lastChild);
  }).catch(e=>console.error(e));
}

function loadHistory() {
  fetch('api_history.php?limit=50').then(r=>r.json()).then(arr=>{
    const tbody = document.getElementById('history-body');
    tbody.innerHTML = '';
    arr.forEach(rw=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${rw.created_at}</td><td>${rw.temp_c ?? '-'}</td><td>${rw.turbidity_raw ?? '-'}</td><td>${rw.distance_cm ?? '-'}</td><td>${rw.feed_event==1?'FEEDING':'IDLE'}</td>`;
      tbody.appendChild(tr);
    });
  }).catch(e=>console.error(e));
}

// control
document.addEventListener('DOMContentLoaded', ()=>{
  loadHistory();
  loadLatest();
  setInterval(loadLatest, POLL);
  document.getElementById('btn-toggle-pump').addEventListener('click', ()=>{
    fetch('control.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'device=nodemcu&pump=1' })
      .then(()=>alert('Perintah toggle pompa dikirim (enqueue).')).catch(()=>alert('Gagal'));
  });
  document.getElementById('btn-feed-start').addEventListener('click', ()=>{
    fetch('control.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'device=nodemcu&feed=start' })
      .then(()=>alert('Perintah START FEED dikirim.')).catch(()=>alert('Gagal'));
  });
  document.getElementById('btn-feed-stop').addEventListener('click', ()=>{
    fetch('control.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'device=nodemcu&feed=stop' })
      .then(()=>alert('Perintah STOP FEED dikirim.')).catch(()=>alert('Gagal'));
  });
});
</script>
</body>
</html>
