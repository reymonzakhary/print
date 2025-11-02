<template>
  <div class="-mt-10 grid h-full place-items-center text-center">
    <div>
      <div class="my-4 -ml-10 flex justify-center">
        <font-awesome-icon :icon="['fal', 'exclamation-circle']" class="fa-3x m-4 text-gray-300" />
        <font-awesome-icon
          :icon="['fal', 'triangle-exclamation']"
          class="fa-5x my-4 text-gray-400"
        />
        <font-awesome-icon :icon="['fal', 'exclamation-circle']" class="fa-2x my-4 text-gray-300" />
      </div>
      <h1 class="-mt-4 text-xl font-bold text-gray-400">
        {{ $t("Whoops. This page doesn't seem to exist.") }}
      </h1>
      <div class="mt-4 flex gap-4">
        <SalesActionButton variant="neutral" @click="navigateTo('/')">
          {{ $t("Go to your dashboard") }}
        </SalesActionButton>
      </div>
    </div>
  </div>
</template>
