<template>
  <div class="flex mb-10 dark:text-white">
    <div
      class="mt-1 text-xs text-center text-gray-600 truncate"
      style="width: 100%; min-width: 7rem; max-width: 7rem"
    >
      {{ event.day }} {{ event.hour }}
    </div>
    <aside class="ml-1">
      <div class="flex items-center mb-1">
        <div class="w-2 h-2 rounded-full bg-theme-400"></div>
        <div
          v-if="Object.hasOwnProperty.call(event, 'user.profile')"
          class="flex-1 pb-1 ml-4 text-sm font-bold text-gray-500 uppercase"
        >
          {{ event.user.profile.first_name }}
          {{ event.user.profile.last_name }}
        </div>
        <div v-else class="flex-1 ml-4 text-sm font-medium text-gray-600">
          {{ event.user.email }}
        </div>
      </div>
      <div class="ml-6 -mt-2 text-sm">
        <span>{{ event.event }}</span>

        <template v-if="event.from">
          <span class="text-gray-600"> {{ $t("from") }} </span>
          <span
            v-if="event.from.object"
            class="relative cursor-pointer text-theme-500 hover:text-theme-800"
            @click="drop = !drop"
          >
            {{ event.from.category_name }}
            <ul
              v-if="drop"
              class="p-2 rounded text-themecontrast-400 bg-theme-400 list hover:bg-theme-400"
            >
              <li
                v-for="(value, key, i) in event.from.object"
                :key="i"
                class="flex justify-between p-1 border-b border-theme-300"
              >
                <p class="text-xs font-bold capitalize">{{ key }}</p>
                <p class="text-xs font-bold capitalize">
                  {{ value }}
                </p>
              </li>
            </ul>
          </span>
          <span v-else class="relative cursor-pointer text-theme-500 hover:text-theme-800"
            >{{ event.from }}
          </span>
        </template>

        <template v-if="event.to">
          <span v-if="!event.to.object && event.from && event.to"> {{ $t("to") }} </span>
          <span
            v-if="event.to.object"
            class="relative cursor-pointer text-theme-500 hover:text-theme-800"
            @click="drop = !drop"
          >
            {{ event.to.category_name }}
            <ul
              v-if="drop"
              class="p-2 rounded text-themecontrast-400 bg-theme-400 list hover:bg-theme-400"
            >
              <li
                v-for="(value, key, i) in event.to.object"
                :key="i"
                class="flex justify-between p-1 border-b border-theme-300"
              >
                <p class="text-xs font-bold capitalize">{{ key }}</p>
                <p class="text-xs font-bold capitalize">
                  {{ value }}
                </p>
              </li>
            </ul>
          </span>
          <span
            v-else
            class="relative cursor-pointer text-theme-500 hover:text-theme-800"
            @click="drop = !drop"
          >
            <span v-if="typeof event.to === 'object'">
              {{ event.to.category_name }}
            </span>
            <span v-else>
              {{ event.to }}
            </span>
          </span>
        </template>
      </div>
    </aside>
  </div>
</template>

<script>
export default {
  props: {
    event: {
      default: {},
      validator: (value) => {
        return Object.prototype.hasOwnProperty.call(value, "user") || "User property is required.";
      },
    },
  },
  data: () => ({
    drop: false,
  }),
};
</script>

<style>
.history .list {
  position: absolute;
  width: 204px;
  z-index: 9999;
  transform: translateY(-40%);
  top: 0;
  left: 67px;
  list-style-type: none;
  -webkit-transform-origin: top;
  transform-origin: top;
  -webkit-transition: -webkit-transform 0.2s ease-in-out;
  transition: -webkit-transform 0.2s ease-in-out;
  transition: transform 0.2s ease-in-out;
  transition:
    transform 0.2s ease-in-out,
    -webkit-transform 0.2s ease-in-out;
  overflow: hidden;
}
.history .list li:last-child {
  border: none;
}

/* ==== Style Scrollbar ==== */
.history-wrapper::-webkit-scrollbar {
  width: 6px;
}

.history-wrapper::-webkit-scrollbar-track {
  -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  border-radius: 10px;
  background-color: #ececec;
}

.history-wrapper::-webkit-scrollbar-thumb {
  background-color: darkgrey;
  border-radius: 10px;
}

.history-wrapper::-webkit-scrollbar-thumb:hover {
  background-color: #8f8f8f;
}
</style>
