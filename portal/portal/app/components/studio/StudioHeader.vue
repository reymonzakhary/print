<template>
  <div v-if="showNotSaved" class="absolute inset-0 z-50 bg-gray-100/20 dark:bg-gray-900/20" />
  <header class="relative flex items-center rounded-md dark:border-gray-700">
    <div class="w-[250px] bg-gray-100 dark:border-gray-700 dark:bg-gray-800">
      <div class="flex items-center text-base">
        <StudioIcon class="mr-1" />
        <h1>{{ $t("Prindustry Studio") }}</h1>
      </div>
    </div>

    <div
      class="relative flex flex-1 items-center justify-between rounded-t-md bg-gray-50 p-2 shadow dark:border-gray-900 dark:bg-gray-750"
    >
      <div>
        <UIButton
          v-if="!noBackButton"
          variant="link"
          :icon="['fal', 'arrow-left']"
          class="text-white"
          @click="emit('go-back')"
        >
          {{ $t("Back") }}
        </UIButton>
      </div>
      <h1 class="absolute left-1/2 flex -translate-x-1/2 items-center text-lg">
        {{ subtitle ? subtitle : $t("Studio") }}
      </h1>
      <VDropdown v-model:shown="showNotSaved" :triggers="[]" placement="bottom-end" :distance="12">
        <div class="flex justify-end gap-2">
          <UIButton
            v-if="settingsChanged"
            class="!bg-gray-100 !text-gray-800 hover:!bg-gray-200"
            icon-class="!text-base"
            :icon="['fad', 'xmark']"
            @click="emit('discard-changes')"
          >
            {{ $t("Discard changes") }}
          </UIButton>
          <UIButton
            variant="theme"
            icon-class="!text-base"
            icon-placement="right"
            :icon="['fal', 'floppy-disk']"
            :disabled="!settingsChanged || saving"
            @click="emit('save-changes')"
          >
            {{ $t("Save") }}
          </UIButton>
        </div>
        <template #popper="{ hide }">
          <div class="max-w-96">
            <div class="p-4">
              <UIButton
                variant="neutral-light"
                :icon="['fad', 'xmark']"
                class="float-right ml-2 mt-0.5 !h-5"
                @click="hide"
              />
              {{
                //prettier-ignore
                $t("You have unsaved changes. Do you want to save them before leaving?")
              }}
            </div>
          </div>
        </template>
      </VDropdown>
    </div>
  </header>
</template>

<script setup>
defineProps({
  settingsChanged: {
    type: Boolean,
    required: false,
    default: false,
  },
  saving: {
    type: Boolean,
    required: false,
    default: false,
  },
  subtitle: {
    type: String,
    required: false,
    default: "",
  },
  noBackButton: {
    type: Boolean,
    required: false,
    default: false,
  },
});

const showNotSaved = defineModel("showNotSaved", {
  type: Boolean,
  required: false,
  default: false,
});

const emit = defineEmits(["discard-changes", "save-changes", "go-back"]);
</script>
