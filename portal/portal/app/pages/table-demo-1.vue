<template>
  <main class="p-16">
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label for="search">Global Search</label>
        <UIInputText
          v-model="searchQuery"
          name="search"
          placeholder="Search users..."
          class="mb-4"
        />
      </div>
      <div>
        <label for="status">Filter by Status</label>
        <UIVSelect
          v-model="statusQuery"
          name="status"
          :options="['All', 'Active', 'Inactive', 'Pending']"
          placeholder="Select status"
          class="mb-4"
        />
      </div>
    </div>
    <UITable
      :data="users"
      :columns="columns"
      :title="{
        icon: ['fal', 'users'],
        text: 'Users',
      }"
      hover
      :pagination="{
        pageSize: 3,
      }"
      :filter="searchQuery"
      :column-filters="columnFilters"
      may-select-rows
      :row-selection="selectedRows"
      @row-selection-change="selectedRows = $event"
      @row-click="selectedUser = $event"
    >
      <template #actions="{ row }">
        <UIButton
          variant="neutral-light"
          icon="ellipsis"
          class="!h-7"
          @click="handleActionClick(row)"
        />
      </template>
    </UITable>
    <div class="grid grid-cols-2">
      <div class="mt-4 alert alert-info">
        <h3 class="font-bold">Selected User</h3>
        <pre>{{ JSON.stringify(selectedUser, null, 2) }}</pre>
      </div>
      <div class="mt-4 alert alert-info">
        <h3 class="font-bold">Selected Rows</h3>
        <pre>{{ JSON.stringify(selectedRows, null, 2) }}</pre>
      </div>
    </div>
  </main>
</template>

<script setup>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";

const columnFilters = ref([]);
const searchQuery = ref("");
const selectedUser = ref(null);
const selectedRows = ref({});

const statusQuery = ref("All");
watch(
  statusQuery,
  (value) => {
    setColumnFilters("status", value);
  },
  { immediate: true, deep: true },
);

function setColumnFilters(accessorKey, filterValue) {
  const columnFiltersWithoutCurrent = columnFilters.value.filter(
    (filter) => filter.id !== accessorKey,
  );
  columnFilters.value = [
    ...columnFiltersWithoutCurrent,
    {
      id: accessorKey,
      value: filterValue,
    },
  ];
}

const users = ref([
  {
    id: 1,
    firstName: "Sophie",
    lastName: "van der Berg",
    email: "sophie.vanderberg@email.com",
    role: "Admin",
    department: "IT",
    status: "Active",
    lastLogin: "2024-01-20T08:30:00",
    createdAt: "2023-01-15",
  },
  {
    id: 2,
    firstName: "Lars",
    lastName: "de Vries",
    email: "l.devries@email.com",
    role: "User",
    department: "Sales",
    status: "Active",
    lastLogin: "2024-01-21T09:15:00",
    createdAt: "2023-03-22",
  },
  {
    id: 3,
    firstName: "Emma",
    lastName: "Jansen",
    email: "emma.j@email.com",
    role: "Manager",
    department: "HR",
    status: "Active",
    lastLogin: "2024-01-19T14:20:00",
    createdAt: "2023-02-10",
  },
  {
    id: 4,
    firstName: "Thomas",
    lastName: "Bakker",
    email: "t.bakker@email.com",
    role: "User",
    department: "Marketing",
    status: "Inactive",
    lastLogin: "2023-12-15T11:45:00",
    createdAt: "2023-04-05",
  },
  {
    id: 5,
    firstName: "Lisa",
    lastName: "Visser",
    email: "l.visser@email.com",
    role: "User",
    department: "Finance",
    status: "Active",
    lastLogin: "2024-01-21T10:30:00",
    createdAt: "2023-05-18",
  },
  {
    id: 6,
    firstName: "Lucas",
    lastName: "Smit",
    email: "l.smit@email.com",
    role: "Manager",
    department: "Operations",
    status: "Active",
    lastLogin: "2024-01-20T16:45:00",
    createdAt: "2023-06-30",
  },
  {
    id: 7,
    firstName: "Anna",
    lastName: "Mulder",
    email: "a.mulder@email.com",
    role: "User",
    department: "Support",
    status: "Active",
    lastLogin: "2024-01-21T08:00:00",
    createdAt: "2023-07-12",
  },
  {
    id: 8,
    firstName: "Noah",
    lastName: "van Dijk",
    email: "n.vandijk@email.com",
    role: "User",
    department: "Sales",
    status: "Pending",
    lastLogin: null,
    createdAt: "2024-01-18",
  },
  {
    id: 9,
    firstName: "Julia",
    lastName: "de Boer",
    email: "j.deboer@email.com",
    role: "User",
    department: "Marketing",
    status: "Active",
    lastLogin: "2024-01-20T13:15:00",
    createdAt: "2023-09-25",
  },
  {
    id: 10,
    firstName: "Max",
    lastName: "Hoekstra",
    email: "m.hoekstra@email.com",
    role: "Admin",
    department: "IT",
    status: "Active",
    lastLogin: "2024-01-21T11:30:00",
    createdAt: "2023-10-08",
  },
]);

const columns = [
  {
    header: "ID",
    accessorKey: "id",
  },
  {
    header: "Voornaam",
    accessorKey: "firstName",
  },
  {
    header: "Achternaam",
    accessorKey: "lastName",
  },
  {
    header: "E-mail",
    accessorKey: "email",
  },
  {
    header: "Rol",
    accessorKey: "role",
    cell: ({ row }) => {
      const role = row.original.role;
      const icons = {
        User: "user",
        Manager: "user-tie",
        Admin: "user-police",
      };
      return h(FontAwesomeIcon, {
        icon: icons[role],
        class: "border border-theme-500 rounded-full p-2 text-theme-500",
      });
    },
  },
  {
    header: "Afdeling",
    accessorKey: "department",
  },
  {
    header: "Status",
    accessorKey: "status",
    meta: {
      class: "w-24",
    },
    cell: ({ row }) => {
      const status = row.original.status;
      const colors = {
        Active: "text-green-600",
        Inactive: "text-red-600",
        Pending: "text-yellow-600",
      };
      return h("span", { class: colors[status] }, status);
    },
    filterFn: (row, columnId, filterValue) => {
      if (filterValue === "All") return true;
      return filterValue === row.original.status;
    },
  },
  {
    header: "Laatste Login",
    accessorKey: "lastLogin",
  },
  {
    header: "Aangemaakt op",
    accessorKey: "createdAt",
  },
];
</script>
