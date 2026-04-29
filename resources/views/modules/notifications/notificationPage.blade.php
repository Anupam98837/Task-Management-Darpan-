@extends('pages.users.admin.layout.structure')

@section('title', 'Notification History')

@section('content')
<div class="container-fluid">
  <!-- Page Header -->
  <div class="page-head d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-2">
      <i class="fa-solid fa-bell fs-4" style="color: var(--primary-color);"></i>
      <h5 class="mb-0 fw-bold">Notification History</h5>
    </div>
    <div class="actions d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" id="filterBtn">
        <i class="fa-solid fa-filter me-1"></i>Filters
      </button>
      <button class="btn btn-primary btn-sm" id="markAllReadBtn">
        <i class="fa-solid fa-check-double me-1"></i>Mark All Read
      </button>
    </div>
  </div>

  <!-- Filter Panel (collapsible) -->
  <div class="card mb-3" id="filterPanel" style="display: none;">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Status</label>
          <select class="form-select form-select-sm" id="filterStatus">
            <option value="">All</option>
            <option value="unread">Unread Only</option>
            <option value="read">Read Only</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Priority</label>
          <select class="form-select form-select-sm" id="filterPriority">
            <option value="">All Priorities</option>
            <option value="urgent">Urgent</option>
            <option value="high">High</option>
            <option value="normal">Normal</option>
            <option value="low">Low</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold" >Type</label>
          <select class="form-select form-select-sm" id="filterType">
            <option value="">All Types</option>
            <option value="general">General</option>
            <option value="system">System</option>
            <option value="alert">Alert</option>
            <option value="reminder">Reminder</option>
          </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button class="btn btn-primary btn-sm w-100" id="applyFiltersBtn">
            <i class="fa-solid fa-magnifying-glass me-1"></i>Apply Filters
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Notifications List -->
  <div class="card">
    <div class="card-body">
      <!-- Loading State -->
      <div id="loadingState" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="text-muted mt-2 mb-0">Loading notifications...</p>
      </div>

      <!-- Empty State -->
      <div id="emptyState" class="text-center py-5" style="display: none;">
        <i class="fa-regular fa-bell-slash fa-3x text-muted mb-3"></i>
        <h6 class="text-muted">No notifications found</h6>
        <p class="text-muted small mb-0">You're all caught up!</p>
      </div>

      <!-- Notifications Container -->
      <div id="notificationsList" style="display: none;">
        <!-- Notifications will be dynamically inserted here -->
      </div>

      <!-- Pagination -->
      <div id="paginationContainer" class="d-flex justify-content-between align-items-center mt-4" style="display: none;">
        <div class="text-muted small" id="paginationInfo"></div>
        <nav aria-label="Notification pagination">
          <ul class="pagination pagination-sm mb-0" id="paginationControls"></ul>
        </nav>
      </div>
    </div>
  </div>
</div>

<!-- Notification Detail Modal -->
<div class="modal fade" id="notifModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div class="d-flex align-items-center gap-2">
          <i id="notifModalIcon" class="fa-solid fa-bell me-1"></i>
          <h5 class="modal-title" id="notifModalTitle">Notification</h5>
          <span id="notifModalPriority" class="badge priority-badge ms-2">normal</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="d-flex flex-wrap gap-3 small text-muted mb-3">
          <span id="notifModalTime"><i class="fa-regular fa-clock me-1"></i>—</span>
          <span id="notifModalType" class="d-none">
            <i class="fa-solid fa-tag me-1"></i><span></span>
          </span>
          <span id="notifModalStatus"><i class="fa-solid fa-envelope-open me-1"></i>Unread</span>
        </div>

        <div id="notifModalMessage" class="notif-modal-message"></div>
      </div>

      <div class="modal-footer">
        <a id="notifModalOpenLink" class="btn btn-primary d-none" href="#" target="_blank" rel="noopener" style="display:none">
          <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Open Link
        </a>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection
