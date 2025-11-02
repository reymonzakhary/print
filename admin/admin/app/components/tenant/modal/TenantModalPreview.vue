<template>
  <UIModalSlideIn
    :icon="['fas', 'user']"
    :title="`Tenant ${props.tenant?.name ?? ''}`"
    :show="props.show"
    @on-close="emit('on-close', $event)"
    @on-backdrop-click="emit('on-close', $event)"
  >
    <TenantModalCreate
      v-if="!!editingTenant"
      :z-index="10000"
      :editing="editingTenant"
      @on-close="editingTenant = false"
      @on-tenant-created="handleTenantEditted"
    />
    <div class="h-full md:w-[75vw] lg:w-[66vw] xl:w-[55vw] text-sm flex flex-col p-2 gap-4">
      <!-- Info -->
      <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 dark:text-theme-50">
        <article>
          <section class="flex items-end h-8">
            <h1 class="font-bold tracking-wide uppercase">Company</h1>
          </section>
          <section
            class="p-2 pt-8 bg-gray-100 border rounded dark:bg-gray-800 dark:border-gray-700"
          >
            <!-- negative margin top here is calculated based on the height of the title and the padding-top of the container. -->
            <div
              class="w-1/2 h-20 p-1 mx-auto -mt-16 bg-white border rounded shadow dark:bg-gray-900"
            >
              <img
                v-if="!invalidImage"
                :src="props.tenant.logo  + `?ts=${Date.now()}`"
                alt="logo"
                class="object-contain h-full mx-auto"
                @error="invalidImage = true"
              />
              
              <div v-if="invalidImage" class="h-full py-2 text-center">
                <font-awesome-icon
                  :icon="['fas', 'buildings']"
                  class="h-full text-gray-200 dark:text-gray-600"
                />
              </div>
            </div>
            <div class="flex justify-center gap-2 mt-2">
              <span class="text-gray-500 text-mono">#{{ props.tenant.id }}</span>
              <span>{{ props.tenant.name }}</span>
              <span>{{ props.tenant.owner.email }}</span>
            </div>
            <div class="flex gap-2 mt-2 font-mono text-center text-gray-400">
              <span v-tooltip="'Tenant ID'" class="select-all">
                {{ props.tenant.tenantId }}
              </span>
              <span>/</span>
              <span v-tooltip="'Supplier ID'" class="select-all">
                {{ props.tenant.supplierId }}
              </span>
            </div>
          </section>
        </article>
        <article>
          <section class="flex items-end h-8">
            <h1 class="font-bold tracking-wide uppercase">Owner</h1>
          </section>
          <section class="p-2 bg-gray-100 border rounded dark:bg-gray-800 dark:border-gray-700">
            <div class="flex justify-center gap-2 mt-2">
              <span>{{ props.tenant.owner.name }}</span>
              <span>{{ props.tenant.owner.email }}</span>
            </div>
          </section>
          <div
            class="grid grid-cols-2 gap-2 p-2 mt-2 bg-gray-100 border rounded dark:bg-gray-800 dark:border-gray-700"
          >
            <section>
              <h5 class="mb-1 font-bold">Created</h5>
              <div class="flex justify-between">
                <span class="flex items-center gap-2 p-1 pr-2">
                  <font-awesome-icon
                    :icon="['fal', 'calendar']"
                    class="mb-0.5 text-gray-500 dark:text-gray-100"
                  />
                  {{ extractDateFromFormattedDate(props.tenant.createdAt) }}
                </span>
                <span class="flex items-center gap-2 p-1 pr-2">
                  <font-awesome-icon
                    :icon="['fal', 'clock']"
                    class="mb-0.5 text-gray-500 dark:text-gray-100"
                  />
                  {{ extractTimeFromFormattedDate(props.tenant.createdAt) }}
                </span>
              </div>
            </section>
            <section>
              <h5 class="mb-1 font-bold">Updated</h5>
              <div class="flex justify-between">
                <span class="flex items-center gap-2 p-1 pr-2">
                  <font-awesome-icon
                    :icon="['fal', 'calendar']"
                    class="mb-0.5 text-gray-500 dark:text-gray-100"
                  />
                  {{ extractDateFromFormattedDate(props.tenant.createdAt) }}
                </span>
                <span class="flex items-center gap-2 p-1 pr-2">
                  <font-awesome-icon
                    :icon="['fal', 'clock']"
                    class="mb-0.5 text-gray-500 dark:text-gray-100"
                  />
                  {{ extractTimeFromFormattedDate(props.tenant.createdAt) }}
                </span>
              </div>
            </section>
          </div>
        </article>
      </div>

      <!-- Actions -->
      <div class="dark:text-theme-50">
        <h1 class="mb-2 font-bold tracking-wide uppercase">Actions</h1>
        <div class="grid grid-cols-2 gap-2">
          <NuxtLink
            :to="`/tenants/${props.tenant.id}`"
            class="flex items-center justify-center h-10 px-4 text-sm font-semibold text-white bg-blue-500 border border-blue-500 rounded enabled:hover:bg-blue-600 disabled:cursor-not-allowed disabled:opacity-25"
          >
            <font-awesome-icon :icon="['fas', 'edit']" class="mr-2" />
            Edit
          </NuxtLink>
          <button
            class="flex items-center justify-center h-10 px-4 text-sm font-semibold text-white bg-red-500 border border-red-500 rounded enabled:hover:bg-red-600 disabled:cursor-not-allowed disabled:opacity-25"
            :disabled="fetching"
            @click="handleDeleteTenant"
          >
            <font-awesome-icon :icon="['fas', 'trash-alt']" class="mr-2" />
            Delete
          </button>
        </div>
      </div>

      <!-- Modules -->
      <div class="dark:text-theme-50">
        <h1 class="mb-2 font-mono font-bold tracking-wide uppercase">
          <span class="mr-2">Modules</span>
          <span class="text-green-500">{{ enabledModulesCount }}</span>
          <span>/</span>
          <span>{{ props.tenant.modules.length }}</span>
        </h1>
        <ul class="grid grid-cols-3 gap-2 sm:grid-cols-6">
          <li v-for="module in tenant.modules" :key="module.name" class="border rounded">
            <!-- Render a collapsible details element if the amount of areas exceeds 3 -->
            <component
              :is="module.areas.length > 3 ? 'details' : 'div'"
              class="p-2 overflow-hidden text-xs border-b"
              :class="
                module.enabled ? 'bg-green-100 dark:bg-green-900' : 'bg-gray-100 dark:bg-gray-700'
              "
            >
              <!-- Render the summary if the amount of areas exceeds 3 -->
              <component
                :is="module.areas.length > 3 ? 'summary' : 'div'"
                class="font-normal lowercase"
                :class="
                  module.enabled
                    ? 'text-green-900 dark:text-green-100'
                    : ' text-gray-800 dark:text-gray-200'
                "
              >
                <span class="truncate">
                  <font-awesome-icon
                    class="mr-2"
                    :icon="['fal', module.enabled ? 'check-square' : 'square']"
                  />
                  <span>{{ module.name }}</span>
                </span>
              </component>
            </component>
            <ul class="p-2 text-xs min-h-16" data-details>
              <li v-for="area in module.areas" :key="area">
                <font-awesome-icon :icon="['fal', 'check']" class="text-[0.5rem] text-green-500" />
                {{ area }}
              </li>
            </ul>
          </li>
        </ul>
      </div>
      <!-- Weird fix for dissapearing padding. Probably something to do with the scroll but I don't want to spend too much time on it right now. -->
      <div class="min-h-2" />
    </div>
  </UIModalSlideIn>
