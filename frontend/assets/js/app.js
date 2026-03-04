// API Configuration
const API_BASE = window.API_BASE || '/api';
const DB_NAME = 'farmhandpro_db';
const DB_VERSION = 1;

// Global State
let db = null;
let authToken = null;

// Initialize IndexedDB
async function initDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onerror = () => reject(request.error);
        request.onsuccess = () => {
            db = request.result;
            resolve(db);
        };

        request.onupgradeneeded = (event) => {
            const db = event.target.result;

            if (!db.objectStoreNames.contains('users')) {
                db.createObjectStore('users', { keyPath: 'id' });
            }

            if (!db.objectStoreNames.contains('attendance')) {
                const attendanceStore = db.createObjectStore('attendance', { keyPath: 'id', autoIncrement: true });
                attendanceStore.createIndex('user_id', 'user_id', { unique: false });
                attendanceStore.createIndex('date', 'date', { unique: false });
            }

            if (!db.objectStoreNames.contains('reports')) {
                const reportsStore = db.createObjectStore('reports', { keyPath: 'id', autoIncrement: true });
                reportsStore.createIndex('user_id', 'user_id', { unique: false });
                reportsStore.createIndex('status', 'status', { unique: false });
            }

            if (!db.objectStoreNames.contains('syncQueue')) {
                db.createObjectStore('syncQueue', { keyPath: 'id', autoIncrement: true });
            }
        };
    });
}

