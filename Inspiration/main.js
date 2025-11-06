/* script.js - eVote frontend demo
   - persistent state in localStorage:
     evote_elections, evote_votes, evote_voters, evote_current
   - i18n strings for en/hi/gu
   - simulate OTP (admin log shows OTP)
*/

const STR = {
  en: {
    appTitle: "eVote — Online Voting Prototype",
    appSub: "voter + admin demo — frontend only",
    voterHeading: "Voter Verification",
    voterHint: "Enter Aadhaar, DOB and receive OTP to verify. (Demo OTP appears in admin log)",
    sendOtp: "Send OTP",
    verify: "Verify & Continue",
    ballotHeading: "Ballot — Choose Your Candidate",
    castVote: "Cast Vote",
    thanks: "Thanks — Vote Recorded",
    adminHeading: "Admin Control Panel",
    adminHint: "Create elections, add candidates, import voters, start/stop, monitor turnout. (Demo only)",
    modeVoter: "Voter",
    modeAdmin: "Admin"
  },
  hi: {
    appTitle: "ई-वोट — ऑनलाइन वोटिंग प्रोटोटाइप",
    appSub: "मतदाता + प्रशासक डेमो — केवल फ्रंटएण्ड",
    voterHeading: "मतदाता सत्यापन",
    voterHint: "Aadhaar, जन्मतिथि दर्ज करें और OTP प्राप्त करें। (डेमो OTP एडमिन लॉग में दिखेगा)",
    sendOtp: "OTP भेजें",
    verify: "सत्यापित करें और आगे जाएँ",
    ballotHeading: "मतपत्र — अपना उम्मीदवार चुनें",
    castVote: "वोट डालें",
    thanks: "धन्यवाद — आपका वोट दर्ज हुआ",
    adminHeading: "प्रशासक नियंत्रण पैनल",
    adminHint: "चुनाव बनायें, उम्मीदवार जोड़ें, मतदाता इम्पोर्ट करें, शुरू/रोकें और टर्नआउट देखें। (डेमो)",
    modeVoter: "मतदाता",
    modeAdmin: "प्रशासक"
  },
  gu: {
    appTitle: "ઈ-વોટ — ઑનલાઇન મતદાન પ્રોટોટાઇપ",
    appSub: "મતદાર + એડમિન ડેમો — ફ્રન્ટએન્ડ માત્ર",
    voterHeading: "મતદાર તપાસ",
    voterHint: "Aadhaar, જન્મ તારીખ દાખલ કરો અને OTP મેળવો. (ડેમો OTP એડમિન લૉગમાં બતાશે)",
    sendOtp: "OTP મોકલો",
    verify: "ચકાસો અને આગળ વધો",
    ballotHeading: "બોલેટ — તમારો ઉમેદવાર પસંદ કરો",
    castVote: "વોટ મૂકો",
    thanks: "આભાર — મત રેકોર્ડ થયો",
    adminHeading: "એડમિન કંટ્રોલ પેનલ",
    adminHint: "ચૂંટણી બનાવો, ઉમેદવાર ઉમેરો, મતદાર ઇમ્પોર્ટ કરો, શરૂ/બંધ કરો અને ટર્નઆઉટ જુઓ. (ડેમો)",
    modeVoter: "મતદાર",
    modeAdmin: "એડમિન"
  }
};

const $ = id => document.getElementById(id);
let LANG = 'en';
let MODE = 'voter'; // 'voter' | 'admin'
let STATE = { otpMap: {} }; // in-memory OTPs for demo

/* localStorage helpers */
const storage = {
  get(k, fallback = null) { try { const v = localStorage.getItem(k); return v ? JSON.parse(v) : fallback } catch(e){ return fallback } },
  set(k, v) { localStorage.setItem(k, JSON.stringify(v)) },
  rm(k) { localStorage.removeItem(k) }
};

if (!storage.get('evote_elections')) storage.set('evote_elections', []);
if (!storage.get('evote_votes')) storage.set('evote_votes', {});
if (!storage.get('evote_voters')) storage.set('evote_voters', {});

