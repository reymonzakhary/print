<template>
  <div>
    <UICardHeader v-if="props.title">
      <template #left>
        <UICardHeaderTitle :icon="props.title.icon" :title="props.title.text" />
      </template>
    </UICardHeader>
    <div class="overflow-x-auto">
      <table :class="tableClasses">
        <thead v-if="!columns && loading">
          <tr>
            <th
              :colspan="table.getVisibleFlatColumns().length + (maySelectRows ? 2 : 1)"
              class="py-4 text-center"
            >
              <UILoader />
            </th>
          </tr>
        </thead>
        <thead
          v-else-if="(loading && columns) || !loading"
          class="text-xs uppercase bg-gray-100/40 dark:bg-gray-900 dark:text-white"
        >
          <tr v-for="headerGroup in headerGroups" :key="headerGroup.id">
            <!-- Selection Column -->
            <th
              v-if="maySelectRows && headerGroup.depth === 0"
              :class="[columnClasses, '!py-0 !px-0 w-[2.813rem] relative']"
            >
              <label
                for="toggleAllRowsSelected"
                class="w-[2.813rem] cursor-pointer grid place-items-center py-2"
              >
                <input
                  id="toggleAllRowsSelected"
                  type="checkbox"
                  class="!h-7 cursor-pointer"
                  :checked="table.getIsAllRowsSelected()"
                  @change="table.toggleAllRowsSelected()"
                  @click.stop="null"
                />
              </label>
            </th>

            <!-- Header cells -->
            <th
              v-for="header in headerGroup.headers"
              :key="header.id"
              :colspan="header.colSpan"
              :rowspan="getHeaderRowSpan(header, headerGroup)"
              :class="[
                columnClasses,
                '!py-2 !break-keep',
                header.column.columnDef.meta?.class,
                { 'border-b text-center font-bold': header.column.columnDef.columns },
              ]"
            >
              <div class="flex gap-1 items-center">
                <!-- Expansion Controls -->
                <div
                  v-if="
                    table.getCanSomeRowsExpand() &&
                    header.column.getIsFirstColumn() &&
                    headerGroup.depth === headerGroups.length - 1
                  "
                  class="inline-flex justify-end items-center pr-2"
                >
                  <UIButton
                    :icon="[
                      'fal',
                      table.getIsAllRowsExpanded()
                        ? 'angle-double-down'
                        : table.getIsSomeRowsExpanded()
                          ? 'angle-double-right'
                          : 'angle-double-up',
                    ]"
                    variant="neutral-light"
                    class="!h-5 !text-[0.65rem]"
                    @click="table.toggleAllRowsExpanded()"
                  />
                </div>

                <!-- Header Content -->
                <div class="flex-1 items-center">
                  <FlexRender
                    :render="header.column.columnDef.header"
                    :props="header.getContext()"
                  />
                </div>

                <!-- Column Ordering Controls -->
                <div
                  v-if="mayOrderColumns && headerGroup.depth === headerGroups.length - 1"
                  class="flex gap-1 items-center"
                >
                  <UIButton
                    icon="chevron-left"
                    class="!h-7"
                    @click="
                      isAccessorColumn(header.column.columnDef)
                        ? moveColumn(header.column.columnDef.accessorKey, 'left')
                        : null
                    "
                  />
                  <UIButton
                    icon="chevron-right"
                    class="!h-7"
                    @click="
                      isAccessorColumn(header.column.columnDef)
                        ? moveColumn(header.column.columnDef.accessorKey, 'right')
                        : null
                    "
                  />
                </div>
              </div>
            </th>

            <!-- Actions Column -->
            <th
              v-if="headerGroup.depth === 0"
              :rowspan="headerGroups.length"
              :class="[columnClasses, '!py-2 w-[2.813rem]']"
            >
              <VDropdown>
                <UIButton :icon="['fal', 'sliders']" variant="neutral-light" class="!h-7" />
                <template #popper>
                  <ol
                    class="p-4 text-sm text-white bg-gray-900 rounded-md divide-y divide-black shadow-md shadow-gray-200 dark:shadow-gray-900"
                  >
                    <li v-for="column in table.getAllLeafColumns()" :key="column.id">
                      <label
                        :for="`displayField_${column.id}`"
                        class="flex items-center"
                        :class="{ 'cursor-pointer': column.getCanHide() }"
                      >
                        <input
                          :id="`displayField_${column.id}`"
                          type="checkbox"
                          :checked="column.getIsVisible()"
                          :name="`displayField_${column.id}`"
                          class="mr-2"
                          :disabled="!column.getCanHide()"
                          @change="column.toggleVisibility()"
                        />
                        <span :class="{ 'text-gray-400': !column.getCanHide() }">
                          {{ column.columnDef.header }}
                        </span>
                      </label>
                    </li>
                  </ol>
                </template>
              </VDropdown>
            </th>
          </tr>
        </thead>
        <tbody v-if="columns && loading">
          <tr v-for="i in Math.min(_pagination.pageSize, 12)" :key="i" :class="['h-11']">
            <td
              v-if="i === Math.floor(Math.min(_pagination.pageSize, 12) / 2)"
              :colspan="table.getVisibleLeafColumns().length + 2"
              class="py-4 text-center"
            >
              <UILoader />
            </td>
          </tr>
        </tbody>
        <tbody v-else-if="!loading && table.getRowCount() > 0">
          <tr
            v-for="row in rows"
            :key="row.id"
            :class="[
              rowClasses,
              'bg-[var(--bg-color)] dark:bg-[var(--bg-color-dark)]',
              {
                'dark:hover:!bg-[var(--hover-bg-color-dark)] hover:!bg-[var(--hover-bg-color)]':
                  props.hover,
              },
              getRowStyleClass(row),
            ]"
            :style="{
              '--bg-color': getRowBackgroundColor(row.depth, false),
              '--hover-bg-color': getRowHoverColor(row.depth, false),
              '--bg-color-dark': getRowBackgroundColor(row.depth, true),
              '--hover-bg-color-dark': getRowHoverColor(row.depth, true),
            }"
            @click="emit('row-click', row.original)"
          >
            <td v-if="maySelectRows" :class="[columnClasses, '!py-2 w-[2.813rem] relative']">
              <label
                :for="row.id"
                class="grid absolute top-0 right-0 bottom-0 left-0 place-items-center cursor-pointer"
              >
                <input
                  :id="row.id"
                  type="checkbox"
                  class="!h-7 cursor-pointer"
                  :checked="row.getIsSelected()"
                  @change="row.toggleSelected()"
                  @click.stop="null"
                />
              </label>
            </td>
            <td
              v-for="cell in row.getVisibleCells()"
              ref="cells"
              :key="cell.id"
              :class="[
                columnClasses,
                'relative',
                cell.column.columnDef.meta?.class,
                cell.column.columnDef.meta?.columnClass,
              ]"
            >
              <div
                v-if="table.getCanSomeRowsExpand() && cell.column.getIsFirstColumn()"
                class="inline-flex justify-end items-center pr-2"
                :style="{
                  width: `${(row.depth + 1) * 1.75}rem`,
                }"
              >
                <UIButton
                  v-if="row.getCanExpand()"
                  :icon="[
                    'fal',
                    row.getCanExpand()
                      ? row.getIsExpanded()
                        ? 'chevron-down'
                        : 'chevron-up'
                      : 'minus',
                  ]"
                  variant="neutral-light"
                  class="!h-5 !text-[0.65rem]"
                  @click="row.toggleExpanded()"
                />
              </div>
              <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
            </td>
            <td :class="[columnClasses, 'w-[2.813rem] relative']">
              <slot name="actions" :row="row.original" />
            </td>
          </tr>
        </tbody>
        <tbody v-else>
          <tr>
            <td :colspan="table.getAllColumns().length + 2">
              <ZeroState class="pt-6" :message="zeroState" />
            </td>
          </tr>
        </tbody>
        <tfoot
          v-if="table.getPageCount() > 1"
          class="text-xs uppercase bg-gray-100/40 dark:bg-gray-900 dark:text-white"
        >
          <tr>
            <td
              :colspan="table.getVisibleLeafColumns().length + 2"
              :class="[columnClasses, '!py-2']"
            >
              <UIPagination
                :manual-pagination="hasManualPagination"
                :current-page="hasManualPagination ? _pagination.pageIndex + 1 : undefined"
                :total-pages="table.getPageCount()"
                @update:page="handlePageChange"
              />
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</template>

