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
const icons = { Dashboard: '⌂', Learn: '◉', Wellbeing: '♡', Counselling: '✳', Notifications: '○', Workspace: '⌂', Requests: '▤', Overview: '⌂', Users: '●', Content: '◇', Schedules: '◷', Reports: '▥' };
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
function navItems() { return role === 'Student' ? ['Dashboard', 'Learn', 'Wellbeing', 'Counselling', 'Notifications'] : role === 'Counsellor' ? ['Workspace', 'Requests'] : ['Overview', 'Users', 'Content', 'Schedules', 'Reports']; }
function renderNav() {
  document.getElementById('main-nav').innerHTML = navItems().map(item => `<button type="button" class="nav-item ${page === item || (item === 'Counselling' && ['Request detail','Request support'].includes(page)) ? 'active' : ''}" data-page="${item}"><span class="nav-icon" aria-hidden="true">${icons[item]}</span><span>${item}</span></button>`).join('');
  document.querySelectorAll('.nav-item').forEach(button => button.addEventListener('click', () => { page = button.dataset.page; selectedId = null; render(); }));
}
function updateChrome() {
  const display = role === 'Counsellor' ? { name: 'Maya Thompson', account: 'Counsellor account', avatar: 'M' } : role === 'Admin' ? { name: 'Admin', account: 'Admin account', avatar: 'A' } : { name: STUDENT.name, account: 'Student account', avatar: 'A' };
  document.getElementById('portal-label').textContent = `${role} portal`;
  document.getElementById('page-crumb').textContent = page;
  document.getElementById('profile-name').textContent = display.name;
  document.getElementById('profile-role').textContent = display.account;
  document.getElementById('profile-avatar').textContent = display.avatar;
  renderNav();
}
function heading(title, subtitle, date = false) { return `<div class="page-heading"><div><div class="eyebrow">${role === 'Student' ? 'YOUR WELLBEING, YOUR PACE' : role === 'Admin' ? 'ADMINISTRATION OVERVIEW' : 'COUNSELLOR WORKSPACE'}</div><h1>${title}</h1><p>${subtitle}</p></div>${date ? `<div class="date-label">${new Date().toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' })}</div>` : ''}</div>`; }
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
  return `${heading('Your counselling requests', 'Track each request and see updates from your counsellor.')}<div class="request-hero"><div><h2>Request private support</h2><p>Choose a time that feels right. You can review everything before sending.</p></div><button type="button" class="primary-button" data-go="Request support">New request&nbsp; +</button></div><div class="stats-grid">${statCard('Active', active.length, '◷')}${statCard('Completed', completed.length, '✓')}${statCard('Total requests', own.length, '▤')}</div><div class="controls-row"><div class="filter-tabs" role="tablist" aria-label="Filter requests">${['All','Active','Completed'].map(f => `<button type="button" role="tab" aria-selected="${filter === f}" class="filter-tab ${filter === f ? 'active' : ''}" data-filter="${f}">${f}</button>`).join('')}</div></div><div class="list-grid">${filtered.length ? filtered.slice().sort((a,b) => b.createdAt.localeCompare(a.createdAt)).map(requestCard).join('') : emptyState(filter === 'All' ? 'No requests yet' : `No ${filter.toLowerCase()} requests`, filter === 'All' ? 'Your requests will show here when you submit them.' : `You don’t have any ${filter.toLowerCase()} requests right now.`)}</div>`;
}
function renderRequestSupport() {
  return `${heading('Counselling request', 'Complete the few steps below to request private support.')}<div class="stepper"><span class="current">1 <small>Concern</small></span><i></i><span>2 <small>Consent</small></span><i></i><span>3 <small>Schedule</small></span><i></i><span>4 <small>Review</small></span></div><div class="request-form-grid"><section class="detail-card"><h2 class="form-title">Choose an available time</h2><p class="form-subtitle">Your counsellor will confirm the session after reviewing your request.</p><label class="field-label">Counsellor<select><option>Maya · Student wellbeing</option><option>Sam · Student wellbeing</option></select></label><p class="field-label">Wednesday, 21 October 2026</p><div class="slot-grid">${['09:00','10:20','12:30','15:00'].map((slot,index) => `<button type="button" class="slot-button ${index === 1 ? 'selected' : ''}" data-slot="${slot}">${slot}</button>`).join('')}</div><button type="button" class="secondary-button">In person</button><button type="button" class="primary-button form-submit" data-submit-request>Continue</button></section><aside class="detail-card request-summary"><h2 class="form-title">Your request</h2><div class="summary-row"><span>Concern</span><strong>Academic pressure and anxiety</strong></div><div class="summary-row"><span>Consent</span><strong>Accepted</strong></div><div class="summary-row"><span>Scheduled</span><strong>21 October 2026, 10:20</strong></div><div class="summary-note">Private - only visible to your counsellor.</div></aside></div>`;
}
function renderLearn() {
  const cards = [
    ['ANXIETY', 'Understanding anxious thoughts', 'How small, safe steps can help you notice anxious thoughts without judgement.', '☼'],
    ['ACADEMIC STRESS', 'Planning during a difficult week', 'A practical way to break study pressure into smaller, more manageable actions.', '◇'],
    ['SLEEP', 'A practical sleep routine', 'Simple evening habits that support a calmer and more consistent night.', '♡']
  ];
  return `${heading('Mental health literacy', 'Find clear, practical information for your everyday wellbeing.')}<div class="learn-intro"><div><h2>Find clear, practical information</h2><p>Articles are educational and do not replace professional assessment.</p></div><div class="search-row"><input aria-label="Search articles" placeholder="Search articles, topics, or keywords" data-search-input><button class="primary-button" type="button" data-search>Search</button></div></div><div class="topic-pills"><span class="active">All topics</span><span>Anxiety</span><span>Sleep</span><span>Academic stress</span><span>Relationships</span></div><div class="learn-grid">${cards.map(([tag,title,copy,icon]) => `<article class="learn-card"><div class="learn-art">${icon}</div><small>${tag}</small><h3>${title}</h3><p>${copy}</p><button class="text-link" type="button" data-learn-title="${esc(title)}">Read article&nbsp; →</button></article>`).join('')}</div>`;
}
function renderWellbeing() {
  const choices = ['Not at all', 'Somewhat', 'More than half the days', 'Nearly every day'];
  return `${heading('Wellbeing check in', 'A short reflection to help you notice how you have been feeling.')}<div class="wellbeing-card"><div class="progress-meta"><span>A short reflection</span><span>1 of 5</span></div><div class="progress-track"><span style="width:20%"></span></div><div class="question-kicker">OVER THE PAST TWO WEEKS</div><h2>How often have you found it difficult to concentrate on study or daily activities?</h2><div class="choice-list">${choices.map((choice,index) => `<button type="button" class="choice-button ${index === 1 ? 'selected' : ''}" data-choice="${esc(choice)}"><span class="choice-dot"></span>${choice}</button>`).join('')}</div><div class="wellbeing-actions"><button type="button" class="secondary-button" data-go="Dashboard">Back</button><button type="button" class="primary-button" data-next-checkin>Continue</button></div></div>`;
}
function renderNotifications() {
  return `${heading('Notifications', 'Updates from your wellbeing and support journey.')}<div class="notification-list"><article class="notification-card unread"><span class="notification-icon">✳</span><div><strong>Your counselling request is ongoing</strong><p>Maya Thompson shared an update on your request ML-2048.</p><small>Today · 9:40 AM</small></div></article><article class="notification-card"><span class="notification-icon">✓</span><div><strong>Take a moment to check in</strong><p>Your weekly wellbeing reflection is ready whenever you are.</p><small>Yesterday</small></div></article></div>`;
}
function renderAdmin() {
  return `${heading('Administrative dashboard', 'Monitor content, accounts, and aggregate service activity.', true)}<div class="report-pill">Reporting period · October</div><div class="stats-grid admin-stats">${statCard('Registered students', '438', '♙')}${statCard('Counselling requests', '64', '▤')}${statCard('Sessions completed', '41', '✓')}</div><div class="admin-grid"><section class="detail-card chart-card"><h2 class="form-title">Weekly request activity</h2><p class="form-subtitle">Aggregate counts; no personal counselling notes are shown.</p><div class="bar-chart">${[6,12,10,19,16].map((v,i) => `<div class="bar-col"><span style="height:${v*5}px"></span><small>W${i+1}</small></div>`).join('')}</div></section><section class="detail-card"><h2 class="form-title">Content status</h2><div class="admin-list"><div>Published resources <b>24</b></div><div>Draft resources <b>5</b></div><div>Awaiting review <b>2</b></div></div><div class="policy-card"><strong>PRIVACY BOUNDARY</strong><p>Administration sees service data, not counselling notes.</p></div></section></div>`;
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
  else if (role === 'Student' && page === 'Request support') content.innerHTML = renderRequestSupport();
  else if (role === 'Student' && page === 'Learn') content.innerHTML = renderLearn();
  else if (role === 'Student' && page === 'Wellbeing') content.innerHTML = renderWellbeing();
  else if (role === 'Student' && page === 'Notifications') content.innerHTML = renderNotifications();
  else if (role === 'Counsellor' && page === 'Requests') content.innerHTML = renderQueue();
  else if (role === 'Counsellor' && page === 'Workspace') content.innerHTML = renderCounsellor();
  else if (role === 'Admin' && page === 'Overview') content.innerHTML = renderAdmin();
  else content.innerHTML = renderPlaceholder();
  bindContent();
}
function bindContent() {
  content.querySelectorAll('[data-go]').forEach(el => el.addEventListener('click', () => { page = el.dataset.go; selectedId = null; render(); }));
  content.querySelectorAll('[data-filter]').forEach(el => el.addEventListener('click', () => { filter = el.dataset.filter; render(); }));
  content.querySelectorAll('[data-open]').forEach(el => el.addEventListener('click', () => { selectedId = el.dataset.open; page = 'Request detail'; render(); content.focus(); }));
  content.querySelectorAll('[data-status-id]').forEach(select => select.addEventListener('change', () => updateStatus(select.dataset.statusId, select.value)));
  content.querySelectorAll('[data-cancel]').forEach(button => button.addEventListener('click', () => updateStatus(button.dataset.cancel, 'Cancelled')));
  content.querySelectorAll('[data-choice]').forEach(button => button.addEventListener('click', () => { content.querySelectorAll('[data-choice]').forEach(item => item.classList.remove('selected')); button.classList.add('selected'); }));
  content.querySelectorAll('[data-slot]').forEach(button => button.addEventListener('click', () => { content.querySelectorAll('[data-slot]').forEach(item => item.classList.remove('selected')); button.classList.add('selected'); }));
  content.querySelectorAll('[data-submit-request]').forEach(button => button.addEventListener('click', () => { showToast('Your counselling request is ready for review.'); page = 'Counselling'; render(); }));
  content.querySelectorAll('[data-next-checkin]').forEach(button => button.addEventListener('click', () => { showToast('Your reflection has been saved.'); page = 'Dashboard'; render(); }));
  content.querySelectorAll('[data-learn-title]').forEach(button => button.addEventListener('click', () => showToast(`${button.dataset.learnTitle} opened.`)));
  content.querySelectorAll('[data-search]').forEach(button => button.addEventListener('click', () => showToast('Showing the most relevant articles.')));
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
function switchRole() { role = role === 'Student' ? 'Counsellor' : role === 'Counsellor' ? 'Admin' : 'Student'; localStorage.setItem(ROLE_KEY, role); page = role === 'Student' ? 'Dashboard' : role === 'Counsellor' ? 'Workspace' : 'Overview'; selectedId = null; render(); }
document.getElementById('role-switch').addEventListener('click', switchRole);
document.getElementById('mobile-role-switch').addEventListener('click', switchRole);
document.getElementById('theme-toggle').addEventListener('click', () => { const dark = document.body.classList.toggle('theme-dark'); localStorage.setItem('mindlink.theme.v1', dark ? 'dark' : 'light'); document.getElementById('theme-icon').textContent = dark ? '☀' : '☾'; });
if (localStorage.getItem('mindlink.theme.v1') === 'dark') { document.body.classList.add('theme-dark'); document.getElementById('theme-icon').textContent = '☀'; }
render();