@push('styles')
<style>
/* Notification Item Styles */
.notification-item {
  border: 1px solid var(--border-color, #e5e7eb);
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 12px;
  transition: all 0.2s ease;
  background: #fff;
}

html.theme-dark .notification-item {
  background: var(--light-color, #0f172a);
  border-color: var(--border-color, #273244);
}

.notification-item:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transform: translateY(-2px);
}

.notification-item.unread {
  background: rgba(79, 70, 229, 0.04);
  border-left: 4px solid var(--accent-color, #6366f1);
}

html.theme-dark .notification-item.unread {
  background: rgba(99, 102, 241, 0.08);
}

.notification-header {
  display: flex;
  align-items: start;
  gap: 12px;
  margin-bottom: 12px;
}

.notification-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  flex-shrink: 0;
}

.notification-icon.urgent { background: rgba(220, 38, 38, 0.1); color: #dc2626; }
.notification-icon.high { background: rgba(234, 179, 8, 0.1); color: #eab308; }
.notification-icon.normal { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
.notification-icon.low { background: rgba(107, 114, 128, 0.1); color: #6b7280; }

.notification-content { 
  flex: 1; 
  min-width: 0; 
  cursor: pointer; /* Make content clickable */
}

.notification-title {
  font-weight: 600;
  font-size: 15px;
  color: var(--text-color);
  margin-bottom: 4px;
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.notification-message {
  color: var(--muted-color, #6b7280);
  font-size: 14px;
  line-height: 1.5;
  margin-bottom: 8px;
}

.notification-meta {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
  font-size: 12px;
  color: var(--muted-color, #6b7280);
}

.notification-actions { 
  display: flex; 
  gap: 8px; 
  margin-top: 12px; 
}

.notification-actions .btn {
  padding: 0.25rem 0.5rem;
}

.badge.priority-badge {
  font-size: 10px;
  font-weight: 600;
  padding: 4px 8px;
  border-radius: 6px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.unread-indicator {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--accent-color, #6366f1);
  flex-shrink: 0;
}

/* Filter Panel Animation */
@keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
#filterPanel { animation: slideDown 0.3s ease; }

/* Modal tweaks */
.notif-modal-message { white-space: pre-wrap; font-size: 14px; }
html.theme-dark .modal-content {
  background: var(--light-color, #0f172a);
  color: var(--text-color, #e5e7eb);
}

/* Responsive Styles */
@media (max-width: 768px) {
  .notification-header { flex-direction: column; align-items: stretch; }
  .notification-actions { flex-direction: column; }
  .notification-actions .btn { width: 100%; }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const TOKEN = (sessionStorage.getItem('token') || localStorage.getItem('token') || '').trim();
  const role = (sessionStorage.getItem('role') || localStorage.getItem('type') || '').toLowerCase();
  const IS_ASSIGNEE = role === 'assignee';
  const IS_CLIENT_USER = role === 'client_user';
  const IS_READ_ONLY = IS_ASSIGNEE || IS_CLIENT_USER;

  console.log('🔔 Notification System - Role Based Access Control');
  console.log('Role:', role, 'IS_ASSIGNEE:', IS_ASSIGNEE);

  const loadingState = document.getElementById('loadingState');
  const emptyState = document.getElementById('emptyState');
  const notificationsList = document.getElementById('notificationsList');
  const paginationContainer = document.getElementById('paginationContainer');
  const paginationInfo = document.getElementById('paginationInfo');
  const paginationControls = document.getElementById('paginationControls');
  const filterPanel = document.getElementById('filterPanel');
  const filterBtn = document.getElementById('filterBtn');
  const applyFiltersBtn = document.getElementById('applyFiltersBtn');
  const markAllReadBtn = document.getElementById('markAllReadBtn');

  let currentPage = 1;
  let currentFilters = {};
  let notifCache = new Map();

  // Bootstrap modal instance
  const notifModalEl = document.getElementById('notifModal');
  const notifModal = (() => {
    try { return new bootstrap.Modal(notifModalEl); } catch { return null; }
  })();

  /* ===== Role-based UI hiding (following your existing pattern) ===== */
  function applyRoleVisibility(){
    if(!IS_READ_ONLY) return;
    
    // Hide delete buttons from existing notifications if any
    document.querySelectorAll('.notif-delete-btn').forEach(btn => {
      btn.style.display = 'none';
    });
    
    console.log('👤 Assignee mode: Delete buttons hidden');
  }

  /* ---------------- API Helpers ---------------- */
  async function jfetch(url, opt = {}) {
    const headers = { ...(opt.headers || {}) };
    if (TOKEN) headers.Authorization = 'Bearer ' + TOKEN;
    
    console.log(`🔄 API Call: ${opt.method || 'GET'} ${url}`);

    try {
      const res = await fetch(url, { ...opt, headers });
      console.log(`📡 Response: ${res.status} ${res.statusText}`);
      
      const text = await res.text();
      let json = {};
      
      try {
        json = text ? JSON.parse(text) : {};
      } catch (e) {
        console.error('❌ Failed to parse JSON response:', text);
        throw new Error(`Invalid JSON response`);
      }
      
      if (!res.ok) {
        console.error('❌ API Error:', {
          status: res.status,
          statusText: res.statusText,
          response: json
        });
        throw new Error(json?.message || json?.error || `HTTP ${res.status}`);
      }
      
      return json;
    } catch (error) {
      console.error('❌ Fetch error:', error);
      throw error;
    }
  }

  // Correct API endpoints based on your backend routes
  const API_BASE = '/api';

  async function apiGet(path) { 
    return jfetch(`${API_BASE}${path}`); 
  }

  async function apiPost(path, body) { 
    return jfetch(`${API_BASE}${path}`, { 
      method: 'POST', 
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body) 
    }); 
  }

  async function apiPatch(path, body) { 
    return jfetch(`${API_BASE}${path}`, { 
      method: 'PATCH', 
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body) 
    }); 
  }

  async function apiDelete(path) { 
    return jfetch(`${API_BASE}${path}`, { method: 'DELETE' }); 
  }

  /* ---------------- Core Functions ---------------- */
  function formatTimestamp(ts) {
    if (!ts) return 'Unknown';
    try {
      const d = new Date(ts);
      if (isNaN(d)) return 'Unknown';
      const now = new Date();
      const diff = now - d;
      const minutes = Math.floor(diff / 60000);
      const hours = Math.floor(diff / 3600000);
      const days = Math.floor(diff / 86400000);

      if (minutes < 1) return 'Just now';
      if (minutes < 60) return `${minutes}m ago`;
      if (hours < 24) return `${hours}h ago`;
      if (days < 7) return `${days}d ago`;
      return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    } catch {
      return 'Unknown';
    }
  }

  function isNotificationRead(receivers) {
    if (!Array.isArray(receivers)) return false;
    return receivers.some(r => Number(r.read) === 1);
  }

  function colorForPriority(p) {
    return ({ urgent: 'danger', high: 'warning', normal: 'primary', low: 'secondary' }[p] || 'secondary');
  }
  
  function iconForPriority(p) {
    return ({
      urgent: 'fa-solid fa-circle-exclamation',
      high: 'fa-solid fa-triangle-exclamation',
      normal: 'fa-solid fa-bell',
      low: 'fa-regular fa-bell'
    }[p] || 'fa-solid fa-bell');
  }

  function getPriorityBadge(priority) {
    const p = (priority || 'normal').toLowerCase();
    const color = colorForPriority(p);
    return `<span class="badge priority-badge bg-${color}">${p}</span>`;
  }

  function renderNotification(notif) {
    const isRead = isNotificationRead(notif.receivers || []);
    const priority = (notif.priority || 'normal').toLowerCase();
    const title = notif.title || 'Notification';
    const message = notif.message || '';
    const timestamp = formatTimestamp(notif.created_at || notif.updated_at);
    const link = notif.link_url || null;

    const iconClass = iconForPriority(priority);

    return `
      <div class="notification-item ${isRead ? 'read' : 'unread'}" data-id="${notif.id}">
        <div class="notification-header">
          <div class="notification-icon ${priority}">
            <i class="${iconClass}"></i>
          </div>
          <div class="notification-content" data-id="${notif.id}" ${link ? `data-link="${link}"` : ''}>
            <div class="notification-title">
              ${!isRead ? '<span class="unread-indicator"></span>' : ''}
              <span>${title}</span>
              ${getPriorityBadge(priority)}
            </div>
            <div class="notification-message">${message}</div>
            <div class="notification-meta">
              <span><i class="fa-regular fa-clock me-1"></i>${timestamp}</span>
              ${notif.type ? `<span><i class="fa-solid fa-tag me-1"></i>${notif.type}</span>` : ''}
              ${isRead ? '<span class="text-success"><i class="fa-solid fa-check me-1"></i>Read</span>' : ''}
              ${!IS_READ_ONLY ? '<span class="text-info"><i class="fa-solid fa-shield me-1"></i>Admin</span>' : ''}
            </div>
            <div class="notification-actions">
              ${!isRead ? `
                 <button class="btn btn-sm btn-outline-success notif-mark-read-btn" data-id="${notif.id}">
                  <i class="fa-solid fa-check"></i>Mark as Read
                </button>
              ` : ''}
              ${!IS_READ_ONLY ? `
                <button class="btn btn-sm btn-outline-danger notif-delete-btn" data-id="${notif.id}" title="Delete">
                  <i class="fa-solid fa-trash"></i>
                </button>
              ` : ''}
            </div>
          </div>
        </div>
      </div>
    `;
  }

  function applyReadStateToCard(id) {
    const card = document.querySelector(`.notification-item[data-id="${id}"]`);
    if (!card) return;

    // Update UI
    card.classList.remove('unread');
    card.classList.add('read');

    // Remove unread dot
    const dot = card.querySelector('.unread-indicator');
    if (dot) dot.remove();

    // Remove "Mark as Read" button
    const markReadBtn = card.querySelector('.notif-mark-read-btn');
    if (markReadBtn) markReadBtn.remove();

    // Update read status in meta
    const meta = card.querySelector('.notification-meta');
    if (meta) {
      const existingReadStatus = meta.querySelector('.text-success');
      if (!existingReadStatus) {
        const readSpan = document.createElement('span');
        readSpan.className = 'text-success';
        readSpan.innerHTML = '<i class="fa-solid fa-check me-1"></i>Read';
        meta.appendChild(readSpan);
      }
    }
    
    // Update cache
    const cachedNotif = notifCache.get(id);
    if (cachedNotif && Array.isArray(cachedNotif.receivers)) {
      cachedNotif.receivers = cachedNotif.receivers.map(r => ({
        ...r, 
        read: 1, 
        read_at: new Date().toISOString() 
      }));
      notifCache.set(id, cachedNotif);
    }
  }

  /* ---------------- Event Handlers ---------------- */
  function attachEventHandlers() {
    // Clickable notification content (title, message, etc.)
    document.querySelectorAll('.notification-content').forEach(content => {
      content.addEventListener('click', async (e) => {
        // Prevent triggering if clicking on action buttons
        if (e.target.closest('.notification-actions')) return;
        
        const id = Number(content.dataset.id);
        const link = content.dataset.link;

        let notif = notifCache.get(id);

        try {
          const detail = await apiGet(`/notifications/${id}`);
          if (detail && typeof detail === 'object') {
            notif = { ...(notif || {}), ...detail };
            notifCache.set(id, notif);
          }
        } catch (err) {
          console.warn('Detail fetch failed, using cache (if any).', err);
        }

        updateNotifModal(notif || {});
        if (typeof bootstrap !== 'undefined' && notifModal) notifModal.show();

        // Mark read if currently unread
        try {
          const currentlyRead = isNotificationRead((notif && notif.receivers) || []);
          if (!currentlyRead) {
            // Use the correct endpoint from your backend
            await apiPatch(`/notifications/${id}/read`, { read: true });
            applyReadStateToCard(id);

            if (currentFilters.status === 'unread') {
              const card = document.querySelector(`.notification-item[data-id="${id}"]`);
              if (card) {
                card.remove();
                if (!notificationsList.querySelector('.notification-item')) {
                  emptyState.style.display = 'block';
                  notificationsList.style.display = 'none';
                  paginationContainer.style.display = 'none';
                }
              }
            }
          }
        } catch (patchErr) {
          console.error('Failed to mark as read from modal view:', patchErr);
          Swal.fire('Error', 'Failed to mark as read: ' + patchErr.message, 'error');
        }
      });
    });

    // Mark as read buttons - FIXED with correct endpoint
    document.querySelectorAll('.notif-mark-read-btn').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        e.stopPropagation(); // Prevent triggering the content click event
        const id = Number(btn.dataset.id);
        
        console.log(`📝 Attempting to mark notification ${id} as read`);
        
        try {
          // Use the correct endpoint from your backend
          await apiPatch(`/notifications/${id}/read`, { read: true });
          console.log(`✅ Successfully marked notification ${id} as read`);
          
          applyReadStateToCard(id);

          if (currentFilters.status === 'unread') {
            const card = document.querySelector(`.notification-item[data-id="${id}"]`);
            if (card) {
              card.style.opacity = '0.5';
              setTimeout(() => {
                card.remove();
                if (!notificationsList.querySelector('.notification-item')) {
                  emptyState.style.display = 'block';
                  notificationsList.style.display = 'none';
                  paginationContainer.style.display = 'none';
                }
              }, 300);
            }
          }
          
          Swal.fire({ 
            icon: 'success', 
            title: 'Marked as read', 
            timer: 1000, 
            showConfirmButton: false 
          });
        } catch (err) {
          console.error('❌ Mark as read error:', err);
          Swal.fire('Error', 'Failed to mark as read: ' + err.message, 'error');
        }
      });
    });

    // Delete buttons (only visible for non-assignees)
    if (!IS_READ_ONLY) {
      document.querySelectorAll('.notif-delete-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
          e.preventDefault();
          e.stopPropagation(); // Prevent triggering the content click event
          const id = Number(btn.dataset.id);

          const result = await Swal.fire({
            title: 'Delete Notification?',
            text: 'This action cannot be undone',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'Yes, delete it'
          });

          if (result.isConfirmed) {
            try {
              await apiDelete(`/notifications/${id}`);
              await loadNotifications(currentPage);
              Swal.fire({ icon: 'success', title: 'Deleted', timer: 1500, showConfirmButton: false });
            } catch (err) {
              console.error(err);
              Swal.fire('Error', 'Failed to delete notification', 'error');
            }
          }
        });
      });
    }

    // Pagination
    document.querySelectorAll('#paginationControls .page-link').forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        const page = Number(link.dataset.page);
        if (page && page !== currentPage) {
          loadNotifications(page);
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }
      });
    });
  }

  // Mark all as read - FIXED with correct endpoint
  markAllReadBtn.addEventListener('click', async () => {
    const result = await Swal.fire({
      title: 'Mark All as Read?',
      text: 'This will mark all your notifications as read',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Yes, mark all'
    });

    if (result.isConfirmed) {
      try {
        console.log('📝 Attempting to mark all notifications as read');
        // Use the correct endpoint from your backend
        await apiPost('/notifications/mark-all-read', {});
        console.log('✅ Successfully marked all notifications as read');
        
        // Update ALL visible notifications immediately
        document.querySelectorAll('.notification-item').forEach(card => {
          const id = Number(card.dataset.id);
          applyReadStateToCard(id);
        });

        // If in unread filter mode, show empty state
        if (currentFilters.status === 'unread') {
          emptyState.style.display = 'block';
          notificationsList.style.display = 'none';
          paginationContainer.style.display = 'none';
        }
        
        Swal.fire({ 
          icon: 'success', 
          title: 'All marked as read', 
          timer: 1500, 
          showConfirmButton: false 
        });
      } catch (err) {
        console.error('❌ Mark all read error:', err);
        Swal.fire('Error', 'Failed to mark all as read: ' + err.message, 'error');
      }
    }
  });

  /* ---------------- Notification Loading ---------------- */
  async function loadNotifications(page = 1) {
    try {
      loadingState.style.display = 'block';
      emptyState.style.display = 'none';
      notificationsList.style.display = 'none';
      paginationContainer.style.display = 'none';

      const qp = new URLSearchParams();
      qp.set('page', String(page));
      qp.set('limit', '15');

      // Apply filters
      if (currentFilters.status === 'unread') qp.set('unread', '1');
      if (currentFilters.priority) qp.set('priority', currentFilters.priority);
      if (currentFilters.type) qp.set('type', currentFilters.type);

      // Use the correct endpoint from your backend
      const data = await apiGet(`/notifications/my?${qp.toString()}`);
      const items = Array.isArray(data?.data) ? data.data : Array.isArray(data) ? data : [];

      console.log('Loaded notifications:', items);

      // Fill cache for this page
      notifCache = new Map(items.map(n => [Number(n.id), n]));

      // Client-side "read only" filter
      let list = items;
      if (currentFilters.status === 'read') {
        list = items.filter(n => isNotificationRead(n.receivers));
      }

      loadingState.style.display = 'none';

      if (list.length === 0) {
        emptyState.style.display = 'block';
        return;
      }

      notificationsList.innerHTML = list.map(renderNotification).join('');
      notificationsList.style.display = 'block';

      if (data.pagination) {
        renderPagination(data.pagination);
        paginationContainer.style.display = 'flex';
      }

      currentPage = page;
      attachEventHandlers();
      applyRoleVisibility(); // Apply role-based visibility after loading
    } catch (err) {
      console.error('Failed to load notifications:', err);
      loadingState.style.display = 'none';
      Swal.fire('Error', 'Failed to load notifications: ' + err.message, 'error');
    }
  }

  function renderPagination(pagination) {
    const { current_page, last_page, total, per_page } = pagination;
    const start = (current_page - 1) * per_page + 1;
    const end = Math.min(current_page * per_page, total);

    paginationInfo.textContent = `Showing ${start}-${end} of ${total} notifications`;

    let html = '';
    html += `
      <li class="page-item ${current_page === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" data-page="${current_page - 1}">
          <i class="fa-solid fa-chevron-left"></i>
        </a>
      </li>
    `;

    const maxVisible = 5;
    let startPage = Math.max(1, current_page - Math.floor(maxVisible / 2));
    let endPage = Math.min(last_page, startPage + maxVisible - 1);
    if (endPage - startPage < maxVisible - 1) {
      startPage = Math.max(1, endPage - maxVisible + 1);
    }

    if (startPage > 1) {
      html += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
      if (startPage > 2) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
    }

    for (let i = startPage; i <= endPage; i++) {
      html += `
        <li class="page-item ${i === current_page ? 'active' : ''}">
          <a class="page-link" href="#" data-page="${i}">${i}</a>
        </li>
      `;
    }

    if (endPage < last_page) {
      if (endPage < last_page - 1) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
      html += `<li class="page-item"><a class="page-link" href="#" data-page="${last_page}">${last_page}</a></li>`;
    }

    html += `
      <li class="page-item ${current_page === last_page ? 'disabled' : ''}">
        <a class="page-link" href="#" data-page="${current_page + 1}">
          <i class="fa-solid fa-chevron-right"></i>
        </a>
      </li>
    `;

    paginationControls.innerHTML = html;
  }

  function updateNotifModal(notif) {
    const priority = (notif?.priority || 'normal').toLowerCase();
    const isRead = isNotificationRead(notif?.receivers || []);

    const titleEl = document.getElementById('notifModalTitle');
    const prBadge = document.getElementById('notifModalPriority');
    const iconEl  = document.getElementById('notifModalIcon');

    titleEl.textContent = notif?.title || 'Notification';
    prBadge.className = `badge priority-badge bg-${colorForPriority(priority)}`;
    prBadge.textContent = priority;
    iconEl.className = `${iconForPriority(priority)} me-1`;

    const timeEl = document.getElementById('notifModalTime');
    timeEl.innerHTML = `<i class="fa-regular fa-clock me-1"></i>${formatTimestamp(notif?.created_at || notif?.updated_at)}`;

    const typeWrap = document.getElementById('notifModalType');
    const typeSpan = typeWrap.querySelector('span');
    if (notif?.type) {
      typeSpan.textContent = notif.type;
      typeWrap.classList.remove('d-none');
    } else {
      typeWrap.classList.add('d-none');
    }

    const statusEl = document.getElementById('notifModalStatus');
    statusEl.innerHTML = isRead
      ? '<i class="fa-solid fa-check me-1 text-success"></i>Read'
      : '<i class="fa-solid fa-envelope-open me-1 text-primary"></i>Unread';

    const msgEl = document.getElementById('notifModalMessage');
    msgEl.textContent = notif?.message || '';

    const openLinkBtn = document.getElementById('notifModalOpenLink');
    if (notif?.link_url) {
      openLinkBtn.href = notif.link_url;
      openLinkBtn.classList.remove('d-none');
    } else {
      openLinkBtn.classList.add('d-none');
      openLinkBtn.removeAttribute('href');
    }
  }

  // Filter handlers
  filterBtn.addEventListener('click', () => {
    filterPanel.style.display = filterPanel.style.display === 'none' ? 'block' : 'none';
  });

  applyFiltersBtn.addEventListener('click', () => {
    currentFilters = {
      status: document.getElementById('filterStatus').value,
      priority: document.getElementById('filterPriority').value,
      type: document.getElementById('filterType').value
    };
    loadNotifications(1);
  });

  // Apply role visibility immediately
  applyRoleVisibility();

  // Initial load
  await loadNotifications(1);
});
</script>
@endpush