<script setup>
import { rankItem } from "@tanstack/match-sorter-utils";
import {
  useVueTable,
  getCoreRowModel,
  getExpandedRowModel,
  getPaginationRowModel,
  getFilteredRowModel,
  FlexRender,
} from "@tanstack/vue-table";

const props = defineProps({
  zeroState: {
    type: String,
    default: "No data",
  },
  title: {
    type: Object,
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  data: {
    type: Array,
    required: true,
  },
  columns: {
    type: Array,
    default: null,
  },
  filter: {
    type: String,
    default: "",
  },
  rowSelection: {
    type: Object,
    default: undefined,
  },
  columnFilters: {
    type: Object,
    default: undefined,
  },
  pagination: {
    type: Object,
    default: () => ({}),
  },
  columnsOrder: {
    type: Array,
    default: () => [],
  },
  columnsVisibility: {
    type: Object,
    default: () => ({}),
  },
  options: {
    type: Object,
    default: () => ({}),
  },
  getRowClass: {
    type: Function,
    default: null,
  },
  mayOrderColumns: {
    type: Boolean,
    default: false,
  },
  maySelectRows: {
    type: Boolean,
    default: false,
  },
  striped: {
    type: Boolean,
    default: false,
  },
  bordered: {
    type: Boolean,
    default: false,
  },
  hover: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["filter-change", "page-change", "row-selection-change"]);

const fuzzyFilter = (row, columnId, value, addMeta) => {
  const itemRank = rankItem(row.getValue(columnId), value);
  addMeta({ itemRank });
  return itemRank.passed;
};

function isAccessorColumn(columnDef) {
  return "accessorKey" in columnDef;
}

const _columns = computed(() => {
  let columns;

  if (props.columns) {
    columns = props.columns;
  } else if (props.data) {
    const uniqueProperties = new Set();

    props.data.forEach((item) => {
      Object.keys(item).forEach((key) => {
        uniqueProperties.add(key);
      });
    });

    columns = Array.from(uniqueProperties).map((property) => ({
      header:
        property
          .replace(/([A-Z])/g, " $1")
          .charAt(0)
          .toUpperCase() + property.replace(/([A-Z])/g, " $1").slice(1),
      accessorKey: property,
    }));
  } else {
    columns = [];
  }
  return columns;
});

const _columnsOrder = ref(props.columnsOrder);
const _columnsVisibility = ref(props.columnsVisibility);
const _pagination = computed(() => ({
  pageIndex: Math.max(0, (props.pagination?.pageIndex ?? 1) - 1),
  pageSize: props.pagination?.pageSize ?? 25,
  pageCount: props.pagination?.pageCount ?? undefined,
}));
const hasManualPagination = computed(() => !!_pagination.value.pageCount);

const table = computed(() => {
  return useVueTable({
    data: props.data,
    columns: _columns.value,
    pageCount: _pagination.value.pageCount,
    initialState: {
      columnVisibility: _columnsVisibility.value,
      columnOrder: _columnsOrder.value,
      ...(!hasManualPagination.value && { pagination: _pagination.value }),
    },
    state: {
      globalFilter: props.filter,
      ...(props.rowSelection !== undefined && { rowSelection: props.rowSelection }),
      ...(hasManualPagination.value && { pagination: _pagination.value }),
      ...(props.columnFilters !== undefined && { columnFilters: props.columnFilters }),
    },
    manualPagination: hasManualPagination.value,
    onGlobalFilterChange: (newFilter) => emit("filter-change", newFilter),
    ...(hasManualPagination.value && {
      onPaginationChange: (updater) => {
        const newPage =
          (typeof updater === "function"
            ? updater(_pagination.value).pageIndex
            : updater.pageIndex) + 1;
        emit("page-change", newPage);
      },
    }),
    ...(props.rowSelection !== undefined && {
      onRowSelectionChange: (updater) => {
        const newSelection =
          typeof updater === "function" ? updater(props.rowSelection ?? {}) : updater;
        emit("row-selection-change", newSelection);
      },
    }),
    getSubRows: (row) => row.children,
    getCoreRowModel: getCoreRowModel(),
    getExpandedRowModel: getExpandedRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    globalFilterFn: fuzzyFilter,
    renderFallbackValue: "N/A",
    ...props.options,
  });
});

const headerGroups = computed(() => table.value.getHeaderGroups());
const rows = computed(() => table.value.getRowModel().rows);

const moveColumn = (accessorKey, direction) => {
  const currentIndex = _columnsOrder.value.indexOf(accessorKey);
  const newIndex = direction === "left" ? currentIndex - 1 : currentIndex + 1;

  if (newIndex < 0 || newIndex >= _columnsOrder.value.length) {
    return;
  }

  const newColumnsOrder = [..._columnsOrder.value];
  newColumnsOrder.splice(currentIndex, 1);
  newColumnsOrder.splice(newIndex, 0, accessorKey);

  _columnsOrder.value = newColumnsOrder;
};

const getRowStyleClass = (row) => {
  return props.getRowClass ? props.getRowClass(table.value, row) : "";
};

const handlePageChange = (page) => {
  if (hasManualPagination.value) {
    return emit("page-change", page);
  } else {
    table.value.setPageIndex(page - 1);
  }
};

function getRowBackgroundColor(rowDepth, isDark = false) {
  if (isDark) {
    return rowDepth > 0 ? `hsl(220deg 14.3% ${3.9 + rowDepth * 1.25}%)` : `hsl(220deg 14.3% 10%)`;
  } else {
    return rowDepth > 0 ? `hsl(220deg 14.3% ${98.9 - rowDepth * 1.5}%)` : `white`;
  }
}

function getRowHoverColor(rowDepth, isDark = false) {
  if (isDark) {
    return `hsl(220deg 14.3% ${6.9 + rowDepth * 3}%)`;
  } else {
    return `hsl(220deg 14.3% ${97.9 - rowDepth * 3}%)`;
  }
}

const tableClasses = computed(() => ({
  "w-full text-sm text-left shadow-md bg-gray-50 dark:bg-gray-800 dark:border-gray-950": true,
  "border border-gray-200 dark:border-black": props.bordered,
}));

const rowClasses = computed(() => ({
  "border-b border-gray-200 dark:border-black dark:text-white": true,
  "hover:bg-gray-100 dark:hover:bg-gray-950 cursor-pointer": props.hover,
  "even:bg-gray-50 dark:even:bg-gray-800 odd:bg-white dark:odd:bg-gray-900": props.striped,
}));

const columnClasses = computed(() => ({
  "px-2 py-3 break-word": true,
  "border border-gray-200 dark:border-black": props.bordered,
}));

function getHeaderRowSpan(header, headerGroup) {
  // If this is a leaf header (no sub columns), it should span all the way to the bottom
  if (!header.column.columnDef.columns) {
    return headerGroups.value.length - headerGroup.depth;
  }
  // Otherwise, it just takes up one row
  return 1;
}
</script>