</template>

<script setup>
const props = defineProps({
  show: Boolean,
  tenant: {
    type: Object,
    default: () => ({}),
  },
});
const emit = defineEmits(["on-close", "on-tenant-deleted", "on-tenant-updated"]);

const { extractTimeFromFormattedDate, extractDateFromFormattedDate } = useHelpers();
const tenantRepository = useTenantRepository();
const { handleError } = useMessageHandler();
const { confirm } = useConfirmation();
const { addToast } = useToastStore();

const fetching = ref(false);
const invalidImage = ref(false);
const enabledModulesCount = computed(() => {
  return props.tenant.modules.filter((module) => module.enabled).length;
});
const editingTenant = ref({});

watchEffect(() => {
  // Reset props as component is never dismounted
  if (props.show) {
    invalidImage.value = false;
    editingTenant.value = false;
  }
});

function handleEditTenant() {
  editingTenant.value = props.tenant;
}

async function handleTenantEditted() {
  editingTenant.value = false;
  emit("on-tenant-updated");
}

async function handleDeleteTenant() {
  try {
    fetching.value = true;
    await confirm({
      title: "Delete Tenant",
      message: "Are you sure you want to delete this tenant?",
      confirmOptions: {
        label: "Delete",
        variant: "danger",
      },
    });
    await tenantRepository.deleteTenant(`${props.tenant.id}`);
    emit("on-tenant-deleted", props.tenant.id);
    emit("on-close");
    addToast({
      message: "Tenant deleted successfully",
      type: "success",
    });
  } catch (error) {
    if (error.cancelled) return;
    handleError(error);
  } finally {
    fetching.value = false;
  }
}
</script>
