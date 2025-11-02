<template>
  <Teleport to="body">
    <div
      v-bind="$attrs"
      class="absolute top-0 bottom-0 left-0 right-0 z-50 flex items-center justify-center w-full h-full bg-black bg-opacity-50"
      @click="handleClick"
    >
      <UILoader color="text-white" />
    </div>
  </Teleport>
</template>

<script setup>
import { useStore } from "vuex";
const store = useStore();

function handleClick() {
  return store.commit("fm/modal/setModalState", {
    modalName: "",
    show: false,
  });
}
</script>
