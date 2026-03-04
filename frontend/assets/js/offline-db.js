// Offline DB initialization and utilities
class OfflineDB {
    constructor() {
        this.db = null;
        this.DB_NAME = 'farmhandpro_db';
        this.DB_VERSION = 1;
    }

    async init() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.DB_NAME, this.DB_VERSION);

            request.onerror = () => reject(request.error);
            request.onsuccess = () => {
                this.db = request.result;
                resolve(this.db);
            };

            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                this.createStores(db);
            };
        });
    }

    createStores(db) {
        // Attendance store
        if (!db.objectStoreNames.contains('attendance')) {
            const attendanceStore = db.createObjectStore('attendance', { keyPath: 'id', autoIncrement: true });
            attendanceStore.createIndex('user_id', 'user_id', { unique: false });
            attendanceStore.createIndex('date', 'date', { unique: false });
            attendanceStore.createIndex('synced', 'synced', { unique: false });
        }

        // Reports store
        if (!db.objectStoreNames.contains('reports')) {
            const reportsStore = db.createObjectStore('reports', { keyPath: 'id', autoIncrement: true });
            reportsStore.createIndex('user_id', 'user_id', { unique: false });
            reportsStore.createIndex('status', 'status', { unique: false });
            reportsStore.createIndex('synced', 'synced', { unique: false });
        }

        // Sync queue
        if (!db.objectStoreNames.contains('syncQueue')) {
            const syncStore = db.createObjectStore('syncQueue', { keyPath: 'id', autoIncrement: true });
            syncStore.createIndex('action', 'action', { unique: false });
            syncStore.createIndex('timestamp', 'timestamp', { unique: false });
        }

        // Settings store
        if (!db.objectStoreNames.contains('settings')) {
            db.createObjectStore('settings', { keyPath: 'key' });
        }
    }

    async add(storeName, data) {
        const transaction = this.db.transaction([storeName], 'readwrite');
        const store = transaction.objectStore(storeName);
        return new Promise((resolve, reject) => {
            const request = store.add(data);
            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(request.result);
        });
    }

    async get(storeName, key) {
        const transaction = this.db.transaction([storeName], 'readonly');
        const store = transaction.objectStore(storeName);
        return new Promise((resolve, reject) => {
            const request = store.get(key);
            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(request.result);
        });
    }

    async getAll(storeName) {
        const transaction = this.db.transaction([storeName], 'readonly');
        const store = transaction.objectStore(storeName);
        return new Promise((resolve, reject) => {
            const request = store.getAll();
            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(request.result);
        });
    }

    async update(storeName, data) {
        const transaction = this.db.transaction([storeName], 'readwrite');
        const store = transaction.objectStore(storeName);
        return new Promise((resolve, reject) => {
            const request = store.put(data);
            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(request.result);
        });
    }

    async delete(storeName, key) {
        const transaction = this.db.transaction([storeName], 'readwrite');
        const store = transaction.objectStore(storeName);
        return new Promise((resolve, reject) => {
            const request = store.delete(key);
            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve();
        });
    }

    async clear(storeName) {
        const transaction = this.db.transaction([storeName], 'readwrite');
        const store = transaction.objectStore(storeName);
        return new Promise((resolve, reject) => {
            const request = store.clear();
            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve();
        });
    }
}

// Background sync manager
class SyncManager {
    constructor(offlineDB) {
        this.db = offlineDB;
    }

    async queueAction(action, data) {
        return this.db.add('syncQueue', {
            action,
            data,
            timestamp: new Date().toISOString(),
            retries: 0
        });
    }

    async processSyncQueue() {
        const items = await this.db.getAll('syncQueue');

        for (const item of items) {
            try {
                await this.syncItem(item);
                await this.db.delete('syncQueue', item.id);
            } catch (error) {
                console.error('Sync item failed:', error);
                item.retries = (item.retries || 0) + 1;
                if (item.retries < 3) {
                    await this.db.update('syncQueue', item);
                } else {
                    await this.db.delete('syncQueue', item.id);
                }
            }
        }
    }

    async syncItem(item) {
        const token = localStorage.getItem('authToken');
        const headers = {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        };

        switch (item.action) {
            case 'clock-in':
                return fetch('/api/attendance/clock-in', {
                    method: 'POST',
                    headers,
                    body: JSON.stringify(item.data)
                });
            case 'clock-out':
                return fetch('/api/attendance/clock-out', {
                    method: 'POST',
                    headers,
                    body: JSON.stringify(item.data)
                });
            case 'submit-report':
                return fetch('/api/reports', {
                    method: 'POST',
                    headers,
                    body: JSON.stringify(item.data)
                });
            default:
                throw new Error(`Unknown action: ${item.action}`);
        }
    }
}

// Export for use in app.js
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { OfflineDB, SyncManager };
}
