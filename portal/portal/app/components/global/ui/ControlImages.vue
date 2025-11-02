<template>
  <div class="w-screen">
    <SidePanel width="w-3/4" full-height="true" @on-close="close">
      <template #side-panel-header>
        <h2 class="bg-gray-100 p-2 text-sm font-bold uppercase tracking-wide">
          {{ $t("Control Options Images") }}
        </h2>
      </template>

      <template #side-panel-content>
        <div class="flex flex-wrap">
          <div v-for="(image, index) in props.images" :key="index" class="flex w-6/12 flex-col p-2">
            <UIImageSelector
              disk="assets"
              :selected-image="image"
              :from-controller="true"
              @on-image-remove="
                update_item({
                  key: 'media',
                  value: index,
                })
              "
            />
          </div>
        </div>
      </template>
    </SidePanel>
  </div>
</template>

<script setup>
import { useStore } from "vuex";

const store = useStore();

function update_item(payload) {
  if (props.updateCustom) {
    props.updateCustom(payload);
    return;
  }
  store.commit("assortmentsettings/update_item", payload);
}
const emit = defineEmits(["on-close"]);
const props = defineProps({
  images: {
    type: Array,
    default: () => [],
  },
  updateCustom: Function,
});
const close = () => {
  emit("on-close");
};
</script>

<style scoped></style>