/* DOM refs */
const refs = {
  // language & mode
  lang: $('lang'), modeToggle: $('mode-toggle'), modeLabel: $('mode-label'),
  // voter
  loginView: $('voter-login'), loginForm: $('login-form'), aadhaar: $('v-aadhaar'), dob: $('v-dob'), contact: $('v-contact'), contactType: $('v-contact-type'),
  sendOtp: $('send-otp'), otpArea: $('otp-area'), otpInput: $('v-otp'), verifyOtp: $('verify-otp'), resendOtp: $('resend-otp'), clearLogin: $('clear-login'),
  // ballot
  ballotView: $('ballot'), candidatesDiv: $('candidates'), electionTitle: $('election-title'), voterName: $('voter-name'), voterAad: $('voter-aad'),
  castVote: $('cast-vote'), backToLogin: $('back-to-login'),
  // confirmation
  confirmView: $('confirmation'), finish: $('finish'), viewReceipt: $('view-receipt'),
  // admin
  adminView: $('admin-panel'), eName: $('e-name'), eConst: $('e-const'), eStart: $('e-start'), eEnd: $('e-end'), createElection: $('create-election'), loadSample: $('load-sample'),
  cName: $('c-name'), cParty: $('c-party'), cSymbol: $('c-symbol'), addCandidate: $('add-candidate'), clearCandidate: $('clear-candidate'), cTable: $('c-table'),
  vRollFile: $('v-roll-file'), importRoll: $('import-roll'), vTable: $('v-table'), clearRoll: $('clear-roll'),
  selectElection: $('select-election'), startElection: $('start-election'), stopElection: $('stop-election'),
  statVoted: $('stat-voted'), statEligible: $('stat-eligible'), statTurnout: $('stat-turnout'),
  resultsChartEl: $('results-chart'), exportData: $('export-data'), resetAll: $('reset-all'), adminLog: $('admin-log'),
  // widgets
  widgetTitle: $('widget-election'), widgetBody: $('widget-body'), openVoter: $('open-voter'), openAdmin: $('open-admin'),
  modal: $('modal'), receiptBody: $('receipt-body'), closeReceipt: $('close-receipt'),
  langSelect: $('lang')
};

function t(k) { return (STR[LANG] && STR[LANG][k]) || STR['en'][k] || k }

/* i18n apply */
function applyI18n(){
  $('app-title').textContent = t('appTitle');
  $('app-sub').textContent = t('appSub');
  $('voter-heading').textContent = t('voterHeading');
  $('voter-hint').textContent = t('voterHint');
  $('send-otp-label').textContent = t('sendOtp');
  $('verify-otp').textContent = t('verify');
  $('ballot-heading').textContent = t('ballotHeading');
  $('cast-vote').textContent = t('castVote');
  $('confirm-heading').textContent = t('thanks');
  $('admin-heading').textContent = t('adminHeading');
  $('admin-hint')?.textContent && ($('admin-hint').textContent = t('adminHint'));
  refs.modeLabel.textContent = (MODE === 'admin' ? t('modeAdmin') : t('modeVoter'));
}

/* admin utility functions */
function adminLog(msg){
  const now = new Date().toLocaleString();
  refs.adminLog.innerHTML = `<div>[${now}] ${msg}</div>` + refs.adminLog.innerHTML;
}

function getElections(){ return storage.get('evote_elections', []) }
function putElections(arr){ storage.set('evote_elections', arr) }
function getVotes(){ return storage.get('evote_votes', {}) }
function putVotes(obj){ storage.set('evote_votes', obj) }
function getVoters(){ return storage.get('evote_voters', {}) }
function putVoters(obj){ storage.set('evote_voters', obj) }

