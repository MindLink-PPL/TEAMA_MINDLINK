/* ============================================================
   MindLink – Student appointments (PBI #12, with #14 polling)
   Talks to Laravel routes:
     GET    /api/counsellors
     GET    /api/slots?counsellor_id=&date=&ignore=
     GET    /api/appointments
     POST   /api/appointments
     PUT    /api/appointments/{id}
     DELETE /api/appointments/{id}
   ============================================================ */

const csrf = document.querySelector('meta[name=csrf-token]').content;

async function api(url, method = 'GET', body) {
  const res = await fetch(url, {
    method,
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrf,
    },
    body: body ? JSON.stringify(body) : undefined,
  });
  if (!res.ok) {
    const err = await res.json().catch(() => ({}));
    throw new Error(err.message || 'Something went wrong');
  }
  return res.status === 204 ? null : res.json();
}

/* ---------- Config ---------- */
const MODES = ['In person', 'Online'];
const POLL_MS = 15000;

// local-time YYYY-MM-DD (avoids timezone shifts from toISOString)
const pad = n => String(n).padStart(2, '0');
const iso = d => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

// next 7 weekdays, starting tomorrow
const DAYS = (() => {
  const out = [];
  const d = new Date();
  while (out.length < 7) {
    d.setDate(d.getDate() + 1);
    if (d.getDay() % 6) out.push(iso(d)); // skip Sat (6) and Sun (0)
  }
  return out;
})();

const fmt = (s, o) => new Date(s + 'T00:00').toLocaleDateString('en-GB', o);

/* ---------- State ---------- */
let COUNSELLORS = [];
let freeSlots = [];   // times still available for the selected counsellor + date
let mine = [];        // this student's confirmed appointments
let lastUpdated = Date.now();

const S = {
  c: null,            // selected counsellor id
  d: DAYS[0],         // selected date
  t: null,            // selected time
  mode: MODES[0],
  resch: null,        // id of appointment being rescheduled
};

const $ = id => document.getElementById(id);

function toast(msg) {
  const t = $('toast');
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2400);
}

function ask(title, text, onYes) {
  $('dlgT').textContent = title;
  $('dlgP').textContent = text;
  $('dlg').showModal();
  $('dlgYes').onclick = () => { $('dlg').close(); onYes(); };
}
$('dlgNo').onclick = () => $('dlg').close();

const ago = ts => {
  const s = Math.round((Date.now() - ts) / 1000);
  return s < 10 ? 'just now' : s < 60 ? `${s}s ago` : `${Math.round(s / 60)} min ago`;
};

/* ---------- Data loading ---------- */
async function refresh({ silent = false } = {}) {
  if (!S.c) return render();
  try {
    const url = `/api/slots?counsellor_id=${S.c}&date=${S.d}` + (S.resch ? `&ignore=${S.resch}` : '');
    [freeSlots, mine] = await Promise.all([api(url), api('/api/appointments')]);
    lastUpdated = Date.now();

    // PBI #14: if the slot the student picked disappeared, tell them
    if (S.t && !freeSlots.includes(S.t)) {
      S.t = null;
      if (silent) toast('Availability changed. Please pick another time.');
    }
  } catch (e) {
    if (!silent) toast(e.message);
  }
  render();
}

