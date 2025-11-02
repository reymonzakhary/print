<template>
  <main>
    <!-- Main Dashboard Container -->
    <!-- Welcome Section -->
    <header class="mb-8">
      <h1 class="text-3xl font-semibold text-gray-800 dark:text-gray-200">
        Welcome back, {{ userName }}
        <button
          class="inline-flex items-center ml-4 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
          @click="authStore.refreshToken()"
        >
          <svg class="mr-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
            />
          </svg>
          Refresh
        </button>
      </h1>
      <p class="mt-2 text-gray-600 dark:text-gray-400">
        Here's what's happening within Prindustry Platform today.
      </p>
    </header>

    <!-- Stats Grid Section -->
    <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-4">
      <!-- Vacant Terms Card -->
      <div
        class="p-6 bg-white rounded border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700"
      >
        <div class="flex justify-between items-center">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Vacant Terms</p>
            <p class="mt-1 text-2xl font-semibold text-theme-500 dark:text-theme-400">
              {{ stats.vacantTerms }}
            </p>
          </div>
          <div class="p-3 rounded-lg bg-theme-50 dark:bg-theme-900">
            <svg
              class="w-6 h-6 text-theme-500 dark:text-theme-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
              />
            </svg>
          </div>
        </div>
        <div class="flex items-center mt-4">
          <span
            :class="
              weeklyChange.vacantTerms < 0
                ? 'text-emerald-600 dark:text-emerald-400'
                : 'text-red-600 dark:text-red-400'
            "
            class="text-sm font-medium"
          >
            {{ weeklyChange.vacantTerms < 0 ? "↓" : "↑" }}
            {{ Math.abs(weeklyChange.vacantTerms) }}
          </span>
          <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">vs last week</span>
        </div>
      </div>

      <!-- Auto-Connected Card -->
      <div
        class="p-6 bg-white rounded border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700"
      >
        <div class="flex justify-between items-center">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Auto-Connected</p>
            <p class="mt-1 text-2xl font-semibold text-emerald-500 dark:text-emerald-400">
              {{ stats.autoConnected }}
            </p>
          </div>
          <div class="p-3 bg-emerald-50 rounded-lg dark:bg-emerald-900">
            <svg
              class="w-6 h-6 text-emerald-500 dark:text-emerald-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
          </div>
        </div>
        <div class="flex items-center mt-4">
          <span class="text-sm font-medium text-emerald-600 dark:text-emerald-400">
            ↑ {{ weeklyChange.autoConnected }}
          </span>
          <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">vs last week</span>
        </div>
      </div>

      <!-- Total Terms Card -->
      <div
        class="p-6 bg-white rounded border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700"
      >
        <div class="flex justify-between items-center">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Terms</p>
            <p class="mt-1 text-2xl font-semibold text-blue-500 dark:text-blue-400">
              {{ stats.totalTerms }}
            </p>
          </div>
          <div class="p-3 bg-blue-50 rounded-lg dark:bg-blue-900">
            <svg
              class="w-6 h-6 text-blue-500 dark:text-blue-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
              />
            </svg>
          </div>
        </div>
        <div class="flex items-center mt-4">
          <span class="text-sm font-medium text-blue-600 dark:text-blue-400">
            ↑ {{ weeklyChange.totalTerms }}
          </span>
          <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">this week</span>
        </div>
      </div>

      <!-- Completion Rate Card -->
      <div
        class="p-6 bg-white rounded border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700"
      >
        <div class="flex justify-between items-center">
          <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Completion Rate</p>
            <p class="mt-1 text-2xl font-semibold text-purple-500 dark:text-purple-400">
              {{ stats.completionRate }}%
            </p>
          </div>
          <div class="p-3 bg-purple-50 rounded-lg dark:bg-purple-900">
            <svg
              class="w-6 h-6 text-purple-500 dark:text-purple-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
              />
            </svg>
          </div>
        </div>
        <div class="flex items-center mt-4">
          <span class="text-sm font-medium text-purple-600 dark:text-purple-400">
            ↑ {{ weeklyChange.completionRate }}%
          </span>
          <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">vs last week</span>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
      <!-- Chart Section (spans 2 columns) -->
      <div
        class="col-span-2 p-6 bg-white rounded border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700"
      >
        <!-- Header Section -->
        <div class="flex flex-col mb-6 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
              Automation Performance
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
              Tracking automated vs manual term matching
            </p>
          </div>
          <!-- Key Stats -->
          <div class="flex flex-wrap gap-4 mt-4 lg:mt-0">
            <div class="px-4 py-2 bg-gray-50 rounded-lg dark:bg-gray-700">
              <p class="text-sm text-gray-600 dark:text-gray-400">Total Matches</p>
              <p class="text-lg font-semibold text-gray-900 dark:text-gray-200">
                {{ stats.totalMatches }}
              </p>
            </div>
            <div class="px-4 py-2 bg-indigo-50 rounded-lg dark:bg-indigo-900">
              <p class="text-sm text-indigo-600 dark:text-indigo-400">Automation Rate</p>
              <p class="text-lg font-semibold text-indigo-600 dark:text-indigo-400">
                {{ stats.automationRate }}%
              </p>
            </div>
            <div class="px-4 py-2 bg-emerald-50 rounded-lg dark:bg-emerald-900">
              <p class="text-sm text-emerald-600 dark:text-emerald-400">Monthly Improvement</p>
              <p class="text-lg font-semibold text-emerald-600 dark:text-emerald-400">
                +{{ stats.improvement }}%
              </p>
            </div>
          </div>
        </div>

        <!-- Chart Section -->
        <div class="h-80">
          <Line :data="performanceData" :options="chartOptions" />
        </div>
      </div>

      <!-- Notifications Section -->
      <div
        class="p-6 bg-white rounded border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700"
      >
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <div class="flex items-center">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
              Recent Notifications
            </h2>
            <div
              v-if="notificationRepository.unreadCount > 0"
              class="px-2 py-1 ml-3 text-xs font-medium text-white rounded-full bg-theme-500"
            >
              {{ notificationRepository.unreadCount }}
            </div>
          </div>
          <button class="text-sm font-medium text-theme-500 hover:text-theme-600" @click="null">
            View all
          </button>
        </div>

        <!-- Notifications List -->
        <div class="space-y-4">
          <template v-if="notificationRepository.notifications.length">
            <div
              v-for="notification in notificationRepository.notifications.slice(0, 4)"
              :key="notification.id"
              class="flex items-start p-3 space-x-4 rounded-lg transition-colors"
              :class="notification.unread ? 'bg-gray-50 dark:bg-gray-700' : ''"
            >
              <!-- Status Indicator -->
              <div class="flex-shrink-0 mt-1">
                <div
                  :class="[
                    {
                      'bg-green-100 dark:bg-green-500/20': notification.status === 'success',
                      'bg-yellow-100 dark:bg-yellow-500/20': notification.status === 'pending',
                      'bg-gray-100 dark:bg-gray-500/20': !notification.status,
                    },
                  ]"
                  class="w-2 h-2 rounded-full"
                />
              </div>

              <!-- Content -->
              <div class="flex-1 min-w-0">
                <div class="flex justify-between items-center">
                  <p class="text-sm font-medium text-gray-900 dark:text-gray-200">
                    {{ notification.title }}
                  </p>
                  <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ formatRelativeTime(notification.timestamp) }}
                  </span>
                </div>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                  {{ notification.message }}
                </p>

                <!-- Action Button (if unread) -->
                <div v-if="notification.unread" class="mt-2">
                  <button
                    class="text-xs font-medium text-theme-500 hover:text-theme-600"
                    @click="notificationRepository.markAsRead(notification.id)"
                  >
                    Mark as read
                  </button>
                </div>
              </div>
            </div>
          </template>

          <!-- Empty State -->
          <div v-else class="py-8 text-center">
            <svg
              class="mx-auto w-12 h-12 text-gray-400 dark:text-gray-500"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
              />
            </svg>
            <p class="mt-4 text-gray-600 dark:text-gray-400">No notifications yet</p>
          </div>
        </div>
      </div>

      <!-- Recent Activity Section (spans 2 columns) -->
      <div
        class="col-span-2 p-6 bg-white rounded border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700"
      >
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Recent Activity</h2>
          <button
            class="text-sm font-medium text-theme-500 hover:text-theme-600"
            @click="$emit('viewAll')"
          >
            View all
          </button>
        </div>

        <!-- Activity Table -->
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-gray-200 dark:border-gray-700">
                <th
                  class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400"
                >
                  Term
                </th>
                <th
                  class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400"
                >
                  Suggested Match
                </th>
                <th
                  class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400"
                >
                  Tenant
                </th>
                <th
                  class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400"
                >
                  Confidence
                </th>
                <th
                  class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400"
                >
                  Status
                </th>
                <th
                  class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400"
                >
                  Time
                </th>
                <th
                  class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-400"
                >
                  Action
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
              <tr
                v-for="activity in activities"
                :key="activity.id"
                class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700"
              >
                <td
                  class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap dark:text-gray-200"
                >
                  {{ activity.term }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap dark:text-gray-400">
                  {{ activity.suggestedMatch }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap dark:text-gray-400">
                  {{ activity.tenant }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <span class="mr-2 text-sm text-gray-900 dark:text-gray-200">
                      {{ activity.confidence }}%
                    </span>
                    <div class="w-16 h-1.5 bg-gray-200 rounded-full dark:bg-gray-700">
                      <div
                        class="h-1.5 rounded-full bg-theme-500"
                        :style="{ width: `${activity.confidence}%` }"
                      />
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full"
                    :class="getStatusBadge(activity.status).classes"
                  >
                    {{ getStatusBadge(activity.status).text }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">
                  {{ formatTime(activity.timestamp) }}
                </td>
                <td class="px-6 py-4 text-sm whitespace-nowrap">
                  <button
                    v-if="activity.status === 'pending'"
                    class="font-medium text-theme-500 hover:text-theme-600"
                    @click="handleReview(activity.id)"
                  >
                    Review
                  </button>
                  <span v-else class="text-gray-400 dark:text-gray-500"> - </span>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Empty State -->
          <div v-if="!activities.length" class="py-12 text-center">
            <svg
              class="mx-auto w-12 h-12 text-gray-400 dark:text-gray-500"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
              />
            </svg>
            <p class="mt-4 text-gray-600 dark:text-gray-400">No recent activity</p>
          </div>
        </div>
      </div>

      <!-- Quick Actions Section -->
      <div
        class="p-6 bg-white rounded border border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700"
      >
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Quick Actions</h2>
        </div>

        <!-- Action Cards -->
        <div class="space-y-4">
          <!-- Create Tenant Action -->
          <button
            class="flex relative justify-between items-center p-4 w-full rounded-lg transition-colors group bg-theme-50 text-theme-700 hover:bg-theme-100 dark:bg-theme-900 dark:text-theme-400 dark:hover:bg-theme-800"
            @click="handleCreateTenant"
          >
            <div class="flex items-center">
              <div class="p-2 bg-white rounded-lg shadow-sm group-hover:shadow dark:bg-gray-700">
                <svg
                  class="w-6 h-6 text-theme-500 dark:text-theme-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                  />
                </svg>
              </div>
              <div class="ml-4 text-left">
                <span class="block font-medium">Create New Tenant</span>
                <span class="text-sm text-gray-600 dark:text-gray-400"
                  >Add a new SaaS customer</span
                >
              </div>
            </div>
            <svg
              class="w-5 h-5 transition-transform text-theme-500 group-hover:translate-x-0.5 dark:text-theme-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 5l7 7-7 7"
              />
            </svg>
          </button>

          <!-- Create User Action -->
          <button
            class="flex relative justify-between items-center p-4 w-full text-gray-700 bg-gray-50 rounded-lg transition-colors group hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
            @click="handleCreateUser"
          >
            <div class="flex items-center">
              <div class="p-2 bg-white rounded-lg shadow-sm group-hover:shadow dark:bg-gray-600">
                <svg
                  class="w-6 h-6 text-gray-500 dark:text-gray-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
                  />
                </svg>
              </div>
              <div class="ml-4 text-left">
                <span class="block font-medium">Create New User</span>
                <span class="text-sm text-gray-600 dark:text-gray-400">Add a new user account</span>
              </div>
            </div>
            <svg
              class="w-5 h-5 text-gray-500 transition-transform group-hover:translate-x-0.5 dark:text-gray-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 5l7 7-7 7"
              />
            </svg>
          </button>
        </div>

        <!-- Help Section -->
        <div class="p-4 mt-6 bg-gray-50 rounded-lg dark:bg-gray-700">
          <div class="flex items-start">
            <svg
              class="mt-0.5 w-5 h-5 text-gray-400 dark:text-gray-300"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
            <p class="ml-3 text-sm text-gray-600 dark:text-gray-300">
              Need help? Check out our
              <a
                href="#"
                class="text-theme-500 hover:text-theme-600 dark:text-theme-400 dark:hover:text-theme-300"
                >documentation</a
              >
              for detailed guides on managing tenants and users.
            </p>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { formatDistance, format, differenceInDays, isAfter, formatDistanceToNow } from "date-fns";
import { Line } from "vue-chartjs";
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
} from "chart.js";

const sessionStore = useSessionStore();
const userName = sessionStore.session?.username;

/**
 * This is just for demo purposes and can be safely removed
 * Do not forget to remove the imports as well :S
 */
// Register Chart.js components
ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend);
// Mock data - replace with real data from your API
const performanceData = ref({
  labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
  datasets: [
    {
      label: "Automated Matches",
      data: [65, 78, 86, 92, 95, 98],
      borderColor: "#6366f1", // Indigo-500
      backgroundColor: "#6366f1",
      tension: 0.4,
      fill: false,
    },
    {
      label: "Manual Matches",
      data: [35, 28, 24, 18, 15, 12],
      borderColor: "#a855f7", // Purple-500
      backgroundColor: "#a855f7",
      tension: 0.4,
      fill: false,
    },
  ],
});

// Chart configuration
const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    intersect: false,
    mode: "index",
  },
  plugins: {
    legend: {
      position: "top",
      labels: {
        usePointStyle: true,
        padding: 20,
        font: {
          family: "system-ui",
        },
      },
    },
    tooltip: {
      backgroundColor: "white",
      titleColor: "#374151",
      bodyColor: "#374151",
      bodyFont: {
        family: "system-ui",
      },
      borderColor: "#e5e7eb",
      borderWidth: 1,
      padding: 12,
      usePointStyle: true,
      boxPadding: 4,
    },
  },
  scales: {
    x: {
      grid: {
        display: false,
      },
      ticks: {
        font: {
          family: "system-ui",
        },
      },
    },
    y: {
      beginAtZero: true,
      grid: {
        borderDash: [2, 2],
      },
      ticks: {
        font: {
          family: "system-ui",
        },
        callback: (value) => `${value}%`,
      },
    },
  },
});