/* UI switching */
function showView(name){
  // name: 'login'|'ballot'|'confirm'|'admin'
  refs.loginView.classList.remove('active'); refs.ballotView.classList.remove('active'); refs.confirmView.classList.remove('active'); refs.adminView.classList.remove('active');
  if (name === 'login') refs.loginView.classList.add('active');
  if (name === 'ballot') refs.ballotView.classList.add('active');
  if (name === 'confirm') refs.confirmView.classList.add('active');
  if (name === 'admin') refs.adminView.classList.add('active');
  refs.modeLabel.textContent = (MODE === 'admin' ? t('modeAdmin') : t('modeVoter'));
}

/* init */
document.addEventListener('DOMContentLoaded', ()=>{
  // default language
  LANG = localStorage.getItem('evote_lang') || 'en';
  refs.langSelect.value = LANG;
  applyI18n();
  refreshAll();

  // attach handlers
  refs.langSelect.addEventListener('change', (e) => { LANG = e.target.value; localStorage.setItem('evote_lang', LANG); applyI18n(); });
  refs.modeToggle.addEventListener('click', toggleMode);
  refs.openVoter.addEventListener('click', ()=>{ MODE='voter'; showView('login'); applyI18n() });
  refs.openAdmin.addEventListener('click', ()=>{ MODE='admin'; showView('admin'); applyI18n() });

  // voter handlers
  refs.sendOtp.addEventListener('click', sendOtpHandler);
  refs.verifyOtp.addEventListener('click', verifyOtpHandler);
  refs.resendOtp.addEventListener('click', resendOtpHandler);
  refs.clearLogin.addEventListener('click', ()=>{ refs.aadhaar.value=''; refs.dob.value=''; refs.contact.value=''; refs.otpInput.value=''; refs.otpArea.hidden=true });
  refs.backToLogin.addEventListener('click', ()=>{ storage.rm('evote_current'); showView('login') });
  refs.castVote.addEventListener('click', castVoteHandler);
  refs.finish.addEventListener('click', finishHandler);
  refs.viewReceipt.addEventListener('click', viewReceiptHandler);

  // admin handlers
  refs.createElection.addEventListener('click', createElectionHandler);
  refs.addCandidate.addEventListener('click', addCandidateHandler);
  refs.clearCandidate.addEventListener('click', ()=>{ refs.cName.value=''; refs.cParty.value=''; refs.cSymbol.value=''; });
  refs.importRoll.addEventListener('click', importVoterRoll);
  refs.clearRoll.addEventListener('click', clearRollHandler);
  refs.selectElection.addEventListener('change', ()=>{ refreshCandidates(); refreshVoters(); refreshStats(); refreshChart(); });
  refs.startElection.addEventListener('click', ()=>{ setElectionStatus('running') });
  refs.stopElection.addEventListener('click', ()=>{ setElectionStatus('stopped') });
  refs.loadSample.addEventListener('click', loadSampleElection);
  refs.exportData.addEventListener('click', exportAll);
  refs.resetAll.addEventListener('click', resetAll);
  refs.resultsChartEl.addEventListener('click', ()=>{}); // placeholder

});

/* toggle admin/voter */
function toggleMode(){
  MODE = (MODE === 'voter') ? 'admin' : 'voter';
  showView(MODE === 'admin' ? 'admin' : 'login');
  applyI18n();
}

/* OTP simulation & verification */
function sendOtpHandler(){
  const aad = refs.aadhaar.value.trim();
  const dob = refs.dob.value;
  if (!/^\d{12}$/.test(aad)) { alert('Aadhaar must be 12 digits (demo)'); return; }
  if (!dob) { alert('Enter DOB'); return; }
  // pick an active election
  let eid = refs.selectElection.value || (getElections().find(x=>x.status==='running')||{}).id;
  if (!eid){ alert('No active election. Admin: create/start an election or select one.'); return; }
  // check roll if exists
  const voters = getVoters(); const roll = voters[eid]||{};
  if (Object.keys(roll).length && !roll[aad]) { if (!confirm('Aadhaar not found in roll. Continue for demo?')) return; }

  const otp = (Math.floor(100000 + Math.random()*900000)).toString();
  STATE.otpMap[aad] = otp;
  adminLog(`OTP for ${aad}: ${otp} (demo)`);
  refs.otpArea.hidden = false;
  alert('OTP (demo) sent — check admin log.');
}