/* ---------- Rendering ---------- */
function render() {
  // counsellor dropdown
  $('cSel').innerHTML = COUNSELLORS.length
    ? COUNSELLORS.map(c => `<option value="${c.id}" ${c.id == S.c ? 'selected' : ''}>${c.name}</option>`).join('')
    : '<option>No counsellors available</option>';

  // date chips
  $('days').innerHTML = DAYS.map(d =>
    `<button class="day ${d === S.d ? 'on' : ''}" data-d="${d}">
       ${fmt(d, { day: 'numeric', month: 'short' })}<small>${fmt(d, { weekday: 'short' })}</small>
     </button>`).join('');
  $('dateLbl').textContent = fmt(S.d, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });

  // slots
  $('slots').innerHTML = freeSlots.length
    ? freeSlots.map(t => `<button class="slot ${t === S.t ? 'on' : ''}" data-t="${t}">${t}</button>`).join('')
    : '<p class="empty">No free slots on this day. Try another date or counsellor.</p>';

  // mode chips
  $('modes').innerHTML = MODES.map(m => `<button class="chip ${m === S.mode ? 'on' : ''}" data-m="${m}">${m}</button>`).join('');

  // form heading (book vs reschedule)
  const r = S.resch && mine.find(a => a.id === S.resch);
  $('formTitle').textContent = r ? 'Reschedule appointment' : 'Choose an available time';
  $('formSub').textContent = r
    ? `Currently ${fmt(r.date, { day: 'numeric', month: 'short' })} at ${r.time} with ${r.counsellor.name}. Pick a new time.`
    : 'Pick a counsellor, a day and a slot.';
  $('confirmBtn').textContent = r ? 'Confirm new time' : 'Confirm booking';
  $('confirmBtn').disabled = !S.t;
  $('cancelResch').classList.toggle('hide', !r);
  $('updated').textContent = 'Availability updated ' + ago(lastUpdated);

  // my appointments
  $('myAppts').innerHTML = mine.length
    ? mine.map(a => `
      <div class="appt">
        <div class="row">
          <div>
            <b>${fmt(a.date, { weekday: 'long', day: 'numeric', month: 'long' })}, ${a.time}</b>
            <p>${a.counsellor.name} • ${a.mode}</p>
          </div>
          <span class="tag ${S.resch === a.id ? 'p' : ''}">${S.resch === a.id ? 'Rescheduling' : 'Confirmed'}</span>
        </div>
        <div class="acts">
          <button class="btn o s" data-resch="${a.id}">Reschedule</button>
          <button class="btn d s" data-cancel="${a.id}">Cancel</button>
        </div>
      </div>`).join('')
    : '<p class="empty">No appointments yet. Choose a time to book your first session.</p>';
}

/* ---------- Events ---------- */
$('cSel').onchange = e => { S.c = Number(e.target.value); S.t = null; refresh(); };

$('days').onclick = e => {
  const b = e.target.closest('[data-d]');
  if (b) { S.d = b.dataset.d; S.t = null; refresh(); }
};

$('slots').onclick = e => {
  const b = e.target.closest('[data-t]');
  if (b) { S.t = b.dataset.t; render(); }
};

$('modes').onclick = e => {
  const b = e.target.closest('[data-m]');
  if (b) { S.mode = b.dataset.m; render(); }
};

$('cancelResch').onclick = () => { S.resch = null; S.t = null; refresh(); };

$('confirmBtn').onclick = async () => {
  const payload = { counsellor_id: S.c, date: S.d, time: S.t, mode: S.mode };
  $('confirmBtn').disabled = true;
  try {
    if (S.resch) {
      await api(`/api/appointments/${S.resch}`, 'PUT', payload);
      toast('Appointment rescheduled');
    } else {
      await api('/api/appointments', 'POST', payload);
      toast('Appointment booked');
    }
    S.resch = null;
  } catch (e) {
    toast(e.message); // e.g. "Slot no longer available"
  }
  S.t = null;
  refresh();
};

$('myAppts').onclick = e => {
  const rs = e.target.closest('[data-resch]');
  const cn = e.target.closest('[data-cancel]');

  if (rs) {
    const a = mine.find(x => x.id === Number(rs.dataset.resch));
    S.resch = a.id;
    S.c = a.counsellor_id;
    S.d = DAYS.includes(a.date) ? a.date : DAYS[0];
    S.mode = a.mode;
    S.t = null;
    refresh();
    scrollTo({ top: 0, behavior: 'smooth' });
  }

  if (cn) {
    ask('Cancel this appointment?', 'The slot will open up for other students.', async () => {
      try {
        await api(`/api/appointments/${cn.dataset.cancel}`, 'DELETE');
        if (S.resch === Number(cn.dataset.cancel)) S.resch = null;
        toast('Appointment cancelled');
      } catch (err) {
        toast(err.message);
      }
      refresh();
    });
  }
};

/* ---------- Startup & live updates (PBI #14) ---------- */
async function init() {
  try {
    COUNSELLORS = await api('/api/counsellors');
    S.c = COUNSELLORS[0]?.id ?? null;
  } catch (e) {
    toast(e.message);
  }
  await refresh();

  // poll for fresh availability; skip while a dialog is open
  setInterval(() => { if (!$('dlg').open) refresh({ silent: true }); }, POLL_MS);
  // update the "updated x ago" label between polls
  setInterval(() => { $('updated').textContent = 'Availability updated ' + ago(lastUpdated); }, 5000);
}

init();