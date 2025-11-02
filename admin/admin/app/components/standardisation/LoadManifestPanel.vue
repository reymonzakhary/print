<template>
  <!-- <div> -->
  <UIModalSlideIn
    class="w-1/2"
    :icon="['fas', 'pencil']"
    :title="`Load ${item.name} from supplier manifest`"
    :show="true"
    @on-close="emit('close', $event)"
    @on-backdrop-click="emit('close', $event)"
  >
    <div class="relative p-4 text-base h-full w-full flex gap-4">
      <PrindustryLogo
        class="absolute -right-[20%] top-1/2 -translate-y-1/2 text-gray-50 dark:text-gray-900 z-0 h-[750px]"
      />
      <!-- CONNECTED SUPPLIER ITEMS -->
      <section class="w-full dark:text-white z-10">
        <p class="mt-4 mb-2 font-bold tracking-wide uppercase">
          <font-awesome-icon :icon="['fal', 'link']" class="mr-1" />
          <font-awesome-icon :icon="['fal', 'parachute-box']" class="mr-1" />
          <span class="text-gray-500">Connected supplier </span>
          {{ type === "category" ? "categories" : type === "box" ? "boxes" : "options" }}
        </p>
        <ul class="divide-y dark:divide-black">
          <li
            v-for="(supplierItem, i) in item.suppliers"
            :key="supplierItem.id"
            class="p-2 my-2 transition-colors first:rounded-t last:rounded-b hover:bg-gray-100 dark:hover:bg-gray-900"
          >
            <div class="flex items-center justify-between">
              <span class="flex flex-1">
                <span class="font-bold">
                  {{ supplierItem.tenant_name }}
                </span>
                's
                <span class="ml-4">
                  {{ supplierItem.name }}
                </span>
              </span>
              <font-awesome-icon :icon="['fal', 'arrow-right']" class="flex-1 text-blue-500" />
              <span class="flex">
                <button
                  class="flex-1 px-2 py-1 transition-colors rounded bg-gradient-to-r from-pink-500 via-purple-500 group to-cyan-500 inline-block text-transparent bg-clip-text ml-2 hover:text-white hover:from-pink-400 hover:via-purple-400 hover:to-cyan-400 hover:bg-clip-border"
                  @click="emit('onSelectProducer', supplierItem)"
                >
                  <font-awesome-icon
                    :icon="['fal', 'plus']"
                    class="text-pink-500 group-hover:text-white"
                  />
                  Load manifest
                </button>
              </span>
            </div>
          </li>
        </ul>
      </section>
    </div>
  </UIModalSlideIn>
</template>

<script setup>
import UIModalSlideIn from "../global/ui/modal/UIModalSlideIn.vue";
// import EditForm from "./inputs/EditForm.vue";

const standardizationRepository = useStandardizationRepository();
const { handleError, handleSuccess } = useMessageHandler();

const props = defineProps({
  item: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(["close", "onSelectProducer"]);
const editLinked = ref(false);
const onClose = () => {
  emit("close");
};
</script>

<style></style>