function resendOtpHandler(){
  const aad = refs.aadhaar.value.trim();
  if (!aad || !STATE.otpMap[aad]) { alert('No OTP in progress for this Aadhaar'); return; }
  const otp = (Math.floor(100000 + Math.random()*900000)).toString();
  STATE.otpMap[aad] = otp;
  adminLog(`Resent OTP for ${aad}: ${otp} (demo)`);
  alert('OTP resent (demo).');
}

function verifyOtpHandler(){
  const aad = refs.aadhaar.value.trim();
  const otp = refs.otpInput.value.trim();
  if (!STATE.otpMap[aad] || STATE.otpMap[aad] !== otp) { alert('Invalid OTP (demo)'); return; }
  // success: create evote_current
  const elections = getElections(); const eid = refs.selectElection.value || (elections.find(x=>x.status==='running')||{}).id;
  const voters = getVoters();
  const roll = voters[eid] || {};
  const info = roll[aad] || { name: 'Guest Voter', phone: refs.contact.value || '', email:'' };
  const current = { aadhaar: aad, name: info.name || 'Voter', lang: info.lang || LANG, electionId: eid };
  storage.set('evote_current', current);
  delete STATE.otpMap[aad];
  adminLog(`Verified voter ${aad} (${current.name}) for election ${eid}`);
  prepareBallot(eid);
  showView('ballot');
}

/* prepare ballot UI from election */
function prepareBallot(eid){
  const elections = getElections(); const e = elections.find(x=>x.id===eid);
  if (!e) { alert('Election not found'); return; }
  refs.electionTitle.textContent = e.name + (e.constituency ? ` — ${e.constituency}` : '');
  const current = storage.get('evote_current'); refs.voterName.textContent = current.name; refs.voterAad.textContent = current.aadhaar;
  // clear existing
  refs.candidatesDiv.innerHTML = '';
  // seats config
  const seats = e.seats || 1;
  e.candidates.forEach(c=>{
    const div = document.createElement('div'); div.className='candidate'; div.tabIndex=0; div.setAttribute('role','listitem');
    const symbol = document.createElement('div'); symbol.className='symbol';
    if (c.symbolData) { const img = document.createElement('img'); img.src = c.symbolData; img.alt = `${c.name} symbol`; img.style.width='100%'; img.style.height='100%'; img.style.objectFit='cover'; img.style.borderRadius='8px'; symbol.appendChild(img); }
    else { symbol.textContent = (c.party && c.party[0]) || '?'; symbol.style.background = randomColor(c.party || c.name); }

    const info = document.createElement('div'); info.className='info'; info.innerHTML = `<h4>${c.name}</h4><p class="kv">${c.party || ''}</p>`;
    div.appendChild(symbol); div.appendChild(info);

    // selection behavior: for multi-seat allow selecting up to seats
    div.dataset.cid = c.id;
    div.addEventListener('click', ()=> toggleCandidateSelection(div, seats));
    div.addEventListener('keypress', (e)=>{ if(e.key==='Enter') toggleCandidateSelection(div, seats) });
    refs.candidatesDiv.appendChild(div);
  });
}

/* toggle candidate selection respecting seats */
function toggleCandidateSelection(div, seats){
  const selected = Array.from(document.querySelectorAll('.candidate.selected'));
  if (!div.classList.contains('selected')){
    if (selected.length >= seats){
      // if seats==1 then clear previous, else block
      if (seats === 1){
        selected.forEach(s => s.classList.remove('selected'));
        div.classList.add('selected');
      } else {
        alert(`This election allows maximum ${seats} selections`);
      }
    } else {
      div.classList.add('selected');
    }
  } else {
    div.classList.remove('selected');
  }
}

