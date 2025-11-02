<template>
  <div>
    <TenantModalCreate
      v-if="showCreateModal"
      @on-close="showCreateModal = false"
      @on-tenant-created="handleTenantCreated"
    />
    <TenantModalPreview
      :show="!!selectedTenant"
      :tenant="selectedTenant"
      @on-close="selectedTenant = null"
      @on-tenant-deleted="handleTenantDeleted"
      @on-tenant-updated="fetchTenants"
    />
    <header class="flex justify-between mb-2">
      <div class="flex gap-4 items-center">
        <UICardHeaderTitle title="Tenants" :icon="['fal', 'users']" />
        <NuxtLink to="/tenants/create">
          <UIButton :icon="['fal', 'user-plus']"> Add Tenant </UIButton>
        </NuxtLink>
      </div>
      <UIInputText v-model="searchQuery" name="search" placeholder="Search tenants" />
    </header>
    <UICard rounded-full>
      <UITable
        :loading="loading"
        :data="tenants"
        :columns="columnDef"
        zero-state="No tenants found."
        :pagination="{
          pageSize: pagination.per_page,
          pageCount: pagination.last_page,
          pageIndex: pagination.current_page,
        }"
        hover
        class="rounded-md border border-gray-300 overflow-hidden"
        @row-click="handleRowClick"
        @page-change="fetchTenants"
      >
        <template #actions="{ row }">
          <div class="flex gap-2 items-center">
            <UIButton
              v-tooltip="'Go to tenant\'s page'"
              :icon="['fal', 'external-link']"
              variant="theme-light"
              class="!h-7"
              @click.stop="
                navigateTo(`https://${row.domain}/manager`, { external: true, open: { target: '_blank' } })
              "
            />
            <UIButton variant="theme-light" @click.stop="impersonate(row.tenantId, row.domain)">
              Impersonate
            </UIButton>
          </div>
        </template>
      </UITable>
    </UICard>
  </div>
</template>

<script setup>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { vTooltip } from "floating-vue";

const tenantRepository = useTenantRepository();
const { handleError } = useMessageHandler();

const loading = ref(true);
const tenants = ref([]);
const pagination = ref({});
const searchQuery = ref("");
const selectedTenant = ref(null);
const showCreateModal = ref(false);

onMounted(fetchTenants);

const { $api } = useNuxtApp();

const impersonate = async (tenantId, domain) => {
  const url = new URL(location.href);
  const protocol = url.protocol;
  try {
    const response = await $api(`/auth/generate-session`, {
      method: "POST",
      body: { tenant_id: tenantId },
    });
    if (response) {
      window.open(protocol + `//${domain}/manager/impersonate/${response.token}`, "_blank");
    }
  } catch (err) {
    console.error(err);
  }
};

async function fetchTenants(e) {
  try {
    loading.value = true;
    const data = await tenantRepository.getAllTenants(e, searchQuery.value);
    tenants.value = data.data;
    pagination.value = data.meta;
  } catch (error) {
    handleError(error);
  } finally {
    loading.value = false;
  }
}

watch(
  searchQuery,
  useDebounceFn(() => {
    fetchTenants();
  }, 300),
);

function useDebounceFn(fn, delay) {
  let timeout;
  return (...args) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
      fn(...args);
    }, delay);
  };
}

function handleRowClick(row) {
  selectedTenant.value = row;
}

function handleTenantDeleted(tenantId) {
  tenants.value = tenants.value.filter((tenant) => tenant.id !== tenantId);
}

function handleTenantCreated() {
  fetchTenants();
}

const columnDef = ref([
  {
    header: "Logo",
    accessorKey: "logo",
    cell: (props) => {
      return h("img", {
        src: props.getValue(),
        alt: "logo",
        class: "w-8 aspect-square object-contain border border-gray-200 rounded",
        onError: (e) => {
          e.target.outerHTML = `<div class="flex justify-center items-center w-8 text-gray-400 bg-gray-100 rounded border border-gray-200 dark:bg-gray-800 aspect-square"></div>`;
        },
      });
    },
    meta: {
      class: "max-w-12 min-w-12",
      columnClass: "text-center",
    },
  },
  {
    header: "id",
    accessorKey: "id",
    meta: {
      class: "min-w-8 text-right",
      columnClass: "font-mono text-gray-500",
    },
    cell: (props) => {
      return h("span", "#" + props.getValue());
    },
  },
  {
    header: "Company",
    accessorKey: "companyName",
    meta: {
      class: "min-w-[100px] w-1/12",
    },
  },
  {
    header: "Company Owner",
    accessorKey: "name",
    meta: {
      class: "min-w-[100px] w-1/12",
    },
  },
  {
    header: "Domain",
    accessorKey: "domain",
    meta: {
      class: "min-w-[150px] w-2/12",
    },
  },
  {
    header: "supplier",
    accessorKey: "supplier",
    meta: {
      class: "min-w-8 text-right",
      columnClass: "font-mono text-gray-500",
    },
    cell: (props) => {
      let supplier;
      let external;

      if (props.getValue().supplier === true) {
        supplier = {
          icon: ["fal", "parachute-box"],
          class:
            "text-theme-500 border border-theme-500 rounded-full p-1 aspect-square text-xs align-middle mr-1",
        };
      }

      if (props.getValue().external === true) {
        external = {
          icon: ["fal", "plug"],
          class:
            "text-theme-500 border border-theme-500 rounded-full p-1 aspect-square text-xs align-middle",
        };
      }

      const resp = [
        withDirectives(h(FontAwesomeIcon, supplier), [[vTooltip, "supplier"]]),
        withDirectives(h(FontAwesomeIcon, external), [
          [vTooltip, "external supplier (print.com, Probo, etc)"],
        ]),
      ];

      return resp;
    },
  },
  {
    header: "Tenant ID",
    accessorKey: "tenantId",
    meta: {
      class: "min-w-[150px] w-2/12 text-right",
      columnClass: "font-mono text-gray-400",
    },
  },
//   {
//     header: "Supplier ID",
//     accessorKey: "supplierId",
//     meta: {
//       class: "min-w-[150px] w-2/12 text-right",
//       columnClass: "font-mono text-gray-400",
//     },
//   },
  {
    header: "Modules",
    accessorKey: "modules",
    meta: {
      class: "min-w-[100px] w-1/12 text-right",
    },
    cell: (props) => {
      const modules = props.getValue().length;
      const enabledModules = props.getValue().filter((module) => module.enabled).length;
      const paddedEnabledModules = enabledModules.toString().padStart(2, "0");
      return h("div", { class: "font-mono" }, [
        h("span", { class: "text-green-500" }, paddedEnabledModules),
        "/",
        modules,
      ]);
    },
  },
  {
    header: "Created At",
    accessorKey: "createdAt",
    meta: {
      class: "min-w-[100px] w-2/12 text-right",
    },
  },
  {
    header: "Updated At",
    accessorKey: "updatedAt",
    meta: {
      class: "min-w-[100px] w-2/12 text-right",
    },
  },
]);
</script>