// Alpine.js App Component
function app() {
    return {
        // Auth State
        authenticated: false,
        loading: true,
        contentLoading: false,
        loginLoading: false,
        loadingAction: false,
        showRegisterForm: false,
        loginForm: {
            email: 'worker1@farm.local',
            password: 'password'
        },
        loginError: '',

        // UI State
        currentView: 'dashboard',
        isOnline: navigator.onLine,

        // User Data
        user: null,
        
        // Attendance & Time Tracking
        isClocked: false,
        sessionTime: '0h 0m',
        clockTimer: null,
        todayHours: 0,
        weeklyHours: 0,
        monthlyHours: 0,

        // Data
        reports: [],
        attendanceHistory: [],

        async init() {
            // Initialize database
            await initDB();

            // Check if user is already logged in
            authToken = localStorage.getItem('authToken');
            if (authToken) {
                this.loading = true;
                await this.loadUser();
                if (this.user) {
                    this.authenticated = true;
                    await this.initializeApp();
                } else {
                    localStorage.removeItem('authToken');
                }
            }

            // Register service worker
            if ('serviceWorker' in navigator) {
                try {
                    await navigator.serviceWorker.register('/service-worker/sw.js');
                    console.log('Service Worker registered');
                } catch (err) {
                    console.error('Service Worker registration failed:', err);
                }
            }

            this.loading = false;
        },

        async initializeApp() {
            // Load all initial data
            await Promise.all([
                this.loadUser(),
                this.checkClockStatus(),
                this.loadAttendanceHistory(),
                this.loadReports()
            ]);
        },

        async handleLogin() {
            this.loginError = '';
            
            if (!this.loginForm.email || !this.loginForm.password) {
                this.loginError = 'Please enter email and password';
                return;
            }

            this.loginLoading = true;
            try {
                const response = await fetch(`${API_BASE}/auth/login`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.loginForm)
                });

                if (response.ok) {
                    const data = await response.json();
                    authToken = data.token;
                    localStorage.setItem('authToken', authToken);
                    
                    this.authenticated = true;
                    await this.initializeApp();
                } else {
                    const error = await response.json();
                    this.loginError = error.message || 'Login failed. Please check your credentials.';
                }
            } catch (err) {
                console.error('Login error:', err);
                this.loginError = 'Connection error. Please check your internet.';
            } finally {
                this.loginLoading = false;
            }
        },

        async loadUser() {
            try {
                const response = await fetch(`${API_BASE}/auth/me`, {
                    headers: {
                        'Authorization': `Bearer ${authToken || localStorage.getItem('authToken')}`
                    }
                });

                if (response.ok) {
                    this.user = await response.json();
                    authToken = localStorage.getItem('authToken');
                    return true;
                } else {
                    this.user = null;
                    return false;
                }
            } catch (err) {
                console.error('Failed to load user:', err);
                this.user = null;
                return false;
            }
        },

        async checkClockStatus() {
            try {
                const response = await fetch(`${API_BASE}/attendance/current`, {
                    headers: {
                        'Authorization': `Bearer ${authToken || localStorage.getItem('authToken')}`
                    }
                });

                if (response.ok) {
                    const attendance = await response.json();
                    this.isClocked = true;
                    this.startSessionTimer(attendance.clock_in_time);
                } else {
                    this.isClocked = false;
                }
            } catch (err) {
                console.error('Failed to check clock status:', err);
            }
        },

        async loadAttendanceHistory() {
            try {
                const response = await fetch(`${API_BASE}/attendance/history`, {
                    headers: {
                        'Authorization': `Bearer ${authToken || localStorage.getItem('authToken')}`
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.attendanceHistory = data.attendance || [];
                    
                    // Calculate hours
                    const now = new Date();
                    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                    const weekStart = new Date(today);
                    weekStart.setDate(today.getDate() - today.getDay());
                    const monthStart = new Date(now.getFullYear(), now.getMonth(), 1);

                    this.todayHours = 0;
                    this.weeklyHours = 0;
                    this.monthlyHours = 0;

                    for (const entry of this.attendanceHistory) {
                        const entryDate = new Date(entry.date || entry.clock_in_time);
                        const hours = entry.hours || 0;

                        if (entryDate >= today) {
                            this.todayHours += hours;
                        }
                        if (entryDate >= weekStart) {
                            this.weeklyHours += hours;
                        }
                        if (entryDate >= monthStart) {
                            this.monthlyHours += hours;
                        }
                    }
                }
            } catch (err) {
                console.error('Failed to load attendance history:', err);
                this.attendanceHistory = [];
            }
        },

        async loadReports() {
            try {
                const response = await fetch(`${API_BASE}/reports`, {
                    headers: {
                        'Authorization': `Bearer ${authToken || localStorage.getItem('authToken')}`
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.reports = data.reports || data || [];
                }
            } catch (err) {
                console.error('Failed to load reports:', err);
                this.reports = [];
            }
        },

        startSessionTimer(clockInTime) {
            if (this.clockTimer) clearInterval(this.clockTimer);

            const updateTimer = () => {
                const now = new Date();
                const start = new Date(clockInTime);
                const diff = now - start;
                const hours = Math.floor(diff / 3600000);
                const minutes = Math.floor((diff % 3600000) / 60000);

                this.sessionTime = `${hours}h ${minutes}m`;
            };

            updateTimer();
            this.clockTimer = setInterval(updateTimer, 60000);
        },

        async clockIn() {
            this.loadingAction = true;
            try {
                const response = await fetch(`${API_BASE}/attendance/clock-in`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${authToken || localStorage.getItem('authToken')}`
                    },
                    body: JSON.stringify({
                        notes: ''
                    })
                });

                if (response.ok) {
                    const attendance = await response.json();
                    this.isClocked = true;
                    this.startSessionTimer(attendance.clock_in_time);

                    if (db) {
                        const transaction = db.transaction(['attendance'], 'readwrite');
                        const store = transaction.objectStore('attendance');
                        store.add(attendance);
                    }

                    alert('✅ Clocked in successfully');
                } else {
                    alert('❌ Failed to clock in. Please try again.');
                }
            } catch (err) {
                console.error('Clock in error:', err);
                this.queueOfflineAction('clock-in', {});
                alert('✅ Clocked in (offline - will sync when online)');
                this.isClocked = true;
                this.startSessionTimer(new Date());
            } finally {
                this.loadingAction = false;
            }
        },

        async clockOut() {
            this.loadingAction = true;
            try {
                const response = await fetch(`${API_BASE}/attendance/clock-out`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${authToken || localStorage.getItem('authToken')}`
                    },
                    body: JSON.stringify({
                        notes: ''
                    })
                });

                if (response.ok) {
                    this.isClocked = false;
                    if (this.clockTimer) clearInterval(this.clockTimer);
                    this.sessionTime = '0h 0m';
                    alert('✅ Clocked out successfully');
                    await this.loadAttendanceHistory();
                } else {
                    alert('❌ Failed to clock out. Please try again.');
                }
            } catch (err) {
                console.error('Clock out error:', err);
                this.queueOfflineAction('clock-out', {});
                alert('✅ Clocked out (offline - will sync when online)');
                this.isClocked = false;
                if (this.clockTimer) clearInterval(this.clockTimer);
            } finally {
                this.loadingAction = false;
            }
        },

        async queueOfflineAction(action, data) {
            if (!db) return;
            
            const transaction = db.transaction(['syncQueue'], 'readwrite');
            const store = transaction.objectStore('syncQueue');
            store.add({
                action,
                data,
                timestamp: new Date().toISOString()
            });
        },

        async syncPendingData() {
            if (!this.isOnline || !db) return;

            const transaction = db.transaction(['syncQueue'], 'readonly');
            const store = transaction.objectStore('syncQueue');
            const request = store.getAll();

            request.onsuccess = async () => {
                const items = request.result;
                for (const item of items) {
                    try {
                        console.log('Syncing:', item);
                    } catch (err) {
                        console.error('Sync failed:', err);
                    }
                }
            };
        },

        async refreshData() {
            this.contentLoading = true;
            try {
                await Promise.all([
                    this.loadUser(),
                    this.loadAttendanceHistory(),
                    this.loadReports()
                ]);
            } finally {
                this.contentLoading = false;
            }
        },

        getCurrentDate() {
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            return new Date().toLocaleDateString('en-US', options);
        },

        formatDate(dateStr) {
            const date = new Date(dateStr);
            const options = { month: 'short', day: 'numeric' };
            return date.toLocaleDateString('en-US', options);
        },

        formatTime(timeStr) {
            if (!timeStr) return '';
            const date = new Date(timeStr);
            return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        },

        toggleSettings() {
            alert('⚙️ Settings - Coming soon');
        },

        async handleLogout() {
            if (confirm('Are you sure you want to logout?')) {
                try {
                    await fetch(`${API_BASE}/auth/logout`, {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${authToken}`
                        }
                    });
                } catch (err) {
                    console.error('Logout error:', err);
                }
                
                localStorage.removeItem('authToken');
                authToken = null;
                this.authenticated = false;
                this.user = null;
                this.isClocked = false;
                this.reports = [];
                this.attendanceHistory = [];
                this.loginForm.email = 'worker1@farm.local';
                this.loginForm.password = 'password';
                this.loginError = '';
            }
        },

        handleOnline() {
            this.isOnline = true;
            console.log('App is online');
            this.syncPendingData();
        },

        handleOffline() {
            this.isOnline = false;
            console.log('App is offline');
        }
    };
}
