<?php
// ================================================================
// NUMBER INFO PORTAL — Dashboard + API (Single File)
// ================================================================

require_once __DIR__ . '/../vendor/autoload.php';

use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberType;

// ---------- API MODE DETECT ----------
$uri   = $_SERVER['REQUEST_URI'] ?? '/';
$isApi = (strpos($uri, '/api') === 0) || isset($_GET['number']);

if ($isApi) {

    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
        http_response_code(200); exit;
    }

    $number = $_GET['number'] ?? '';

    if (empty($number)) {
        echo json_encode([
            "status"  => false,
            "message" => "Parameter 'number' required",
            "example" => "?number=+919876543210",
            "api_by"  => "https://t.me/username_506"
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    try {
        $phoneUtil   = PhoneNumberUtil::getInstance();
        $phoneNumber = $phoneUtil->parse($number, null);

        $region = $phoneUtil->getRegionCodeForNumber($phoneNumber);

        $countryNames = [
            "IN"=>"India","BD"=>"Bangladesh","PK"=>"Pakistan",
            "US"=>"United States","GB"=>"United Kingdom",
            "CA"=>"Canada","AU"=>"Australia",
            "AE"=>"United Arab Emirates","SA"=>"Saudi Arabia",
            "NP"=>"Nepal","LK"=>"Sri Lanka"
        ];
        $country = $countryNames[$region] ?? $region;

        $type  = $phoneUtil->getNumberType($phoneNumber);
        $types = [
            PhoneNumberType::FIXED_LINE           => "Fixed Line",
            PhoneNumberType::MOBILE               => "Mobile",
            PhoneNumberType::FIXED_LINE_OR_MOBILE => "Fixed Line Or Mobile",
            PhoneNumberType::TOLL_FREE            => "Toll Free",
            PhoneNumberType::PREMIUM_RATE         => "Premium Rate",
            PhoneNumberType::SHARED_COST          => "Shared Cost",
            PhoneNumberType::VOIP                 => "VOIP",
            PhoneNumberType::PERSONAL_NUMBER      => "Personal Number",
            PhoneNumberType::PAGER                => "Pager",
            PhoneNumberType::UAN                  => "UAN",
            PhoneNumberType::VOICEMAIL            => "Voicemail",
            PhoneNumberType::UNKNOWN              => "Unknown"
        ];

        echo json_encode([
            "status"               => true,
            "number"               => $number,
            "country"              => $country,
            "country_code"         => $phoneNumber->getCountryCode(),
            "region"               => $region,
            "national_number"      => (string)$phoneNumber->getNationalNumber(),
            "international_format" => $phoneUtil->format($phoneNumber, PhoneNumberFormat::INTERNATIONAL),
            "national_format"      => $phoneUtil->format($phoneNumber, PhoneNumberFormat::NATIONAL),
            "e164_format"          => $phoneUtil->format($phoneNumber, PhoneNumberFormat::E164),
            "valid"                => $phoneUtil->isValidNumber($phoneNumber),
            "possible"             => $phoneUtil->isPossibleNumber($phoneNumber),
            "type"                 => $types[$type] ?? "Unknown",
            "carrier"              => "Unknown",
            "timezone"             => "Asia/Kolkata",
            "timestamp"            => time(),
            "api_by"               => "https://t.me/username_506"
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    } catch (Exception $e) {
        echo json_encode([
            "status"  => false,
            "error"   => $e->getMessage(),
            "api_by"  => "https://t.me/username_506"
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
    exit;
}

// ================================================================
// HTML MODE — FULL DASHBOARD PORTAL
// ================================================================
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Number Info Portal — Dashboard</title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📱</text></svg>">
<style>
:root{
  --bg:#0a0e1a; --bg2:#111827; --card:#1a2236; --card2:#232d45;
  --border:#2a3654; --text:#e6ecf7; --muted:#8b98b8;
  --primary:#3b82f6; --primary2:#2563eb; --success:#22c55e;
  --danger:#ef4444; --warning:#f59e0b; --accent:#8b5cf6;
}
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',system-ui,-apple-system,sans-serif}
html,body{height:100%}
body{background:var(--bg);color:var(--text);overflow-x:hidden}

/* ---------- LAYOUT ---------- */
.app{display:flex;min-height:100vh}

/* ---------- SIDEBAR ---------- */
.sidebar{
  width:260px;background:var(--bg2);border-right:1px solid var(--border);
  padding:22px 16px;display:flex;flex-direction:column;gap:8px;
  position:sticky;top:0;height:100vh;transition:.3s;
}
.logo{
  display:flex;align-items:center;gap:10px;font-size:20px;font-weight:800;
  padding:6px 10px 22px;color:#fff;border-bottom:1px solid var(--border);margin-bottom:12px;
}
.logo span{font-size:26px}
.nav-item{
  display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:10px;
  color:var(--muted);cursor:pointer;font-size:15px;font-weight:500;transition:.2s;
  user-select:none;
}
.nav-item:hover{background:var(--card);color:var(--text)}
.nav-item.active{background:linear-gradient(135deg,var(--primary),var(--accent));color:#fff;box-shadow:0 4px 14px rgba(59,130,246,.35)}
.nav-item .ico{font-size:18px;width:22px;text-align:center}

.sidebar-footer{
  margin-top:auto;padding-top:14px;border-top:1px solid var(--border);
  font-size:12px;color:var(--muted);text-align:center;
}
.sidebar-footer a{color:var(--primary);text-decoration:none}

/* ---------- MAIN ---------- */
.main{flex:1;padding:26px 30px;max-width:1400px;margin:0 auto;width:100%}

.topbar{
  display:flex;justify-content:space-between;align-items:center;
  margin-bottom:26px;flex-wrap:wrap;gap:14px;
}
.page-title{font-size:26px;font-weight:800;color:#fff}
.page-sub{color:var(--muted);font-size:14px;margin-top:4px}
.status-pill{
  display:flex;align-items:center;gap:8px;background:var(--card);
  padding:8px 16px;border-radius:20px;font-size:13px;border:1px solid var(--border);
}
.dot{width:8px;height:8px;border-radius:50%;background:var(--success);box-shadow:0 0 10px var(--success);animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}

/* ---------- TABS ---------- */
.tab{display:none;animation:fade .3s}
.tab.active{display:block}
@keyframes fade{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}

/* ---------- CARDS ---------- */
.card{
  background:var(--card);border:1px solid var(--border);border-radius:16px;
  padding:22px;margin-bottom:20px;
}
.card-title{font-size:16px;font-weight:700;margin-bottom:14px;color:#fff;display:flex;align-items:center;gap:8px}

/* ---------- SEARCH ---------- */
.search-wrap{display:flex;gap:12px;flex-wrap:wrap}
.search-input{
  flex:1;min-width:240px;padding:15px 18px;border:1px solid var(--border);
  border-radius:12px;background:var(--bg2);color:#fff;font-size:16px;outline:none;
  transition:.2s;
}
.search-input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(59,130,246,.15)}
.btn{
  padding:15px 26px;border:none;border-radius:12px;font-size:15px;font-weight:700;
  cursor:pointer;transition:.2s;display:inline-flex;align-items:center;gap:8px;
}
.btn-primary{background:linear-gradient(135deg,var(--primary),var(--primary2));color:#fff;box-shadow:0 4px 14px rgba(59,130,246,.35)}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(59,130,246,.45)}
.btn-ghost{background:var(--card2);color:var(--text);border:1px solid var(--border)}
.btn-ghost:hover{background:var(--border)}
.btn-success{background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff}
.btn-success:hover{transform:translateY(-2px)}
.btn-danger{background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff}
.btn-sm{padding:9px 16px;font-size:13px;border-radius:9px}
.btn:disabled{opacity:.5;cursor:not-allowed;transform:none!important}

.quick-chips{display:flex;gap:8px;flex-wrap:wrap;margin-top:14px}
.chip{
  background:var(--card2);border:1px solid var(--border);padding:7px 14px;
  border-radius:20px;font-size:13px;cursor:pointer;color:var(--muted);transition:.2s;
}
.chip:hover{background:var(--primary);color:#fff;border-color:var(--primary)}

/* ---------- RESULT GRID ---------- */
.result-head{
  display:flex;align-items:center;gap:14px;padding:16px;border-radius:12px;
  background:linear-gradient(135deg,rgba(34,197,94,.15),rgba(59,130,246,.1));
  border:1px solid rgba(34,197,94,.3);margin-bottom:18px;
}
.result-head.invalid{background:linear-gradient(135deg,rgba(239,68,68,.15),rgba(245,158,11,.1));border-color:rgba(239,68,68,.3)}
.badge{
  width:52px;height:52px;border-radius:14px;display:flex;align-items:center;
  justify-content:center;font-size:26px;background:var(--success);
}
.result-head.invalid .badge{background:var(--danger)}
.result-head .info h3{font-size:17px;font-weight:700;color:#fff}
.result-head .info p{font-size:13px;color:var(--muted);margin-top:3px}

.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px}
.stat{
  background:var(--bg2);border:1px solid var(--border);border-radius:12px;
  padding:14px;transition:.2s;
}
.stat:hover{border-color:var(--primary);transform:translateY(-2px)}
.stat .k{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;font-weight:600}
.stat .v{font-size:15px;color:#fff;font-weight:700;word-break:break-word}
.stat .v.ok{color:var(--success)}
.stat .v.no{color:var(--danger)}

.code-box{
  background:#0a0e1a;border:1px solid var(--border);border-radius:12px;
  padding:16px;margin-top:18px;overflow-x:auto;font-family:'Consolas','Monaco',monospace;
  font-size:13px;color:#22d3ee;line-height:1.7;max-height:420px;
}
.code-box::-webkit-scrollbar{width:8px;height:8px}
.code-box::-webkit-scrollbar-thumb{background:var(--border);border-radius:4px}

/* ---------- HISTORY TABLE ---------- */
.table-wrap{overflow-x:auto;border-radius:12px;border:1px solid var(--border)}
table{width:100%;border-collapse:collapse;font-size:14px}
th{background:var(--bg2);color:var(--muted);text-align:left;padding:13px 16px;font-size:12px;text-transform:uppercase;letter-spacing:.5px;font-weight:700}
td{padding:13px 16px;border-top:1px solid var(--border);color:var(--text)}
tr:hover td{background:var(--card2)}
.tick{color:var(--success);font-weight:700}
.cross{color:var(--danger);font-weight:700}

.empty{padding:40px;text-align:center;color:var(--muted)}
.empty .ico{font-size:44px;margin-bottom:12px;opacity:.5}

/* ---------- LOADER ---------- */
.loader{display:none;text-align:center;padding:30px}
.spinner{
  width:44px;height:44px;border:4px solid var(--border);border-top-color:var(--primary);
  border-radius:50%;margin:0 auto 14px;animation:spin .8s linear infinite;
}
@keyframes spin{to{transform:rotate(360deg)}}

/* ---------- BULK ---------- */
textarea{
  width:100%;min-height:180px;padding:16px;border-radius:12px;
  background:var(--bg2);color:#fff;border:1px solid var(--border);font-size:14px;
  font-family:'Consolas',monospace;outline:none;resize:vertical;line-height:1.6;
}
textarea:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(59,130,246,.15)}

/* ---------- DOCS ---------- */
.endpoint{
  background:var(--bg2);border:1px solid var(--border);border-radius:12px;
  padding:16px;margin-bottom:14px;
}
.method{
  display:inline-block;background:var(--success);color:#fff;padding:3px 10px;
  border-radius:6px;font-size:11px;font-weight:800;margin-right:10px;
}
.endpoint code{color:#22d3ee;font-size:14px;font-family:'Consolas',monospace}
.endpoint p{color:var(--muted);font-size:13px;margin-top:10px}

/* ---------- TOAST ---------- */
.toast{
  position:fixed;bottom:26px;right:26px;background:var(--success);color:#fff;
  padding:14px 22px;border-radius:12px;font-weight:600;font-size:14px;
  box-shadow:0 10px 30px rgba(0,0,0,.4);transform:translateY(120px);transition:.3s;z-index:999;
}
.toast.show{transform:translateY(0)}
.toast.err{background:var(--danger)}

/* ---------- MOBILE ---------- */
.menu-btn{display:none;background:var(--card);border:1px solid var(--border);color:#fff;padding:10px 14px;border-radius:10px;font-size:18px;cursor:pointer}
@media(max-width:860px){
  .sidebar{position:fixed;left:-280px;z-index:100;box-shadow:0 0 40px rgba(0,0,0,.6)}
  .sidebar.open{left:0}
  .menu-btn{display:block}
  .main{padding:18px}
  .page-title{font-size:20px}
  .overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:99}
  .overlay.show{display:block}
}
</style>
</head>
<body>

<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<div class="app">

  <!-- ================= SIDEBAR ================= -->
  <aside class="sidebar" id="sidebar">
    <div class="logo"><span>📱</span> Number Info</div>

    <div class="nav-item active" data-tab="lookup" onclick="switchTab('lookup',this)">
      <span class="ico">🔍</span> Single Lookup
    </div>
    <div class="nav-item" data-tab="bulk" onclick="switchTab('bulk',this)">
      <span class="ico">📦</span> Bulk Lookup
    </div>
    <div class="nav-item" data-tab="history" onclick="switchTab('history',this)">
      <span class="ico">🕐</span> History
    </div>
    <div class="nav-item" data-tab="docs" onclick="switchTab('docs',this)">
      <span class="ico">📚</span> API Docs
    </div>

    <div class="sidebar-footer">
      Made with 💙<br>
      <a href="https://t.me/username_506" target="_blank">@username_506</a>
    </div>
  </aside>

  <!-- ================= MAIN ================= -->
  <main class="main">

    <div class="topbar">
      <div style="display:flex;align-items:center;gap:14px">
        <button class="menu-btn" onclick="toggleSidebar()">☰</button>
        <div>
          <div class="page-title" id="pageTitle">🔍 Single Lookup</div>
          <div class="page-sub" id="pageSub">Enter any phone number to get instant info</div>
        </div>
      </div>
      <div class="status-pill">
        <div class="dot"></div> API Online
      </div>
    </div>

    <!-- ========== TAB: LOOKUP ========== -->
    <section class="tab active" id="tab-lookup">
      <div class="card">
        <div class="card-title">🔎 Phone Number Lookup</div>
        <div class="search-wrap">
          <input type="tel" class="search-input" id="numberInput" placeholder="+91 98765 43210" autocomplete="off">
          <button class="btn btn-primary" onclick="lookup()">🔍 Search</button>
          <button class="btn btn-ghost" onclick="clearResult()">✕ Clear</button>
        </div>
        <div class="quick-chips">
          <div class="chip" onclick="quick('+919876543210')">🇮🇳 India</div>
          <div class="chip" onclick="quick('+8801712345678')">🇧🇩 Bangladesh</div>
          <div class="chip" onclick="quick('+14155552671')">🇺🇸 USA</div>
          <div class="chip" onclick="quick('+447911123456')">🇬🇧 UK</div>
          <div class="chip" onclick="quick('+971501234567')">🇦🇪 UAE</div>
          <div class="chip" onclick="quick('+923001234567')">🇵🇰 Pakistan</div>
        </div>
      </div>

      <div class="loader" id="loader">
        <div class="spinner"></div>
        <div style="color:var(--muted)">Fetching number info...</div>
      </div>

      <div id="resultBox"></div>
    </section>

    <!-- ========== TAB: BULK ========== -->
    <section class="tab" id="tab-bulk">
      <div class="card">
        <div class="card-title">📦 Bulk Lookup</div>
        <p style="color:var(--muted);font-size:13px;margin-bottom:14px">
          Ek line me ek number daalo (max 20 numbers).
        </p>
        <textarea id="bulkInput" placeholder="+919876543210
+14155552671
+447911123456
+8801712345678"></textarea>
        <div style="margin-top:16px;display:flex;gap:12px;flex-wrap:wrap">
          <button class="btn btn-primary" onclick="runBulk()">🚀 Run Bulk Lookup</button>
          <button class="btn btn-ghost" onclick="document.getElementById('bulkInput').value='';">✕ Clear</button>
        </div>
      </div>

      <div class="loader" id="bulkLoader">
        <div class="spinner"></div>
        <div style="color:var(--muted)">Processing batch...</div>
      </div>

      <div id="bulkResult"></div>
    </section>

    <!-- ========== TAB: HISTORY ========== -->
    <section class="tab" id="tab-history">
      <div class="card">
        <div class="card-title">🕐 Search History</div>
        <div id="historyBox"></div>
        <div style="margin-top:16px">
          <button class="btn btn-danger btn-sm" onclick="clearHistory()">🗑️ Clear All History</button>
        </div>
      </div>
    </section>

    <!-- ========== TAB: DOCS ========== -->
    <section class="tab" id="tab-docs">
      <div class="card">
        <div class="card-title">📚 API Documentation</div>

        <div class="endpoint">
          <span class="method">GET</span>
          <code>/api?number=+919876543210</code>
          <p>Returns JSON info about the phone number — country, region, formats, validity, type.</p>
        </div>

        <div class="endpoint">
          <span class="method">GET</span>
          <code>/?number=+919876543210</code>
          <p>Same as above — works from root path too.</p>
        </div>

        <div class="card-title" style="margin-top:22px">📄 Sample Response</div>
        <pre class="code-box">{
  "status": true,
  "number": "+919876543210",
  "country": "India",
  "country_code": 91,
  "region": "IN",
  "national_number": "9876543210",
  "international_format": "+91 98765 43210",
  "national_format": "098765 43210",
  "e164_format": "+919876543210",
  "valid": true,
  "possible": true,
  "type": "Mobile",
  "carrier": "Unknown",
  "timezone": "Asia/Kolkata",
  "timestamp": 1730000000,
  "api_by": "https://t.me/username_506"
}</pre>
      </div>
    </section>

  </main>
</div>

<div class="toast" id="toast"></div>

<script>
// ================== STATE ==================
let history = JSON.parse(localStorage.getItem('numHistory') || '[]');
let lastData = null;

// ================== SIDEBAR ==================
function toggleSidebar(){
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('overlay').classList.toggle('show');
}

// ================== TABS ==================
const tabMeta = {
  lookup:  {t:'🔍 Single Lookup', s:'Enter any phone number to get instant info'},
  bulk:    {t:'📦 Bulk Lookup',   s:'Check multiple numbers at once'},
  history: {t:'🕐 History',       s:'Your recent searches'},
  docs:    {t:'📚 API Docs',      s:'Integrate this API into your apps'}
};

function switchTab(tab, el){
  document.querySelectorAll('.nav-item').forEach(n=>n.classList.remove('active'));
  if(el) el.classList.add('active');
  document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
  document.getElementById('tab-'+tab).classList.add('active');
  document.getElementById('pageTitle').textContent = tabMeta[tab].t;
  document.getElementById('pageSub').textContent   = tabMeta[tab].s;
  if(tab === 'history') renderHistory();
  if(window.innerWidth <= 860) toggleSidebar();
}

// ================== TOAST ==================
function toast(msg, isErr=false){
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast show' + (isErr ? ' err' : '');
  setTimeout(()=>t.className = 'toast' + (isErr?' err':''), 2400);
}

// ================== LOOKUP ==================
function quick(n){
  document.getElementById('numberInput').value = n;
  lookup();
}

function clearResult(){
  document.getElementById('resultBox').innerHTML = '';
  document.getElementById('numberInput').value = '';
}

async function lookup(){
  const num = document.getElementById('numberInput').value.trim();
  if(!num){ toast('Enter a phone number', true); return; }

  const loader = document.getElementById('loader');
  const box    = document.getElementById('resultBox');
  loader.style.display = 'block';
  box.innerHTML = '';

  try{
    const res  = await fetch('/api?number=' + encodeURIComponent(num));
    const data = await res.json();
    loader.style.display = 'none';
    lastData = data;

    if(!data.status){
      box.innerHTML = `<div class="card" style="border-color:var(--danger)">
        <div class="result-head invalid">
          <div class="badge">❌</div>
          <div class="info"><h3>Invalid Number</h3><p>${data.error || data.message}</p></div>
        </div></div>`;
      toast('Invalid number', true);
      return;
    }

    renderResult(data);
    addHistory(data);

  }catch(e){
    loader.style.display = 'none';
    toast('API Error: ' + e.message, true);
  }
}

function renderResult(d){
  const box = document.getElementById('resultBox');

  const validBadge = d.valid ? '✅ Valid' : '⚠️ Invalid';
  const cls = d.valid ? '' : 'invalid';

  let stats = '';
  const order = [
    ['number','📞 Number', null],
    ['country','🌍 Country', null],
    ['country_code','🏳️ Country Code', null],
    ['region','📍 Region', null],
    ['type','📱 Type', null],
    ['carrier','📡 Carrier', null],
    ['international_format','🌐 International', null],
    ['national_format','🏠 National', null],
    ['e164_format','🔢 E.164', null],
    ['national_number','#️⃣ National No.', null],
    ['timezone','🕐 Timezone', null],
    ['valid','✔️ Valid', 'bool'],
    ['possible','🔎 Possible', 'bool'],
  ];

  order.forEach(([k,label,type])=>{
    if(!(k in d)) return;
    let v = d[k];
    let vCls = '';
    if(type === 'bool' || typeof v === 'boolean'){
      v = v ? '✅ Yes' : '❌ No';
      vCls = d[k] ? 'ok' : 'no';
    }
    stats += `<div class="stat">
      <div class="k">${label}</div>
      <div class="v ${vCls}">${v}</div>
    </div>`;
  });

  const json = JSON.stringify(d, null, 2).replace(/</g,'&lt;');

  box.innerHTML = `
    <div class="card">
      <div class="result-head ${cls}">
        <div class="badge">${d.valid ? '✅' : '❌'}</div>
        <div class="info">
          <h3>${d.international_format || d.number}</h3>
          <p>${validBadge} • ${d.country} • ${d.type}</p>
        </div>
      </div>

      <div class="grid">${stats}</div>

      <div style="margin-top:18px;display:flex;gap:10px;flex-wrap:wrap">
        <button class="btn btn-success btn-sm" onclick="copyJson()">📋 Copy JSON</button>
        <button class="btn btn-ghost btn-sm" onclick="copyText('${(d.number||'').replace(/'/g,'')}')">📞 Copy Number</button>
        <button class="btn btn-ghost btn-sm" onclick="downloadJson()">⬇️ Download</button>
      </div>

      <pre class="code-box">${json}</pre>
    </div>`;
}

function copyJson(){
  if(!lastData) return;
  navigator.clipboard.writeText(JSON.stringify(lastData, null, 2))
    .then(()=>toast('✅ JSON copied'))
    .catch(()=>toast('Copy failed', true));
}

function copyText(t){
  navigator.clipboard.writeText(t)
    .then(()=>toast('✅ Copied'))
    .catch(()=>toast('Copy failed', true));
}

function downloadJson(){
  if(!lastData) return;
  const blob = new Blob([JSON.stringify(lastData, null, 2)], {type:'application/json'});
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = 'number-' + Date.now() + '.json';
  a.click();
  toast('✅ Downloaded');
}

// ================== HISTORY ==================
function addHistory(d){
  history.unshift({
    number: d.number,
    country: d.country,
    region: d.region,
    type: d.type,
    valid: d.valid,
    time: Date.now()
  });
  history = history.slice(0, 50);
  localStorage.setItem('numHistory', JSON.stringify(history));
}

function renderHistory(){
  const box = document.getElementById('historyBox');

  if(!history.length){
    box.innerHTML = `<div class="empty"><div class="ico">🗂️</div>No history yet</div>`;
    return;
  }

  let rows = '';
  history.forEach(h=>{
    rows += `<tr>
      <td><strong>${h.number}</strong></td>
      <td>${h.country}</td>
      <td>${h.region}</td>
      <td>${h.type}</td>
      <td class="${h.valid?'tick':'cross'}">${h.valid?'✔️ Yes':'✖️ No'}</td>
      <td>${new Date(h.time).toLocaleString()}</td>
      <td><button class="btn btn-ghost btn-sm" onclick="reLookup('${h.number}')">🔍</button></td>
    </tr>`;
  });

  box.innerHTML = `<div class="table-wrap"><table>
    <thead><tr>
      <th>Number</th><th>Country</th><th>Region</th>
      <th>Type</th><th>Valid</th><th>Time</th><th>Action</th>
    </tr></thead>
    <tbody>${rows}</tbody>
  </table></div>`;
}

function reLookup(n){
  switchTab('lookup', document.querySelector('[data-tab="lookup"]'));
  document.getElementById('numberInput').value = n;
  lookup();
}

function clearHistory(){
  if(!confirm('Clear all history?')) return;
  history = [];
  localStorage.removeItem('numHistory');
  renderHistory();
  toast('🗑️ History cleared');
}

// ================== BULK ==================
async function runBulk(){
  const raw = document.getElementById('bulkInput').value.trim();
  if(!raw){ toast('Enter at least one number', true); return; }

  const nums = raw.split(/\n|,/)
    .map(s=>s.trim())
    .filter(Boolean)
    .slice(0, 20);

  if(!nums.length){ toast('No valid input', true); return; }

  const loader = document.getElementById('bulkLoader');
  const box    = document.getElementById('bulkResult');
  loader.style.display = 'block';
  box.innerHTML = '';

  const results = [];

  for(const n of nums){
    try{
      const res  = await fetch('/api?number=' + encodeURIComponent(n));
      const data = await res.json();
      results.push(data);
    }catch(e){
      results.push({status:false, number:n, error:e.message});
    }
  }

  loader.style.display = 'none';

  let rows = '';
  results.forEach(r=>{
    if(r.status){
      rows += `<tr>
        <td>${r.number}</td>
        <td>${r.country}</td>
        <td>${r.region}</td>
        <td>${r.type}</td>
        <td class="tick">✔️ Valid</td>
      </tr>`;
      addHistory(r);
    }else{
      rows += `<tr>
        <td>${r.number || '-'}</td>
        <td colspan="3" style="color:var(--danger)">${r.error || 'Invalid'}</td>
        <td class="cross">✖️ Invalid</td>
      </tr>`;
    }
  });

  box.innerHTML = `<div class="card">
    <div class="card-title">✅ Processed ${results.length} number(s)</div>
    <div class="table-wrap"><table>
      <thead><tr>
        <th>Number</th><th>Country</th><th>Region</th><th>Type</th><th>Status</th>
      </tr></thead>
      <tbody>${rows}</tbody>
    </table></div>
    <div style="margin-top:16px">
      <button class="btn btn-success btn-sm" onclick='downloadBulk(${JSON.stringify(JSON.stringify(results))})'>⬇️ Download JSON</button>
    </div>
  </div>`;

  toast(`✅ ${results.length} processed`);
}

function downloadBulk(str){
  const data = JSON.parse(str);
  const blob = new Blob([JSON.stringify(data, null, 2)], {type:'application/json'});
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = 'bulk-results.json';
  a.click();
}

// ================== ENTER KEY ==================
document.getElementById('numberInput').addEventListener('keypress', e=>{
  if(e.key === 'Enter') lookup();
});
</script>

</body>
</html>