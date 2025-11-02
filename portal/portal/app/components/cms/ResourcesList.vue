<template>
  <section
    class="h-full pb-1 overflow-hidden"
    :class="{ 'outline outline-1 outline-red-500 rounded': isBin }"
  >
    <UICardHeader v-if="isBin" :background-color="false" no-fixed-height>
      <template #left>
        <UICardHeaderTitle :title="$t('Bin')" :icon="['fal', 'trash']" />
      </template>
      <template #right>
        <UIButton :icon="['fas', 'trash']" variant="danger" @click="handleEmptyBin"
          >Empty Bin
        </UIButton>
        <UIButton :icon="['fas', 'close']" variant="neutral" @click="handleCloseBin"
          >Close
        </UIButton>
      </template>
    </UICardHeader>

    <UICardHeader v-else :background-color="false" no-fixed-height>
      <template #left>
        <div class="flex flex-wrap items-center w-full">
          <UICardHeaderTitle
            class="mr-auto truncate"
            :title="$t('pages')"
            :icon="['fal', 'folder-tree']"
          />
          <div v-if="internalReordering" class="flex ml-auto text-right sm:ml-auto md:ml-0">
            <UIButton
              :icon="['fas', 'xmark']"
              variant="danger"
              class="ml-auto mr-2"
              @click="cancelReorder"
              >{{ $t("Cancel") }}
            </UIButton>
            <UIButton :icon="['fas', 'check']" variant="success" @click="reorder"
              >{{ $t("Save") }}
            </UIButton>
          </div>

          <div v-else class="flex flex-wrap text-right sm:ml-auto md:ml-0">
            <UIButton
              :icon="['fas', 'trash-can-list']"
              variant="neutral"
              class="!h-auto !aspect-auto"
              :disabled="isLoading"
              @click="handleOpenBin"
            />
            <UIButton
              :icon="['fas', 'shuffle']"
              variant="neutral"
              class="mx-2 truncate"
              :disabled="isLoading"
              @click="startReorder"
              >{{ $t("Reorder") }}
            </UIButton>
            <UIButton
              :icon="['fas', 'plus']"
              variant="default"
              :disabled="isLoading"
              @click="$emit('onNew')"
              >{{ $t("New") }}
            </UIButton>
          </div>
        </div>
      </template>
    </UICardHeader>
    <UICard class="!bg-transparent shadow-none">
      <div class="px-2">
        <ul v-if="isLoading">
          <li class="mb-1">
            <ResourcesListItemSkeleton />
          </li>
          <li class="mb-1">
            <ResourcesListItemSkeleton />
          </li>
          <li class="mb-1">
            <ResourcesListItemSkeleton />
          </li>
        </ul>

        <ResourcesListItem
          v-else
          root
          :show-resource-i-ds="showResourceIDs"
          :selected-resource="selectedResource"
          :resources="internalResources"
          :draggable="internalReordering"
          :is-bin-item="isBin"
          @on-item-select="(id) => $emit('onItemSelect', id)"
          @on-item-delete="(id) => $emit('onItemDelete', id)"
          @on-item-restore="(id) => $emit('onItemRestore', id)"
        />
      </div>
    </UICard>
  </section>
</template>

<script>
export default {
  props: {
    resources: {
      type: Array,
      default: () => [],
    },
    selectedResource: {
      type: [Number, String],
      default: null,
    },
    reordering: {
      type: Boolean,
      default: false,
    },
    isBin: {
      type: Boolean,
      default: false,
    },
    isLoading: {
      type: Boolean,
      default: false,
    },
    showResourceIDs: {
      type: Boolean,
      default: false,
    },
  },
  emits: [
    "onItemDelete",
    "onItemSelect",
    "onReorder",
    "onOpenBin",
    "onCloseBin",
    "onEmptyBin",
    "onNew",
    "onItemRestore",
  ],
  data() {
    return {
      internalReordering: this.reordering,
      internalResources: null,
    };
  },
  watch: {
    reordering(newVal) {
      this.internalReordering = newVal;
    },
    resources: {
      handler(newVal, oldVal) {
        this.internalResources = this.deepCloneArray(newVal);
      },
      immediate: true,
    },
  },
  methods: {
    reorder() {
      this.internalReordering = false;
      this.$emit("onReorder", this.internalResources);
    },
    cancelReorder() {
      this.internalReordering = false;
      this.internalResources = this.deepCloneArray(this.resources);
    },
    startReorder() {
      this.internalReordering = true;
    },
    handleOpenBin() {
      this.$emit("onOpenBin");
    },
    handleCloseBin() {
      this.$emit("onCloseBin");
    },
    handleEmptyBin() {
      this.$emit("onEmptyBin");
    },
    deepCloneArray(array) {
      return JSON.parse(JSON.stringify(array));
    },
  },
};
</script>
