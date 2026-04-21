<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow — Todo Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'DM Sans', sans-serif; }
        .font-serif { font-family: 'Instrument Serif', serif; }

        body { background: #0f0f0f; color: #e8e4dc; }

        .card {
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 16px;
        }

        .task-item {
            background: #1e1e1e;
            border: 1px solid #2a2a2a;
            border-radius: 12px;
            transition: all 0.2s ease;
        }
        .task-item:hover { border-color: #3a3a3a; background: #222; transform: translateY(-1px); }
        .task-item.completed { opacity: 0.5; }

        .pill {
            border-radius: 999px;
            padding: 2px 10px;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.03em;
        }

        .priority-high { background: #3d1a1a; color: #f87171; border: 1px solid #5a2020; }
        .priority-medium { background: #2d2a10; color: #fbbf24; border: 1px solid #4a4010; }
        .priority-low { background: #0f2d1e; color: #34d399; border: 1px solid #1a4a30; }

        .status-pending { background: #1e2030; color: #818cf8; border: 1px solid #2a2d4a; }
        .status-in_progress { background: #1e2d30; color: #22d3ee; border: 1px solid #1a3a4a; }
        .status-completed { background: #0f2d1e; color: #34d399; border: 1px solid #1a4a30; }

        .btn-primary {
            background: #e8e4dc;
            color: #0f0f0f;
            border-radius: 10px;
            padding: 8px 18px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.15s ease;
            border: none;
            cursor: pointer;
        }
        .btn-primary:hover { background: #fff; transform: translateY(-1px); }

        .btn-ghost {
            background: transparent;
            color: #888;
            border: 1px solid #2a2a2a;
            border-radius: 10px;
            padding: 7px 16px;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.15s ease;
            cursor: pointer;
        }
        .btn-ghost:hover { color: #e8e4dc; border-color: #444; background: #252525; }
        .btn-ghost.active { color: #e8e4dc; border-color: #555; background: #252525; }

        input, select, textarea {
            background: #141414;
            border: 1px solid #2a2a2a;
            border-radius: 10px;
            color: #e8e4dc;
            padding: 10px 14px;
            font-size: 14px;
            transition: border-color 0.15s;
            width: 100%;
            font-family: 'DM Sans', sans-serif;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #555;
        }
        input::placeholder, textarea::placeholder { color: #444; }
        select option { background: #1a1a1a; }

        .modal-bg {
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(4px);
            position: fixed; inset: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal {
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 20px;
            width: 100%;
            max-width: 520px;
            padding: 28px;
            position: relative;
        }

        .stat-card {
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 14px;
            padding: 18px 20px;
        }

        .checkbox-custom {
            width: 20px; height: 20px;
            border: 2px solid #3a3a3a;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.15s;
        }
        .checkbox-custom:hover { border-color: #888; }
        .checkbox-custom.checked { background: #34d399; border-color: #34d399; }

        .sidebar-link {
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            color: #888;
            cursor: pointer;
            transition: all 0.15s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .sidebar-link:hover, .sidebar-link.active { background: #252525; color: #e8e4dc; }

        .toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #1e1e1e;
            border: 1px solid #3a3a3a;
            border-radius: 12px;
            padding: 14px 20px;
            font-size: 13px;
            z-index: 100;
            transform: translateY(80px);
            opacity: 0;
            transition: all 0.3s ease;
            max-width: 300px;
        }
        .toast.show { transform: translateY(0); opacity: 1; }
        .toast.success { border-color: #1a4a30; color: #34d399; }
        .toast.error { border-color: #5a2020; color: #f87171; }

        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #333; border-radius: 4px; }

        .dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
        .fade-in { animation: fadeIn 0.2s ease forwards; }
    </style>
</head>
<body class="min-h-screen">

<!-- Toast -->
<div id="toast" class="toast"></div>

<!-- Task Modal -->
<div id="taskModal" class="modal-bg hidden">
    <div class="modal fade-in">
        <div class="flex items-center justify-between mb-6">
            <h2 class="font-serif text-xl" id="modalTitle">New Task</h2>
            <button onclick="closeModal()" class="text-gray-600 hover:text-gray-300 text-xl leading-none">&times;</button>
        </div>
        <form id="taskForm" class="space-y-4">
            <input type="hidden" id="taskId">
            <div>
                <label class="block text-xs text-gray-500 mb-1.5 uppercase tracking-wider">Title *</label>
                <input type="text" id="taskTitle" placeholder="What needs to be done?" required>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1.5 uppercase tracking-wider">Description</label>
                <textarea id="taskDesc" rows="2" placeholder="Add details..."></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5 uppercase tracking-wider">Priority</label>
                    <select id="taskPriority">
                        <option value="low">🟢 Low</option>
                        <option value="medium" selected>🟡 Medium</option>
                        <option value="high">🔴 High</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5 uppercase tracking-wider">Status</label>
                    <select id="taskStatus">
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5 uppercase tracking-wider">Category</label>
                    <select id="taskCategory">
                        <option value="">No Category</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5 uppercase tracking-wider">Due Date</label>
                    <input type="date" id="taskDue">
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1">Save Task</button>
                <button type="button" onclick="closeModal()" class="btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Layout -->
<div class="flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-56 border-r border-[#1e1e1e] flex-shrink-0 flex flex-col p-4 overflow-y-auto">
        <div class="mb-8 mt-2">
            <div class="font-serif text-2xl tracking-tight">TaskFlow</div>
            <div class="text-xs text-gray-600 mt-0.5">Stay organized</div>
        </div>

        <div class="space-y-1 mb-6">
            <div class="sidebar-link active" onclick="setFilter('status', '')" id="filter-all">
                <span>◈</span> All Tasks
            </div>
            <div class="sidebar-link" onclick="setFilter('status', 'pending')" id="filter-pending">
                <span>○</span> Pending
            </div>
            <div class="sidebar-link" onclick="setFilter('status', 'in_progress')" id="filter-in_progress">
                <span>◔</span> In Progress
            </div>
            <div class="sidebar-link" onclick="setFilter('status', 'completed')" id="filter-completed">
                <span>●</span> Completed
            </div>
        </div>

        <div class="mb-2">
            <div class="text-xs text-gray-600 uppercase tracking-wider px-3 mb-2">Categories</div>
            <div id="sidebarCategories" class="space-y-1"></div>
            <button onclick="openCategoryModal()" class="sidebar-link w-full mt-1 text-left text-gray-600">
                <span>+</span> Add Category
            </button>
        </div>

        <div class="mt-auto pt-4 border-t border-[#1e1e1e]">
            <div class="text-xs text-gray-600 px-3">
                <div id="sidebarStats" class="space-y-1"></div>
            </div>
        </div>
    </aside>

    <!-- Main -->
    <main class="flex-1 overflow-y-auto">
        <div class="max-w-3xl mx-auto px-6 py-8">

            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="font-serif text-3xl" id="pageTitle">All Tasks</h1>
                    <p class="text-gray-600 text-sm mt-1" id="pageSubtitle">Loading...</p>
                </div>
                <button onclick="openModal()" class="btn-primary">+ New Task</button>
            </div>

            <!-- Stats Row -->
            <div class="grid grid-cols-4 gap-3 mb-6" id="statsRow">
                <div class="stat-card">
                    <div class="text-2xl font-semibold" id="statTotal">—</div>
                    <div class="text-xs text-gray-600 mt-0.5">Total</div>
                </div>
                <div class="stat-card">
                    <div class="text-2xl font-semibold text-indigo-400" id="statPending">—</div>
                    <div class="text-xs text-gray-600 mt-0.5">Pending</div>
                </div>
                <div class="stat-card">
                    <div class="text-2xl font-semibold text-cyan-400" id="statProgress">—</div>
                    <div class="text-xs text-gray-600 mt-0.5">In Progress</div>
                </div>
                <div class="stat-card">
                    <div class="text-2xl font-semibold text-emerald-400" id="statDone">—</div>
                    <div class="text-xs text-gray-600 mt-0.5">Done</div>
                </div>
            </div>

            <!-- Filters & Search -->
            <div class="flex items-center gap-3 mb-5">
                <div class="flex-1">
                    <input type="text" id="searchInput" placeholder="Search tasks..." oninput="debounceSearch()" class="!py-2">
                </div>
                <select id="priorityFilter" onchange="applyFilters()" class="!w-36 !py-2">
                    <option value="">All Priority</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>

            <!-- Task List -->
            <div id="taskList" class="space-y-2">
                <div class="text-center text-gray-600 py-16">Loading tasks...</div>
            </div>

        </div>
    </main>
</div>

<!-- Category Modal -->
<div id="categoryModal" class="modal-bg hidden">
    <div class="modal fade-in" style="max-width: 380px;">
        <div class="flex items-center justify-between mb-5">
            <h2 class="font-serif text-xl">New Category</h2>
            <button onclick="closeCategoryModal()" class="text-gray-600 hover:text-gray-300 text-xl">&times;</button>
        </div>
        <div class="space-y-3">
            <div>
                <label class="block text-xs text-gray-500 mb-1.5 uppercase tracking-wider">Name</label>
                <input type="text" id="catName" placeholder="Category name">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1.5 uppercase tracking-wider">Color</label>
                <div class="flex gap-2 flex-wrap">
                    <input type="color" id="catColor" value="#6366f1" class="!w-10 !h-10 !p-1 cursor-pointer">
                    <div class="flex gap-2 flex-wrap">
                        <?php
                        $presets = ['#6366f1','#f59e0b','#3b82f6','#10b981','#ef4444','#ec4899','#8b5cf6','#f97316'];
                        foreach ($presets as $c):
                        ?>
                        <div onclick="document.getElementById('catColor').value='<?= $c ?>'" 
                             class="w-8 h-8 rounded-lg cursor-pointer hover:scale-110 transition-transform"
                             style="background: <?= $c ?>"></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="flex gap-3 pt-1">
                <button onclick="createCategory()" class="btn-primary flex-1">Create</button>
                <button onclick="closeCategoryModal()" class="btn-ghost">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
// State
let tasks = [];
let categories = [];
let currentFilters = { status: '', priority: '', category_id: '', search: '' };
let searchTimer = null;

// ─── API ───────────────────────────────────────────────────────────────────────

async function api(endpoint, method = 'GET', body = null) {
    const opts = { method, headers: { 'Content-Type': 'application/json' } };
    if (body) opts.body = JSON.stringify(body);
    const res = await fetch(endpoint, opts);
    return res.json();
}

function buildQuery(base, params) {
    const q = Object.entries(params).filter(([,v]) => v).map(([k,v]) => `${k}=${encodeURIComponent(v)}`).join('&');
    return q ? `${base}?${q}` : base;
}

// ─── Data Loading ──────────────────────────────────────────────────────────────

async function loadTasks() {
    const url = buildQuery('tasks.php', currentFilters);
    const data = await api(url);
    if (data.success) { tasks = data.tasks; renderTasks(); }
}

async function loadCategories() {
    const data = await api('categories.php');
    if (data.success) { categories = data.categories; renderCategories(); populateCategorySelect(); }
}

async function loadStats() {
    const data = await api('tasks.php?action=stats');
    if (data.success) {
        const s = data.stats;
        document.getElementById('statTotal').textContent = s.total;
        document.getElementById('statPending').textContent = s.by_status.pending;
        document.getElementById('statProgress').textContent = s.by_status.in_progress;
        document.getElementById('statDone').textContent = s.by_status.completed;
        const subtitle = [];
        if (s.overdue > 0) subtitle.push(`<span class="text-red-400">${s.overdue} overdue</span>`);
        subtitle.push(`${s.by_status.completed} completed`);
        document.getElementById('pageSubtitle').innerHTML = subtitle.join(' · ');

        // Sidebar stats
        document.getElementById('sidebarStats').innerHTML = `
            <div class="flex justify-between"><span>Total</span><span class="text-gray-400">${s.total}</span></div>
            <div class="flex justify-between"><span>Overdue</span><span class="text-red-500">${s.overdue}</span></div>
        `;
    }
}

// ─── Rendering ─────────────────────────────────────────────────────────────────

function renderTasks() {
    const el = document.getElementById('taskList');
    if (!tasks.length) {
        el.innerHTML = `<div class="text-center py-16 text-gray-600">
            <div class="text-4xl mb-3">◈</div>
            <div class="text-sm">No tasks found</div>
            <button onclick="openModal()" class="btn-ghost mt-4 text-xs">Add your first task</button>
        </div>`;
        return;
    }
    el.innerHTML = tasks.map(task => renderTask(task)).join('');
}

function renderTask(t) {
    const isCompleted = t.status === 'completed';
    const overdue = t.due_date && !isCompleted && new Date(t.due_date) < new Date(new Date().toDateString());
    const dueDateStr = t.due_date ? new Date(t.due_date + 'T00:00:00').toLocaleDateString('en', { month: 'short', day: 'numeric' }) : '';

    return `<div class="task-item p-4 flex gap-3 fade-in ${isCompleted ? 'completed' : ''}" id="task-${t.id}">
        <div class="checkbox-custom ${isCompleted ? 'checked' : ''} mt-0.5" onclick="toggleTask(${t.id}, '${isCompleted ? 'pending' : 'completed'}')">
            ${isCompleted ? '<svg width="11" height="8" viewBox="0 0 11 8" fill="none"><path d="M1 4L4 7L10 1" stroke="#0f0f0f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>' : ''}
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
                <div class="font-medium text-sm ${isCompleted ? 'line-through text-gray-600' : ''}">${escHtml(t.title)}</div>
                <div class="flex gap-1.5 flex-shrink-0">
                    <span class="pill priority-${t.priority}">${t.priority}</span>
                    <span class="pill status-${t.status}">${t.status.replace('_', ' ')}</span>
                </div>
            </div>
            ${t.description ? `<div class="text-xs text-gray-600 mt-1 truncate">${escHtml(t.description)}</div>` : ''}
            <div class="flex items-center gap-3 mt-2">
                ${t.category_name ? `<span class="flex items-center gap-1.5 text-xs text-gray-500">
                    <span class="dot" style="background: ${t.category_color}"></span>${escHtml(t.category_name)}
                </span>` : ''}
                ${dueDateStr ? `<span class="text-xs ${overdue ? 'text-red-400' : 'text-gray-600'}">${overdue ? '⚠ ' : ''}${dueDateStr}</span>` : ''}
            </div>
        </div>
        <div class="flex gap-1 items-start">
            <button onclick="openEditModal(${t.id})" class="text-gray-700 hover:text-gray-300 p-1.5 rounded transition-colors text-xs">✎</button>
            <button onclick="deleteTask(${t.id})" class="text-gray-700 hover:text-red-400 p-1.5 rounded transition-colors text-xs">✕</button>
        </div>
    </div>`;
}

function renderCategories() {
    const el = document.getElementById('sidebarCategories');
    el.innerHTML = categories.map(c => `
        <div class="sidebar-link" onclick="setFilter('category_id', '${c.id}')" id="filter-cat-${c.id}">
            <span class="dot" style="background: ${c.color}"></span>
            <span class="truncate">${escHtml(c.name)}</span>
            <span class="ml-auto text-gray-700 text-xs">${c.task_count}</span>
        </div>
    `).join('');
}

function populateCategorySelect() {
    const sel = document.getElementById('taskCategory');
    const current = sel.value;
    sel.innerHTML = '<option value="">No Category</option>' +
        categories.map(c => `<option value="${c.id}">${escHtml(c.name)}</option>`).join('');
    if (current) sel.value = current;
}

// ─── Filters ───────────────────────────────────────────────────────────────────

function setFilter(key, value) {
    currentFilters = { status: '', priority: '', category_id: '', search: '' };
    if (key && value !== '') currentFilters[key] = value;

    document.querySelectorAll('.sidebar-link').forEach(el => el.classList.remove('active'));
    if (key === 'status') {
        const id = value ? `filter-${value}` : 'filter-all';
        document.getElementById(id)?.classList.add('active');
        const titles = { '': 'All Tasks', pending: 'Pending', in_progress: 'In Progress', completed: 'Completed' };
        document.getElementById('pageTitle').textContent = titles[value] || 'All Tasks';
    } else if (key === 'category_id') {
        document.getElementById(`filter-cat-${value}`)?.classList.add('active');
        const cat = categories.find(c => c.id == value);
        document.getElementById('pageTitle').textContent = cat?.name || 'Tasks';
    }

    document.getElementById('priorityFilter').value = '';
    document.getElementById('searchInput').value = '';
    loadTasks();
}

function applyFilters() {
    currentFilters.priority = document.getElementById('priorityFilter').value;
    loadTasks();
}

function debounceSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        currentFilters.search = document.getElementById('searchInput').value;
        loadTasks();
    }, 350);
}

// ─── Task CRUD ─────────────────────────────────────────────────────────────────

document.getElementById('taskForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('taskId').value;
    const payload = {
        id: id ? parseInt(id) : undefined,
        title: document.getElementById('taskTitle').value,
        description: document.getElementById('taskDesc').value,
        priority: document.getElementById('taskPriority').value,
        status: document.getElementById('taskStatus').value,
        category_id: document.getElementById('taskCategory').value || null,
        due_date: document.getElementById('taskDue').value || null,
    };

    const method = id ? 'PUT' : 'POST';
    const data = await api('api/tasks.php', method, payload);
    if (data.success) {
        closeModal();
        await Promise.all([loadTasks(), loadStats(), loadCategories()]);
        showToast(id ? 'Task updated!' : 'Task created!', 'success');
    } else {
        showToast(data.error || 'Something went wrong', 'error');
    }
});

async function toggleTask(id, newStatus) {
    const data = await api('api/tasks.php', 'PUT', { id, status: newStatus });
    if (data.success) {
        await Promise.all([loadTasks(), loadStats()]);
    }
}

async function deleteTask(id) {
    if (!confirm('Delete this task?')) return;
    const data = await api(`api/tasks.php?id=${id}`, 'DELETE');
    if (data.success) {
        await Promise.all([loadTasks(), loadStats(), loadCategories()]);
        showToast('Task deleted', 'success');
    }
}

// ─── Modals ────────────────────────────────────────────────────────────────────

function openModal() {
    document.getElementById('modalTitle').textContent = 'New Task';
    document.getElementById('taskId').value = '';
    document.getElementById('taskForm').reset();
    document.getElementById('taskModal').classList.remove('hidden');
}

function openEditModal(id) {
    const task = tasks.find(t => t.id == id);
    if (!task) return;
    document.getElementById('modalTitle').textContent = 'Edit Task';
    document.getElementById('taskId').value = task.id;
    document.getElementById('taskTitle').value = task.title;
    document.getElementById('taskDesc').value = task.description || '';
    document.getElementById('taskPriority').value = task.priority;
    document.getElementById('taskStatus').value = task.status;
    document.getElementById('taskCategory').value = task.category_id || '';
    document.getElementById('taskDue').value = task.due_date || '';
    document.getElementById('taskModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('taskModal').classList.add('hidden');
}

function openCategoryModal() {
    document.getElementById('categoryModal').classList.remove('hidden');
}

function closeCategoryModal() {
    document.getElementById('categoryModal').classList.add('hidden');
    document.getElementById('catName').value = '';
}

async function createCategory() {
    const name = document.getElementById('catName').value.trim();
    const color = document.getElementById('catColor').value;
    if (!name) { showToast('Enter a category name', 'error'); return; }
    const data = await api('api/categories.php', 'POST', { name, color });
    if (data.success) {
        closeCategoryModal();
        await loadCategories();
        showToast('Category created!', 'success');
    }
}

// Close modal on backdrop click
document.getElementById('taskModal').addEventListener('click', (e) => { if (e.target === e.currentTarget) closeModal(); });
document.getElementById('categoryModal').addEventListener('click', (e) => { if (e.target === e.currentTarget) closeCategoryModal(); });

// ─── Utils ─────────────────────────────────────────────────────────────────────

function escHtml(str) {
    return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = `toast ${type}`;
    requestAnimationFrame(() => t.classList.add('show'));
    setTimeout(() => t.classList.remove('show'), 3000);
}

// ─── Init ──────────────────────────────────────────────────────────────────────

(async () => {
    await Promise.all([loadTasks(), loadCategories(), loadStats()]);
})();
</script>
</body>
</html>
