const STORE_KEY = 'mindlink.counselling.requests.v1';
const ROLE_KEY = 'mindlink.role.v1';
const STUDENT = { name: 'Ava Chen', id: 'STU-1042' };
const initialRequests = [
  { id: 'ML-2048', studentName: 'Ava Chen', studentId: 'STU-1042', topic: 'Managing study stress', priority: 'Follow-up', createdAt: '2026-10-28', status: 'Ongoing', counsellor: 'Maya Thompson', history: [{ status: 'Pending', date: '2026-10-28' }, { status: 'Scheduled', date: '2026-10-29' }, { status: 'Ongoing', date: '2026-10-31' }] },
  { id: 'ML-2041', studentName: 'Ava Chen', studentId: 'STU-1042', topic: 'Finding a better balance', priority: 'New', createdAt: '2026-10-25', status: 'Completed', counsellor: 'Maya Thompson', history: [{ status: 'Pending', date: '2026-10-25' }, { status: 'Scheduled', date: '2026-10-26' }, { status: 'Ongoing', date: '2026-10-29' }, { status: 'Completed', date: '2026-11-01' }] },
  { id: 'ML-2037', studentName: 'Jordan Lee', studentId: 'STU-1088', topic: 'Building confidence', priority: 'High', createdAt: '2026-10-24', status: 'Scheduled', counsellor: 'Sam Rivera', history: [{ status: 'Pending', date: '2026-10-24' }, { status: 'Scheduled', date: '2026-10-27' }] },
  { id: 'ML-2030', studentName: 'Priya Nair', studentId: 'STU-1126', topic: 'Support with homesickness', priority: 'New', createdAt: '2026-10-20', status: 'Pending', counsellor: 'Sam Rivera', history: [{ status: 'Pending', date: '2026-10-20' }] }
];
const statuses = ['Pending', 'Scheduled', 'Ongoing', 'Completed', 'Cancelled'];
const responseCopy = { Pending: 'Your request is waiting for counsellor review. We’ll update you when there is progress.', Scheduled: 'Your session has been scheduled. Your counsellor will share any next steps with you.', Ongoing: 'Your counselling is in progress. You can check this page for updates at any time.', Completed: 'Your counselling has been completed. We hope the support was helpful.', Cancelled: 'This request has been cancelled. You can submit a new request whenever you are ready.' };
const icons = { Dashboard: '⌂', Learn: '◉', Wellbeing: '♡', Counselling: '✳', Notifications: '○', Workspace: '⌂', Requests: '▤' };
let requests = loadRequests();
let role = localStorage.getItem(ROLE_KEY) || 'Student';
let page = role === 'Student' ? 'Dashboard' : 'Workspace';
let selectedId = null;
let filter = 'All';
let toastTimer;
const content = document.getElementById('content');