/* cast vote */
function castVoteHandler(){
  const current = storage.get('evote_current');
  if (!current) { alert('Session expired. Please verify again.'); showView('login'); return; }
  const selected = Array.from(document.querySelectorAll('.candidate.selected')).map(d=>d.dataset.cid);
  if (!selected.length) { alert('Select at least one candidate'); return; }
  const eid = current.electionId;
  const votes = getVotes();
  votes[eid] = votes[eid] || {};
  if (votes[eid][current.aadhaar]) { alert('You have already voted in this election'); return; }
  // store selection
  votes[eid][current.aadhaar] = selected;
  putVotes(votes);
  adminLog(`Voter ${current.aadhaar} cast vote in ${eid}: ${selected.join(',')}`);
  refreshStats();
  refreshChart();
  showView('confirm');
}

/* view receipt */
function viewReceiptHandler(){
  const current = storage.get('evote_current'); if (!current) return;
  const votes = getVotes(); const my = votes[current.electionId] && votes[current.electionId][current.aadhaar];
  const elections = getElections(); const e = elections.find(x=>x.id===current.electionId);
  const choices = (e && my) ? my.map(cid => (e.candidates.find(c=>c.id===cid)||{name:'?' }).name).join(', ') : '(none)';
  refs.receiptBody.innerHTML = `<div><strong>${e ? e.name : ''}</strong></div>
    <div class="kv">Voter: ${current.name} (${current.aadhaar})</div>
    <div style="height:8px"></div>
    <div><strong>Voted for:</strong> ${choices}</div>`;
  refs.modal.setAttribute('aria-hidden','false');
}

/* finish */
function finishHandler(){
  storage.rm('evote_current');
  showView('login');
}

/* admin: create election */
function createElectionHandler(){
  const name = refs.eName.value.trim(); if (!name) return alert('Enter election name');
  const id = 'e_' + Math.random().toString(36).slice(2,9);
  const seats = parseInt(refs.eSeats?.value || 1) || 1;
  const election = {
    id, name, constituency: refs.eConst.value.trim(), start: refs.eStart.value || null, end: refs.eEnd.value || null,
    candidates: [], seats, status: 'draft'
  };
  const arr = getElections(); arr.unshift(election); putElections(arr);
  adminLog(`Created election "${name}" (${id})`);
  refreshAll();
  alert('Election created — now select it from dropdown to add candidates and upload voters.');
}

/* add candidate */
async function addCandidateHandler(){
  const eid = refs.selectElection.value; if (!eid) return alert('Select an election first');
  const name = refs.cName.value.trim(); const party = refs.cParty.value.trim();
  if (!name || !party) return alert('Enter candidate name & party');
  let symbolData = null; const f = refs.cSymbol.files[0];
  if (f) symbolData = await fileToDataURL(f);
  const elections = getElections(); const e = elections.find(x=>x.id===eid);
  e.candidates.push({ id: 'c_' + Math.random().toString(36).slice(2,9), name, party, symbolData });
  putElections(elections);
  adminLog(`Added candidate ${name} (${party}) to ${e.name}`);
  refs.cName.value=''; refs.cParty.value=''; refs.cSymbol.value='';
  refreshCandidates(); refreshChart();
}

/* helper to convert file to base64 */
function fileToDataURL(file){ return new Promise((res,rej)=>{ const r = new FileReader(); r.onload = e => res(e.target.result); r.onerror = rej; r.readAsDataURL(file); }) }

