<template>
  <VDropdown :triggers="props.disabled ? [] : ['click', 'hover']" placement="bottom-end">
    <UIButton
      variant="theme"
      class="relative !p-2"
      :class="{ 'cursor-not-allowed hover:!bg-transparent': props.disabled }"
    >
      <font-awesome-icon :icon="['fad', 'bell']" class="w-5 h-5" />
      <span
        v-if="!props.disabled && notificationRepository.unreadCount > 0"
        class="absolute bottom-0 right-0 flex items-center justify-center w-4 h-4 text-xs font-bold text-white transform translate-x-1 -translate-y-1 bg-red-500 rounded-full"
      >
        {{ notificationRepository.unreadCount }}
      </span>
    </UIButton>

    <template #popper>
      <div
        class="w-full p-2 bg-white border border-gray-200 rounded-md shadow-lg dark:bg-gray-900 sm:w-80 dark:border-gray-700"
      >
        <div
          class="flex items-start justify-between px-4 py-2 border-b border-gray-200 dark:border-gray-700"
        >
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Notifications</h3>
          <UIButton
            variant="theme"
            class="p-1 text-gray-500 dark:text-gray-400"
            @click="notificationRepository.markAllAsRead"
          >
            Mark all as read
          </UIButton>
        </div>

        <div class="divide-y divide-gray-200 dark:divide-gray-700">
          <div
            v-for="notification in notificationRepository.notifications.slice(0, 5)"
            :key="notification.id"
            :class="[
              'p-4 transition-all duration-300 cursor-pointer relative',
              'hover:bg-gray-50 dark:hover:bg-gray-800',
            ]"
            @click="markAsRead(notification.id)"
          >
            <div class="flex items-start gap-3">
              <div class="relative mt-1">
                <div class="stack">
                  <div
                    v-if="notification.unread"
                    :class="[
                      'z-10 inset-0 rounded-full h-full w-full',
                      {
                        'bg-green-100 dark:bg-green-500/20': notification.status === 'success',
                        'bg-yellow-100 dark:bg-yellow-500/20': notification.status === 'pending',
                        'bg-gray-100 dark:bg-gray-500/20': !notification.status,
                      },
                      'animate-gentle-pulse',
                    ]"
                  />
                  <div
                    :class="[
                      'h-8 w-8 rounded-full flex items-center justify-center z-20',
                      {
                        'bg-green-100 dark:bg-green-500/20': notification.status === 'success',
                        'bg-yellow-100 dark:bg-yellow-500/20': notification.status === 'pending',
                        'bg-gray-100 dark:bg-gray-500/20': !notification.status,
                      },
                    ]"
                  />
                  <font-awesome-icon
                    :icon="[
                      'fad',
                      notification.status === 'success'
                        ? 'check-circle'
                        : notification.status === 'pending'
                          ? 'clock'
                          : 'user',
                    ]"
                    class="z-30 w-4 h-4"
                    :class="[
                      {
                        'text-green-600 dark:text-green-500': notification.status === 'success',
                        'text-yellow-600 dark:text-yellow-500': notification.status === 'pending',
                        'text-gray-600 dark:text-gray-500': !notification.status,
                      },
                    ]"
                  />
                </div>
              </div>

              <div class="flex-1">
                <h4
                  :class="[
                    'text-sm',
                    notification.unread
                      ? 'font-semibold text-blue-600 dark:text-blue-400'
                      : 'font-medium text-gray-900 dark:text-white',
                  ]"
                >
                  {{ notification.title }}
                </h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  {{ notification.message }}
                </p>
                <div class="flex items-center gap-2 mt-1">
                  <span class="text-xs text-gray-500 dark:text-gray-500">
                    {{ formatRelativeTime(notification.timestamp) }}
                  </span>
                  <!-- Optional: Small dot indicator for unread -->
                  <div v-if="notification.unread" class="w-1.5 h-1.5 rounded-full bg-blue-500" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <div
          v-if="notificationRepository.notifications.length === 0"
          class="py-8 text-center text-gray-500 dark:text-gray-400"
        >
          <p>No notifications</p>
        </div>
      </div>
    </template>
  </VDropdown>
</template>

<script setup>
import { formatDistance, format, differenceInDays, isAfter } from "date-fns";

const props = defineProps({
  disabled: {
    type: Boolean,
    default: false,
  },
});

function formatRelativeTime(date) {
  // Convert input to Date object
  const now = new Date();

  // Handle future dates
  if (isAfter(date, now)) {
    return format(date, "dd-MM-yyyy");
  }

  // Calculate days difference
  const daysDiff = differenceInDays(now, date);

  // If more than 6 days, return formatted date
  if (daysDiff >= 6) {
    return format(date, "dd-MM-yyyy");
  }

  // For recent dates, return relative time
  return (
    formatDistance(date, now, { addSuffix: true })
      // Customize output to match exactly your required format
      .replace("about ", "")
      .replace("less than ", "")
      .replace("over ", "")
      .replace("almost ", "")
  );
}
const notificationRepository = useNotificationRepository();

onMounted(async () => {
  await notificationRepository.fetchNotifications();
  notificationRepository.initializeWebSocket();
});

onUnmounted(() => {
  notificationRepository.cleanup();
});

const markAsRead = async (id) => {
  await notificationRepository.markAsRead(id);
};
</script>

<style scoped>
@keyframes gentle-pulse {
  75%,
  100% {
    transform: scale(1.5);
    opacity: 0;
  }
}

.animate-gentle-pulse {
  animation: gentle-pulse 1500ms cubic-bezier(0, 0, 0.2, 1) infinite;
}
</style>
