<template>
  <div class="grid grid-cols-1 gap-2 p-4 md:grid-cols-[300px_1fr]">
    <article class="mb-2 h-full md:mb-0">
      <UICardHeader>
        <template #left>
          <UICardHeaderTitle :title="$t('UI Settings')" :icon="['fal', 'browsers']" />
        </template>
      </UICardHeader>
      <UICard>
        <div class="grid grid-cols-2 p-2">
          <span class="text-xs font-bold uppercase text-gray-700 dark:text-white">{{
            $t("Show Resource IDs")
          }}</span>
          <UISwitch
            name="showResourceIDs"
            :value="showResourceIDs"
            @input="handleToggleShowResourceIDs"
          />
        </div>
        <div class="grid grid-cols-2 p-2">
          <span class="text-xs font-bold uppercase text-gray-700 dark:text-white">{{
            $t("Never show no calculation message")
          }}</span>
          <UISwitch
            name="disableForever"
            :value="disableForever"
            @input="disableForever = $event"
          />
        </div>
      </UICard>
    </article>
    <article>
      <UICardHeader>
        <template #left>
          <UICardHeaderTitle :title="$t('general settings')" />
        </template>
      </UICardHeader>
      <UICard>
        <div class="p-2">
          <nav class="my-4 flex w-full items-center">
            <button
              class="block border-b-2 px-6 py-2 text-gray-600 hover:text-theme-500 focus:outline-none"
              :class="{
                'border-theme-500 bg-theme-100 font-semibold text-theme-500':
                  component == 'ThemeColors',
              }"
              @click="component = 'ThemeColors'"
            >
              <font-awesome-icon :icon="['fal', 'hand-holding-dollar']" class="text-theme-500" />
              {{ $t("theme colors") }}
            </button>
            <button
              v-for="setting in core_settings"
              :key="`setting_${setting.id}`"
              class="block border-b-2 px-6 py-2 text-gray-600 hover:text-theme-500 focus:outline-none"
              :class="{
                'border-theme-500 bg-theme-100 font-semibold text-theme-500':
                  component == 'ThemeColors',
              }"
              @click="component = 'ThemeColors'"
            >
              <font-awesome-icon :icon="['fal', 'hand-holding-dollar']" class="text-theme-500" />
              {{ setting.name }}
            </button>
          </nav>

          <div class="flex w-full flex-wrap">
            <transition name="fade">
              <ThemeColors />
            </transition>
          </div>
        </div>
      </UICard>
    </article>
  </div>
</template>

<script>
import { mapMutations, mapGetters } from "vuex";

export default {
  transition: "disable",
  props: {
    settings: Array,
  },
  setup() {
    const disableForever = ref(localStorage.getItem("disableForever") === "true");
    watch(disableForever, (newValue) => {
      localStorage.setItem("disableForever", newValue);
    });
    return {
      disableForever,
    };
  },
  computed: {
    ...mapGetters({
      core_settings: ["settings/core_settings"],
    }),
    showResourceIDs() {
      return this.$store.getters["usersettings/showResourceIDs"];
    },
  },
  methods: {
    ...mapMutations({
      set_me: "settings/set_me",
    }),
    async saveMargins(object) {
      const updateMargin = await this.$axios.$patch("/margins/general", {
        margin: object,
      });
      this.$store.dispatch("toast/set", {
        text: updateMargin.message,
        status: updateMargin.status,
      });
    },
    handleToggleShowResourceIDs(value) {
      this.$store.dispatch("usersettings/set_showResourceIDs", value);
    },
  },
};
</script>

<style></style>