/* import voter roll (CSV or JSON) */
async function importVoterRoll(){
  const f = refs.vRollFile.files[0]; if (!f) return alert('Choose a file');
  const txt = await f.text();
  let rows = [];
  if (f.name.toLowerCase().endsWith('.json')) {
    try { rows = JSON.parse(txt); } catch(e) { return alert('Invalid JSON') }
  } else {
    // CSV simple parser
    const lines = txt.split(/\r?\n/).filter(r=>r.trim());
    const hdr = lines.shift().split(',').map(h=>h.trim().toLowerCase());
    rows = lines.map(l => {
      const cols = l.split(',');
      const obj = {};
      hdr.forEach((h,i)=> obj[h] = (cols[i]||'').trim());
      return obj;
    });
  }
  const eid = refs.selectElection.value; if (!eid) return alert('Select election first');
  const voters = getVoters(); voters[eid] = voters[eid] || {};
  let added = 0;
  rows.forEach(r=>{
    const aad = (r.aadhaar || r.id || r.aadhar || '').toString().trim();
    if (!aad) return;
    voters[eid][aad] = { name: r.name||'', phone: r.phone||'', email: r.email||'', lang: r.lang||LANG };
    added++;
  });
  putVoters(voters);
  adminLog(`Imported ${added} voters into ${eid}`);
  refreshVoters(); refreshStats();
}

/* clear roll for selected election */
function clearRollHandler(){
  const eid = refs.selectElection.value; if (!eid) return alert('Select election');
  if (!confirm('Clear voter roll for selected election?')) return;
  const voters = getVoters(); voters[eid] = {}; putVoters(voters);
  adminLog(`Cleared roll for ${eid}`);
  refreshVoters(); refreshStats();
}

/* set election status */
function setElectionStatus(status){
  const eid = refs.selectElection.value; if (!eid) return alert('Select election');
  const arr = getElections(); const e = arr.find(x=>x.id===eid);
  e.status = status; putElections(arr);
  if (status === 'running') adminLog(`Started election ${e.name}`);
  else adminLog(`Stopped election ${e.name}`);
  refreshAll();
}

/* load sample */
function loadSampleElection(){
  const sample = {
    id: 'e_demo',
    name: 'Sample Local Election 2025',
    constituency: 'Demo Constituency',
    start: null, end: null, seats: 1,
    status: 'running',
    candidates: [
      { id:'c_demo1', name:'Amit Sharma', party:'People Forward', symbolData: null },
      { id:'c_demo2', name:'Rina Patel', party:'Green Alliance', symbolData: null },
      { id:'c_demo3', name:'Rajesh Rao', party:'Unity Party', symbolData: null }
    ]
  };
  const elections = getElections(); elections.unshift(sample); putElections(elections);
  const voters = getVoters(); voters['e_demo'] = voters['e_demo'] || {};
  ['111122223333','222233334444','333344445555','444455556666','555566667777'].forEach((a,i)=>{
    voters['e_demo'][a] = { name: `Demo Voter ${i+1}`, phone:'9999900000', email:'demo@example.com', lang:'en' };
  });
  putVoters(voters);
  putVotes(Object.assign({}, getVotes(), { 'e_demo': {} }));
  adminLog('Loaded sample election and demo voters');
  refreshAll();
}

/* refresh UI lists */
function refreshAll(){
  refreshElectionSelector();
  refreshCandidates();
  refreshVoters();
  refreshStats();
  refreshChart();
}

/* election select */
function refreshElectionSelector(){
  const sel = refs.selectElection; sel.innerHTML = '<option value="">— select election —</option>';
  const elections = getElections();
  elections.forEach(e => { const opt = document.createElement('option'); opt.value = e.id; opt.textContent = e.name + (e.constituency?(' — '+e.constituency):''); sel.appendChild(opt); });
  // pick first running as default
  const running = elections.find(x=>x.status==='running') || elections[0];
  if (running) sel.value = running.id;
}

