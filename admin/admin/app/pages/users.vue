<template>
  <div>
    <UserModalCreate
      v-if="showCreateModal"
      :editing="selectedUser"
      @on-close="(showCreateModal = false) && (selectedUser = false)"
      @on-user-created="handleUserCreated"
      @on-user-updated="handleUserUpdated"
      @on-user-deleted="handleUserDeleted"
    />
    <header class="flex justify-between mb-2">
      <div class="flex gap-4 items-center">
        <UICardHeaderTitle title="Users" :icon="['fal', 'users']" />
        <UIButton variant="link" :icon="['fal', 'user-plus']" @click="showCreateModal = true">
          Add User
        </UIButton>
      </div>
      <UIInputText v-model="searchQuery" name="search" placeholder="Search Users" />
    </header>
    <UICard rounded-full>
      <UITable
        :loading="loading"
        :data="users"
        :columns="columnDef"
        hover
        zero-state="No users found."
        :filter="searchQuery"
        :pagination="{
          pageSize: 12,
        }"
        class="rounded-md border border-gray-300 overflow-hidden"
      >
        <template #actions="{ row }">
          <div
            class="flex absolute top-0 right-0 bottom-0 left-0 justify-center items-center bg-gray-300/10 dark:bg-gray-300/5"
          >
            <UIButton
              variant="neutral-light"
              :icon="['fal', 'pencil']"
              class="!h-7 !text-[0.65rem]"
              @click="handleEditUser(row)"
            />
          </div>
        </template>
      </UITable>
    </UICard>
  </div>
</template>

<script setup>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
const userRepository = useUserRepository();
const { handleError } = useMessageHandler();

const loading = ref(true);
const users = ref([]);
const searchQuery = ref("");
const showCreateModal = ref(false);
const selectedUser = ref(null);

onMounted(fetchUsers);

async function fetchUsers() {
  try {
    loading.value = true;
    users.value = await userRepository.getAllUsers();
  } catch (error) {
    handleError(error);
  } finally {
    loading.value = false;
  }
}

function handleEditUser(user) {
  selectedUser.value = user;
  showCreateModal.value = true;
}

function handleUserCreated() {
  fetchUsers();
}

function handleUserUpdated() {
  fetchUsers();
}

function handleUserDeleted() {
  fetchUsers();
}

const columnDef = ref([
  {
    header: "Personal Info",
    columns: [
      {
        header: "Id",
        accessorKey: "id",
        meta: {
          class: "w-1/24",
          columnClass: "font-mono text-gray-500",
        },
        cell: (props) => {
          return h("span", "#" + props.getValue());
        },
      },
      {
        header: "Email",
        accessorKey: "email",
        meta: {
          class: "min-w-[150px]",
        },
      },
      {
        header: "Username",
        accessorKey: "username",
        meta: {
          class: "w-1/12",
        },
      },
      {
        header: "Verified",
        accessorKey: "verified",
        meta: {
          class: "w-1/12 text-center",
        },
        cell: (props) => {
          return props.getValue()
            ? h(FontAwesomeIcon, {
                icon: ["fal", "check-circle"],
                class: "text-green-500 text-base",
              })
            : h(FontAwesomeIcon, {
                icon: ["fal", "times-circle"],
                class: "text-gray-800 text-base",
              });
        },
      },
    ],
  },
  {
    header: "Company Info",
    meta: {
      class: "border-l border-l-gray-200 dark:border-l-gray-700 bg-gray-300/10 dark:bg-gray-300/5",
    },
    columns: [
      {
        header: "Name",
        accessorKey: "company.name",
        meta: {
          class:
            "min-w-[150px] border-l border-l-gray-200 dark:border-l-gray-700 bg-gray-300/10 dark:bg-gray-300/5",
        },
      },
      {
        header: "Dscription",
        accessorKey: "company.description",
        meta: {
          class: "w-4/12 bg-gray-300/10 dark:bg-gray-300/5",
        },
        cell: (props) => {
          return h("span", { class: "line-clamp-2" }, props.getValue());
        },
      },
      {
        header: "URL",
        accessorKey: "company.url",
        meta: {
          class: "w-2/12 bg-gray-300/10 dark:bg-gray-300/5",
        },
      },
      {
        header: "COC",
        accessorKey: "company.chamberOfCommerce",
        meta: {
          class: "w-1/12 bg-gray-300/10 dark:bg-gray-300/5 text-right",
        },
      },
      {
        header: "TAX_NR",
        accessorKey: "company.taxNumber",
        meta: {
          class: "w-2/12 bg-gray-300/10 dark:bg-gray-300/5 text-right",
        },
      },
    ],
  },
]);
</script>