// Statistics
const stats = ref({
  totalMatches: 4326,
  automationRate: 89,
  improvement: 12,
  vacantTerms: 24,
  autoConnected: 156,
  totalTerms: 892,
  completionRate: 87,
});

// Calculate week-over-week change (mock data)
const weeklyChange = computed(() => {
  return {
    vacantTerms: -5, // 5 fewer vacant terms than last week
    autoConnected: 12, // 12 more auto-connections than last week
    totalTerms: 45, // 45 new terms added this week
    completionRate: 2, // 2% improvement in completion rate
  };
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

// Mock data - replace with real data
const activities = ref([
  {
    id: 1,
    term: "Factuurpapier",
    suggestedMatch: "Letterhead",
    confidence: 89,
    status: "pending",
    tenant: "PrintCo Ltd.",
    timestamp: new Date(Date.now() - 1000 * 60 * 30).toISOString(), // 30 mins ago
  },
  {
    id: 2,
    term: "Geschäftspapier",
    suggestedMatch: "Letterhead",
    confidence: 92,
    status: "auto_matched",
    tenant: "Deutsche Print GmbH",
    timestamp: new Date(Date.now() - 1000 * 60 * 60).toISOString(), // 1 hour ago
  },
  {
    id: 3,
    term: "Corporate Letter",
    suggestedMatch: "Letterhead",
    confidence: 95,
    status: "confirmed",
    tenant: "UK Prints Ltd.",
    timestamp: new Date(Date.now() - 1000 * 60 * 90).toISOString(), // 1.5 hours ago
  },
  {
    id: 4,
    term: "Papier à lettres",
    suggestedMatch: "Letterhead",
    confidence: 87,
    status: "pending",
    tenant: "Imprimerie FR",
    timestamp: new Date(Date.now() - 1000 * 60 * 120).toISOString(), // 2 hours ago
  },
]);

const emit = defineEmits(["review", "viewAll"]);

const formatTime = (timestamp) => {
  return formatDistanceToNow(new Date(timestamp), { addSuffix: true });
};

const getStatusBadge = (status) => {
  const badges = {
    pending: {
      text: "Pending Review",
      classes: "bg-amber-50 text-amber-700",
    },
    auto_matched: {
      text: "Auto-matched",
      classes: "bg-emerald-50 text-emerald-700",
    },
    confirmed: {
      text: "Confirmed",
      classes: "bg-blue-50 text-blue-700",
    },
  };
  return badges[status] || badges.pending;
};

const handleReview = (activityId) => {
  emit("review", activityId);
};
</script>
