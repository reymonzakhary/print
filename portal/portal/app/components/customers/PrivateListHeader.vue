<template>
  <UICard class="backdrop-blur">
    <div class="grid grid-cols-4 px-3 py-2 text-xs font-bold uppercase">
      <div class="w-full overflow-hidden whitespace-nowrap text-ellipsis">
        {{ $t("name") }}
      </div>
      <div class="w-full overflow-hidden whitespace-nowrap text-ellipsis">
        {{ $t("email") }}
      </div>
      <div class="w-full overflow-hidden whitespace-nowrap text-ellipsis">
        {{ $t("total sales") }}
      </div>
      <div class="w-full overflow-hidden whitespace-nowrap text-ellipsis">
        {{ $t("verified") }}
      </div>
    </div>
  </UICard>
</template>
