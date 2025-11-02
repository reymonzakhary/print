<template>
  <div v-show="!open" class="fixed right-0 top-1/2 z-[9999] w-fit -translate-y-1/2 py-6 pl-6">
    <div
      class="relative -right-[6px] cursor-pointer rounded-l-full bg-white p-2 shadow-theme-500 filter"
      style="
        --tw-drop-shadow: drop-shadow(0 20px 13px rgb(48 149 180 / 0.03))
          drop-shadow(0 8px 5px rgb(48 149 100 / 0.08));
      "
      @click="open = true"
    >
      <PrindustryLogo class="-mb-[1px] h-5 w-5 text-prindustry" :scale="1.5" />
    </div>
  </div>
  <UICard
    v-show="open"
    ref="debugToolbar"
    class="fixed right-0 top-1/2 z-[9999] h-96 w-fit -translate-y-1/2 !bg-none !py-0"
    rounded-full
  >
    <header
      class="flex cursor-move items-center justify-between bg-gray-200/50 px-4 py-2 !shadow-2xl backdrop-blur-md"
    >
      <h1 class="font-semibold">{{ $t("Debug Toolbar") }}</h1>
      <UIButton
        class="!bg-gray-200 !text-base !text-gray-800 hover:!bg-gray-300"
        :icon="['fal', 'xmark']"
        @click="open = false"
      />
    </header>
    <div
      class="bg-white/50 backdrop-blur-lg transition"
      :class="{ 'h-0 w-0 overflow-hidden': !open }"
    >
      <UIInputText
        v-model="query"
        name="debugQuery"
        placeholder="Search permission"
        input-class="px-4 !py-2"
      />
      <div class="flex items-center px-4 py-1">
        <UIButton size="sm" class="!py-1" :icon="['fal', 'rotate']" @click="authStore.fetchUser">
          {{ $t("Refresh Permissions") }}
        </UIButton>
      </div>
      <ul class="max-h-96 overflow-y-auto px-4 py-2">
        <li
          v-for="permission in filteredPermissions"
          :key="permission"
          class="grid grid-cols-[40px_,_1fr] items-center gap-4 p-1"
        >
          <UISwitch
            :name="`check_${permission}`"
            :value="authStore.permissions.includes(permission)"
            @input="handleTogglePermission(permission, $event)"
          />
          <span>{{ permission }}</span>
        </li>
      </ul>
    </div>
  </UICard>
</template>

<script setup>
const authStore = useAuthStore();
const { permissions, checkedPermissions } = storeToRefs(useAuthStore());
const router = useRouter();

const debugToolbar = ref(null);

const query = ref("");
const open = ref(false);
function handleTogglePermission(permission, value) {
  if (!permissions.value) return;

  if (value) {
    permissions.value.add(permission);
  } else {
    permissions.value.delete(permission);
  }
}

const filteredPermissions = computed(() => {
  if (!query.value) {
    return checkedPermissions.value;
  }
  return [...checkedPermissions.value].filter((permission) =>
    permission.toLowerCase().includes(query.value.toLowerCase()),
  );
});

watch(
  () => router.currentRoute.value.fullPath,
  () => {
    authStore.clearCheckedPermissions();
  },
);
</script>
