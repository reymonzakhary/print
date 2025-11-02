<template>
  <UICard class="backdrop-blur">
    <div class="grid grid-cols-5 py-2 px-3 text-xs font-bold uppercase">
      <div class="w-full overflow-hidden whitespace-nowrap text-ellipsis">
        {{ $t("name") }}
      </div>
      <div class="w-full overflow-hidden whitespace-nowrap text-ellipsis">
        {{ $t("primary email") }}
      </div>
      <div class="w-full overflow-hidden whitespace-nowrap text-ellipsis">
        {{ $t("primary phone number") }}
      </div>
      <div class="w-full overflow-hidden whitespace-nowrap text-ellipsis">
        {{ $t("total sales") }}
      </div>
      <div class="w-full overflow-hidden whitespace-nowrap text-ellipsis">
        {{ $t("total revenue") }}
      </div>
    </div>
  </UICard>
</template>