function loadRequests() { try { const saved = localStorage.getItem(STORE_KEY); return saved ? JSON.parse(saved) : structuredClone(initialRequests); } catch { return structuredClone(initialRequests); } }
function saveRequests() { localStorage.setItem(STORE_KEY, JSON.stringify(requests)); }
function esc(value) { return String(value).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])); }
function prettyDate(date) { return new Date(`${date}T12:00:00`).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }); }
function shortDate(date) { return new Date(`${date}T12:00:00`).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }); }
function ownRequests() { return requests.filter(r => r.studentId === STUDENT.id); }
function statusClass(status) { return `status-${status.toLowerCase()}`; }
function badge(status) { return `<span class="status-badge ${statusClass(status)}">${esc(status)}</span>`; }
function priorityBadge(priority) { return `<span class="priority-badge priority-${priority.toLowerCase().replace(/\s+/g, '-')}">${esc(priority)}</span>`; }
function topicIcon(topic) { return /stress|balance/i.test(topic) ? '☼' : /confidence/i.test(topic) ? '◇' : '♡'; }
function navItems() { return role === 'Student' ? ['Dashboard', 'Learn', 'Wellbeing', 'Counselling', 'Notifications'] : ['Workspace', 'Requests']; }
function renderNav() {
  document.getElementById('main-nav').innerHTML = navItems().map(item => `<button type="button" class="nav-item ${page === item || (item === 'Counselling' && page === 'Request detail') ? 'active' : ''}" data-page="${item}"><span class="nav-icon" aria-hidden="true">${icons[item]}</span><span>${item}</span></button>`).join('');
  document.querySelectorAll('.nav-item').forEach(button => button.addEventListener('click', () => { page = button.dataset.page; selectedId = null; render(); }));
}
function updateChrome() {
  const counsellor = role === 'Counsellor';
  document.getElementById('portal-label').textContent = `${role} portal`;
  document.getElementById('page-crumb').textContent = page;
  document.getElementById('profile-name').textContent = counsellor ? 'Maya Thompson' : STUDENT.name;
  document.getElementById('profile-role').textContent = counsellor ? 'Counsellor account' : 'Student account';
  document.getElementById('profile-avatar').textContent = counsellor ? 'M' : 'A';
  renderNav();
}
function heading(title, subtitle, date = false) { return `<div class="page-heading"><div><div class="eyebrow">${role === 'Student' ? 'YOUR WELLBEING, YOUR PACE' : 'COUNSELLOR WORKSPACE'}</div><h1>${title}</h1><p>${subtitle}</p></div>${date ? `<div class="date-label">${new Date().toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' })}</div>` : ''}</div>`; }
function statCard(label, value, icon) { return `<div class="stat-card"><div class="stat-copy"><span>${label}</span><strong>${value}</strong></div><span class="stat-icon" aria-hidden="true">${icon}</span></div>`; }
function studentRow(r) { return `<div class="request-row"><span class="topic-icon" aria-hidden="true">${topicIcon(r.topic)}</span><div class="request-main"><strong>${esc(r.topic)}</strong><small>${esc(r.id)} · ${esc(r.counsellor)}</small></div><div class="row-right">${badge(r.status)}<span class="row-date">${shortDate(r.createdAt)}</span></div></div>`; }
function requestCard(r) { return `<article class="request-card"><span class="topic-icon" aria-hidden="true">${topicIcon(r.topic)}</span><div class="request-main"><strong>${esc(r.topic)}</strong><div class="request-meta"><small>${esc(r.id)} · Submitted ${shortDate(r.createdAt)}</small>${priorityBadge(r.priority)}</div></div><div class="request-card-actions">${badge(r.status)}<button class="open-button" type="button" data-open="${esc(r.id)}">View details&nbsp; →</button></div></article>`; }
function emptyState(title, message) { return `<div class="empty-state"><div class="empty-illustration" aria-hidden="true">✳</div><h3>${title}</h3><p>${message}</p></div>`; }
function renderDashboard() {
  const own = ownRequests(); const active = own.filter(r => !['Completed', 'Cancelled'].includes(r.status)); const completed = own.filter(r => r.status === 'Completed');
  return `${heading('Good morning, Ava', 'A calm space to check in on your support journey.', true)}<div class="stats-grid">${statCard('Active requests', active.length, '◷')}${statCard('Completed', completed.length, '✓')}${statCard('All requests', own.length, '▤')}</div><section class="section-card"><div class="section-title"><div><h2>Your recent requests</h2><p>Only you can see your counselling requests.</p></div><button class="text-link" type="button" data-go="Counselling">View all&nbsp; →</button></div>${own.length ? own.slice().sort((a,b) => b.createdAt.localeCompare(a.createdAt)).map(studentRow).join('') : emptyState('No requests yet', 'When you submit a counselling request, it will appear here.')}</section><section class="urgent-card"><span class="urgent-icon" aria-hidden="true">✧</span><div class="urgent-copy"><strong>Need urgent help?</strong><p>If you may be in immediate danger, contact your local emergency services.</p></div><a href="https://findahelpline.com/" target="_blank" rel="noreferrer">Find support&nbsp; ↗</a></section><p class="notice">MindLink is not an emergency service.</p>`;
}
function renderCounselling() {
  const own = ownRequests(); const active = own.filter(r => !['Completed', 'Cancelled'].includes(r.status)); const completed = own.filter(r => r.status === 'Completed');
  const filtered = own.filter(r => filter === 'All' || (filter === 'Active' && !['Completed','Cancelled'].includes(r.status)) || (filter === 'Completed' && r.status === 'Completed'));
  return `${heading('Your counselling requests', 'Track each request and see updates from your counsellor.')}<div class="stats-grid">${statCard('Active', active.length, '◷')}${statCard('Completed', completed.length, '✓')}${statCard('Total requests', own.length, '▤')}</div><div class="controls-row"><div class="filter-tabs" role="tablist" aria-label="Filter requests">${['All','Active','Completed'].map(f => `<button type="button" role="tab" aria-selected="${filter === f}" class="filter-tab ${filter === f ? 'active' : ''}" data-filter="${f}">${f}</button>`).join('')}</div></div><div class="list-grid">${filtered.length ? filtered.slice().sort((a,b) => b.createdAt.localeCompare(a.createdAt)).map(requestCard).join('') : emptyState(filter === 'All' ? 'No requests yet' : `No ${filter.toLowerCase()} requests`, filter === 'All' ? 'Your requests will show here when you submit them.' : `You don’t have any ${filter.toLowerCase()} requests right now.`)}</div>`;
}
function renderCounsellor() {
  const active = requests.filter(r => !['Completed','Cancelled'].includes(r.status)); const completed = requests.filter(r => r.status === 'Completed');
  return `${heading('Welcome back, Maya', 'Here is the current picture of your counselling queue.', true)}<div class="stats-grid">${statCard('Active requests', active.length, '◷')}${statCard('Awaiting review', requests.filter(r => r.status === 'Pending').length, '○')}${statCard('Completed', completed.length, '✓')}</div><div class="section-title"><div><h2>Request queue</h2><p>Status changes are shared with the student immediately.</p></div></div><div class="queue-grid">${requests.slice().sort((a,b) => b.createdAt.localeCompare(a.createdAt)).map(r => `<article class="queue-card"><div class="student-info"><strong>${esc(r.studentName)}</strong><small>${esc(r.studentId)} · ${esc(r.id)}</small></div><div class="queue-topic">${esc(r.topic)}<br>${priorityBadge(r.priority)}</div><div>${badge(r.status)}</div><div class="queue-status"><label for="status-${esc(r.id)}">Update status</label><select id="status-${esc(r.id)}" data-status-id="${esc(r.id)}" aria-label="Update status for ${esc(r.studentName)} — ${esc(r.topic)}">${statuses.map(s => `<option ${r.status === s ? 'selected' : ''} value="${s}">${s}</option>`).join('')}</select></div></article>`).join('')}</div>`;
}
function renderQueue() { return renderCounsellor(); }
function renderPlaceholder() { return `${heading(page, 'This area will be available in a future MindLink update.')}<div class="empty-state"><div class="empty-illustration" aria-hidden="true">✳</div><h3>Coming soon</h3><p>For now, you can check your counselling requests and status updates.</p><button class="primary-button" type="button" data-go="${role === 'Student' ? 'Counselling' : 'Requests'}" style="margin-top:14px">${role === 'Student' ? 'View my requests' : 'Open request queue'}</button></div>`; }
function renderDetail() {
  const r = requests.find(item => item.id === selectedId && (role === 'Counsellor' || item.studentId === STUDENT.id));
  if (!r) { selectedId = null; page = role === 'Student' ? 'Counselling' : 'Requests'; return ''; }
  const stepIndex = ['Pending','Scheduled','Ongoing','Completed'].indexOf(r.status);
  const timeline = r.status === 'Cancelled' ? ['Pending','Scheduled','Ongoing','Completed'].map((s,i) => `<div class="timeline-step ${i === 0 ? 'done' : ''} ${i === 0 ? 'cancelled-step' : ''}"><div class="timeline-dot"></div>${s === 'Pending' ? 'Submitted' : s}</div>`).join('') : ['Pending','Scheduled','Ongoing','Completed'].map((s,i) => `<div class="timeline-step ${i < stepIndex ? 'done' : ''} ${i === stepIndex ? 'current' : ''}"><div class="timeline-dot"></div>${s === 'Pending' ? 'Submitted' : s}</div>`).join('');
  const cancelButton = role === 'Student' && r.status === 'Pending' ? `<button type="button" class="danger-button" data-cancel="${esc(r.id)}">Cancel request</button>` : '';
  return `<button class="back-button" type="button" data-go="${role === 'Student' ? 'Counselling' : 'Requests'}">←&nbsp; Back to ${role === 'Student' ? 'my requests' : 'request queue'}</button>${heading('Request details', `${esc(r.id)} · Submitted ${prettyDate(r.createdAt)}`)}<div class="detail-layout"><section class="detail-card"><div class="detail-status-top"><div><h2 class="detail-topic">${esc(r.topic)}</h2><p class="detail-sub">Counselling request · ${esc(r.id)}</p></div>${badge(r.status)}</div><div class="response-box"><span class="response-icon" aria-hidden="true">✳</span><div><strong>What happens next</strong><p>${responseCopy[r.status]}</p></div></div><div class="section-title"><div><h2>Progress</h2><p>Your request journey so far</p></div></div><div class="timeline" aria-label="Request progress: ${esc(r.status)}">${timeline}</div><h3 class="small-heading">Status history</h3><ul class="history-list">${r.history.slice().reverse().map(h => `<li><span>${badge(h.status)}</span><time class="history-date" datetime="${esc(h.date)}">${prettyDate(h.date)}</time></li>`).join('')}</ul><div class="detail-actions">${cancelButton}</div></section><aside class="detail-side"><section class="detail-card"><div class="side-row"><div class="side-label">Current status</div><div class="side-value">${esc(r.status)}</div></div><div class="side-row"><div class="side-label">Priority</div><div>${priorityBadge(r.priority)}</div></div><div class="side-row"><div class="side-label">Counsellor</div><div class="side-value">${esc(r.counsellor)}</div></div></section><section class="detail-card"><div class="side-label">A gentle reminder</div><div class="side-value" style="font-weight:500;line-height:1.6">Your wellbeing is personal. Take things one step at a time.</div></section></aside></div>`;
}
function render() {
  updateChrome();
  if (page === 'Request detail') { content.innerHTML = renderDetail(); if (!selectedId) return render(); }
  else if (role === 'Student' && page === 'Dashboard') content.innerHTML = renderDashboard();
  else if (role === 'Student' && page === 'Counselling') content.innerHTML = renderCounselling();
  else if (role === 'Counsellor' && page === 'Requests') content.innerHTML = renderQueue();
  else if (role === 'Counsellor' && page === 'Workspace') content.innerHTML = renderCounsellor();
  else content.innerHTML = renderPlaceholder();
  bindContent();
}
function bindContent() {
  content.querySelectorAll('[data-go]').forEach(el => el.addEventListener('click', () => { page = el.dataset.go; selectedId = null; render(); }));
  content.querySelectorAll('[data-filter]').forEach(el => el.addEventListener('click', () => { filter = el.dataset.filter; render(); }));
  content.querySelectorAll('[data-open]').forEach(el => el.addEventListener('click', () => { selectedId = el.dataset.open; page = 'Request detail'; render(); content.focus(); }));
  content.querySelectorAll('[data-status-id]').forEach(select => select.addEventListener('change', () => updateStatus(select.dataset.statusId, select.value)));
  content.querySelectorAll('[data-cancel]').forEach(button => button.addEventListener('click', () => updateStatus(button.dataset.cancel, 'Cancelled')));
}
// Students can cancel only while Pending. Counsellors may cancel any state; other updates follow the forward lifecycle.
function updateStatus(id, next) {
  const item = requests.find(r => r.id === id); if (!item || !statuses.includes(next)) return;
  if (role === 'Student' && (item.studentId !== STUDENT.id || item.status !== 'Pending' || next !== 'Cancelled')) { showToast('Only your pending requests can be cancelled.'); render(); return; }
  if (role === 'Counsellor' && next !== 'Cancelled') {
    const order = ['Pending', 'Scheduled', 'Ongoing', 'Completed'];
    if (order.indexOf(next) !== order.indexOf(item.status) + 1) { showToast('Choose the next status in the request lifecycle.'); render(); return; }
  }
  if (item.status === next) return;
  item.status = next; item.history.push({ status: next, date: new Date().toISOString().slice(0,10) }); saveRequests(); render(); showToast(`Status updated to ${next}.`);
}
function showToast(message) { const toast = document.getElementById('toast'); toast.textContent = message; toast.classList.add('show'); clearTimeout(toastTimer); toastTimer = setTimeout(() => toast.classList.remove('show'), 2600); }
function switchRole() { role = role === 'Student' ? 'Counsellor' : 'Student'; localStorage.setItem(ROLE_KEY, role); page = role === 'Student' ? 'Dashboard' : 'Workspace'; selectedId = null; render(); }
document.getElementById('role-switch').addEventListener('click', switchRole);
document.getElementById('mobile-role-switch').addEventListener('click', switchRole);
document.getElementById('theme-toggle').addEventListener('click', () => { const dark = document.body.classList.toggle('theme-dark'); localStorage.setItem('mindlink.theme.v1', dark ? 'dark' : 'light'); document.getElementById('theme-icon').textContent = dark ? '☀' : '☾'; });
if (localStorage.getItem('mindlink.theme.v1') === 'dark') { document.body.classList.add('theme-dark'); document.getElementById('theme-icon').textContent = '☀'; }
render();