/* refresh candidate table */
function refreshCandidates(){
  refs.cTable.innerHTML = '';
  const eid = refs.selectElection.value; if (!eid) return;
  const elections = getElections(); const e = elections.find(x=>x.id===eid);
  e.candidates.forEach(c=>{
    const tr = document.createElement('tr');
    tr.innerHTML = `<td style="width:55%"><div style="display:flex;gap:8px;align-items:center">
      <div style="width:36px;height:36px;border-radius:6px;overflow:hidden">${c.symbolData?'<img src="'+c.symbolData+'" style="width:100%;height:100%;object-fit:cover">':'<div style="background:#333;color:#fff;width:36px;height:36px;display:flex;align-items:center;justify-content:center">'+(c.party?c.party[0]:'?')+'</div>'}</div>
      <div><strong>${c.name}</strong><div class="kv">${c.party}</div></div></div></td>
      <td style="width:30%">${c.party}</td>
      <td style="width:15%"><button class="btn ghost" data-cid="${c.id}" data-eid="${eid}">Remove</button></td>`;
    refs.cTable.appendChild(tr);
  });
  // delegate remove
  refs.cTable.querySelectorAll('button[data-cid]').forEach(btn=>{
    btn.addEventListener('click', (ev)=>{
      const cid = btn.dataset.cid, eid = btn.dataset.eid;
      if (!confirm('Remove candidate?')) return;
      const arr = getElections(); const e = arr.find(x=>x.id===eid);
      e.candidates = e.candidates.filter(c=>c.id !== cid); putElections(arr);
      adminLog(`Removed candidate ${cid} from ${e.name}`);
      refreshCandidates(); refreshChart();
    });
  });
}

/* refresh voter table */
function refreshVoters(){
  refs.vTable.innerHTML = '';
  const eid = refs.selectElection.value; if (!eid) return;
  const voters = getVoters(); const map = voters[eid] || {};
  Object.entries(map).slice(0,200).forEach(([aad,info])=>{
    const tr = document.createElement('tr');
    tr.innerHTML = `<td>${aad}</td><td>${info.name || '-'}</td>`;
    refs.vTable.appendChild(tr);
  });
}

/* stats */
function refreshStats(){
  const eid = refs.selectElection.value || (getElections().find(x=>x.status==='running')||{}).id;
  if (!eid){ refs.statVoted.textContent='0'; refs.statEligible.textContent='0'; refs.statTurnout.textContent='0%'; refs.widgetBody.textContent='No active election'; return; }
  const votes = getVotes(); const voters = getVoters();
  const vmap = votes[eid] || {}; const voterRoll = voters[eid] || {};
  const voted = Object.keys(vmap).length; const eligible = Object.keys(voterRoll).length;
  const pct = eligible ? Math.round((voted/eligible)*100) : (voted ? 100 : 0);
  refs.statVoted.textContent = voted; refs.statEligible.textContent = eligible; refs.statTurnout.textContent = pct + '%';
  const e = getElections().find(x=>x.id===eid);
  refs.widgetTitle.textContent = e ? (e.name + (e.status ? ' — ' + e.status : '')) : 'Active Election';
  refs.widgetBody.innerHTML = e ? `<div class="kv">${e.constituency||''}</div><div class="kv">Seats: ${e.seats || 1}</div>` : '';
}

/* results chart */
let resultsChart = null;
function refreshChart(){
  const eid = refs.selectElection.value || (getElections().find(x=>x.status==='running')||{}).id;
  if (!eid) return;
  const e = getElections().find(x=>x.id===eid); if(!e) return;
  const votes = getVotes()[eid] || {};
  const counts = {}; e.candidates.forEach(c=>counts[c.id]=0);
  Object.values(votes).forEach(selection => {
    if (Array.isArray(selection)) selection.forEach(cid=>{ if (counts[cid] !== undefined) counts[cid]++ });
    else if (counts[selection] !== undefined) counts[selection]++;
  });
  const labels = e.candidates.map(c=>`${c.name} (${c.party})`);
  const data = e.candidates.map(c=>counts[c.id] || 0);
  if (resultsChart) resultsChart.destroy();
  resultsChart = new Chart(refs.resultsChartEl.getContext('2d'), {
    type: 'bar',
    data: { labels, datasets: [{ label:'Votes', data, backgroundColor: e.candidates.map(c=>randomColor(c.party || c.name)) }] },
    options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}} }
  });
}

/* helper random color */
function randomColor(seed){
  let h = 0; for (let i=0;i<seed.length;i++) h = (h<<5)-h+seed.charCodeAt(i);
  const hue = Math.abs(h) % 360; return `hsl(${hue} 70% 45%)`;
}

