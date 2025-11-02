<template>
  <VDropdown>
    <button class="ml-4 text-sm text-themecontrast-400">
      <font-awesome-icon :icon="['fad', 'circle-info']" />
    </button>
    <template #popper>
      <ol
        class="divide-y divide-black rounded-md bg-gray-900 p-4 text-sm text-white shadow-md shadow-gray-200 dark:shadow-gray-900"
      >
        <li>
          <b>{{ $t("access") }}:</b> {{ $t("access page") }}
        </li>
        <li>
          <b>{{ $t("list") }}:</b> {{ $t("list items on page") }}
        </li>
        <li>
          <b>{{ $t("read") }}:</b> {{ $t("read item's details") }}
        </li>
        <li>
          <b>{{ $t("create") }}:</b> {{ $t("create item") }}
        </li>
        <li>
          <b>{{ $t("update") }}:</b> {{ $t("update item") }}
        </li>
        <li>
          <b>{{ $t("delete") }}:</b> {{ $t("delete item") }}
        </li>
      </ol>
    </template>
  </VDropdown>
</template>
