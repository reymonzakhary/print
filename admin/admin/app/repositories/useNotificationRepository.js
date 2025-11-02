import { defineStore } from "pinia";
import { ref, computed } from "vue";

const STORAGE_KEY = "notifications";

export const useNotificationRepository = defineStore("notifications", () => {
  // State
  const notifications = ref([]);
  const isConnected = ref(false);
  const socketInstance = ref(null);

  // Getters
  const unreadCount = computed(() => notifications.value.filter((n) => n.unread).length);

  // Storage Management
  function loadFromStorage() {
    if (import.meta.client) {
      const stored = localStorage.getItem(STORAGE_KEY);
      if (stored) {
        notifications.value = JSON.parse(stored);
      }
    }
  }

  function saveToStorage() {
    if (import.meta.client) {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(notifications.value));
    }
  }

  // Fetch initial notifications
  async function fetchNotifications() {
    loadFromStorage();

    if (import.meta.dev) {
      if (notifications.value.length === 0) {
        const now = Date.now();
        const twoMinutesAgo = new Date(now - 2 * 60 * 1000).toISOString();
        const tenMinutesAgo = new Date(now - 10 * 60 * 1000).toISOString();
        const fifteenMinutesAgo = new Date(now - 15 * 60 * 1000).toISOString();

        const mockNotifications = [
          {
            id: "1",
            title: "New Tenant Created",
            message: 'Tenant "PrintShop Pro" has been successfully created',
            timestamp: twoMinutesAgo,
            status: "success",
            unread: true,
          },
          {
            id: "2",
            title: "New User Account",
            message: "User account for John Smith is ready",
            timestamp: tenMinutesAgo,
            status: "success",
            unread: true,
          },
          {
            id: "3",
            title: "Tenant Creation in Progress",
            message: 'Creating tenant "DigitalPress Inc."...',
            timestamp: fifteenMinutesAgo,
            status: "pending",
            unread: false,
          },
        ];

        await new Promise((resolve) => setTimeout(resolve, 500));
        notifications.value = mockNotifications;
        saveToStorage();
      }
    } else {
      // TODO: Implement a real implementation
    }
  }

  // Mark notification as read
  async function markAsRead(id) {
    if (import.meta.dev) {
      await new Promise((resolve) => setTimeout(resolve, 200));
      const notification = notifications.value.find((n) => n.id === id);
      if (notification) {
        notification.unread = false;
        saveToStorage();
      }
    } else {
      // Real implementation
      // await fetch(`/api/notifications/${id}/read`, { method: 'POST' });
      // const notification = notifications.value.find(n => n.id === id);
      // if (notification) {
      //   notification.unread = false;
      //   saveToStorage();
      // }
    }
  }

  // Mark all as read
  async function markAllAsRead() {
    if (import.meta.dev) {
      await new Promise((resolve) => setTimeout(resolve, 200));
      notifications.value.forEach((notification) => {
        notification.unread = false;
      });
      saveToStorage();
    } else {
      // Real implementation
      // await fetch('/api/notifications/read-all', { method: 'POST' });
      // notifications.value.forEach(notification => notification.unread = false);
      // saveToStorage();
    }
  }

  // Delete notification
  async function deleteNotification(id) {
    if (import.meta.dev) {
      await new Promise((resolve) => setTimeout(resolve, 200));
      notifications.value = notifications.value.filter((n) => n.id !== id);
      saveToStorage();
    } else {
      // Real implementation
      // await fetch(`/api/notifications/${id}`, { method: 'DELETE' });
      // notifications.value = notifications.value.filter(n => n.id !== id);
      // saveToStorage();
    }
  }

  // Clear all notifications
  async function clearAll() {
    if (import.meta.dev) {
      await new Promise((resolve) => setTimeout(resolve, 200));
      notifications.value = [];
      saveToStorage();
    } else {
      // Real implementation
      // await fetch('/api/notifications/clear', { method: 'DELETE' });
      // notifications.value = [];
      // saveToStorage();
    }
  }

  // WebSocket Connection Management
  function initializeWebSocket() {
    isConnected.value = true;

    // Simulate receiving new notifications periodically
    // setTimeout(() => {
    //   const now = Date.now().toString();
    //   handleNewNotification({
    //     id: Date.now().toString(),
    //     title: "New Development Notification",
    //     message: "This is a mock real-time notification",
    //     timestamp: now,
    //     status: "success",
    //     unread: true,
    //   });
    // }, 30000);

    // Real WebSocket implementation
    // const socket = new WebSocket(process.env.WEBSOCKET_URL);
    // socketInstance.value = socket;
    // socket.onopen = () => {
    //   isConnected.value = true;
    // };
    // socket.onmessage = (event) => {
    //   const notification = JSON.parse(event.data);
    //   handleNewNotification(notification);
    // };
    // socket.onclose = () => {
    //   isConnected.value = false;
    //   // Implement reconnection logic if needed
    // };
  }

  function handleNewNotification(notification) {
    notifications.value.unshift(notification);
    saveToStorage();
  }

  // Cleanup
  function cleanup() {
    if (socketInstance.value) {
      socketInstance.value.close();
      socketInstance.value = null;
      isConnected.value = false;
    }
  }

  return {
    // State
    notifications,
    isConnected,
    socketInstance,

    // Getters
    unreadCount,

    // Actions
    fetchNotifications,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    clearAll,
    initializeWebSocket,
    cleanup,
    handleNewNotification,
  };
});