/* export / reset */
function exportAll(){
  const payload = { elections: getElections(), voters: getVoters(), votes: getVotes() };
  const blob = new Blob([JSON.stringify(payload, null, 2)], { type:'application/json' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a'); a.href = url; a.download = 'evote-export.json'; a.click(); URL.revokeObjectURL(url);
}
function resetAll(){
  if (!confirm('Reset all demo data?')) return;
  storage.rm('evote_elections'); storage.rm('evote_votes'); storage.rm('evote_voters'); storage.rm('evote_current');
  storage.set('evote_elections', []); storage.set('evote_votes', {}); storage.set('evote_voters', {});
  adminLog('(reset demo storage)');
  refreshAll();
}

/* utilities */
function fileToDataURL(file){ return new Promise((res,rej)=>{ const r=new FileReader(); r.onload=e=>res(e.target.result); r.onerror=rej; r.readAsDataURL(file); }) }

/* adminLog helper */
function adminLog(msg){ const now = new Date().toLocaleString(); refs.adminLog.innerHTML = `<div>[${now}] ${msg}</div>` + refs.adminLog.innerHTML }

/* refresh initial state */
function refreshAll(){
  refreshElectionSelector(); refreshCandidates(); refreshVoters(); refreshStats(); refreshChart();
}

/* election selector & candidate/voter refreshers */
function refreshElectionSelector(){
  const sel = refs.selectElection; sel.innerHTML = '<option value="">— select election —</option>';
  getElections().forEach(e => { const o = document.createElement('option'); o.value = e.id; o.textContent = e.name + (e.constituency?(' — ' + e.constituency):''); sel.appendChild(o); });
  const running = getElections().find(x=>x.status === 'running') || getElections()[0];
  if (running) sel.value = running.id;
}
function refreshCandidates(){
  refs.cTable.innerHTML = ''; const eid = refs.selectElection.value; if (!eid) return;
  const e = getElections().find(x=>x.id===eid); if (!e) return;
  e.candidates.forEach(c => {
    const tr = document.createElement('tr');
    tr.innerHTML = `<td style="width:55%"><div style="display:flex;gap:8px;align-items:center">
      <div style="width:36px;height:36px;border-radius:6px;overflow:hidden">${c.symbolData?'<img src="'+c.symbolData+'" style="width:100%;height:100%;object-fit:cover">':'<div style="background:#333;color:#fff;width:36px;height:36px;display:flex;align-items:center;justify-content:center">'+(c.party?c.party[0]:'?')+'</div>'}</div>
      <div><strong>${c.name}</strong><div class="kv">${c.party}</div></div></div></td>
      <td style="width:30%">${c.party}</td>
      <td style="width:15%"><button class="btn ghost" data-cid="${c.id}" data-eid="${eid}">Remove</button></td>`;
    refs.cTable.appendChild(tr);
  });
  refs.cTable.querySelectorAll('button[data-cid]').forEach(btn => {
    btn.addEventListener('click', () => {
      const cid = btn.dataset.cid; const eid = btn.dataset.eid;
      if (!confirm('Remove candidate?')) return;
      const arr = getElections(); const e = arr.find(x=>x.id===eid); e.candidates = e.candidates.filter(cc=>cc.id!==cid); putElections(arr);
      adminLog(`Removed candidate ${cid} from ${e.name}`); refreshCandidates(); refreshChart();
    });
  });
}
function refreshVoters(){
  refs.vTable.innerHTML = ''; const eid = refs.selectElection.value; if (!eid) return;
  const voters = getVoters(); const map = voters[eid] || {};
  Object.entries(map).slice(0,200).forEach(([aad, info]) => {
    const tr = document.createElement('tr'); tr.innerHTML = `<td>${aad}</td><td>${info.name || '-'}</td>`;
    refs.vTable.appendChild(tr);
  });
}

/* Run initial refresh */
refreshAll();

/* Expose some functions for console debugging (optional) */
window.evote = {
  getElections, getVotes, getVoters, refreshAll, adminLog
};
