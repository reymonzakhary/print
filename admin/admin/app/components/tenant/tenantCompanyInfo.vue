<template>
  <div>
    <fieldset class="flex flex-col gap-4 p-4 rounded-md bg-white border">
      <legend class="px-3 pb-1 mt-1 text-sm font-bold uppercase">Company Information</legend>
      <div class="col-span-2">
        <label
          for="company_name"
          class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
        >
          Company Name:
        </label>
        <UIInputText
          v-model="tenant.company_name"
          name="company_name"
          placeholder=""
          required
          autocomplete="off"
          @input="
            !props.editing
              ? (tenant.fqdn = tenant.company_name.toLowerCase().replace(/[^a-z0-9]/g, '-'))
              : null
          "
        />
        <ErrorMessage name="company_name" as="span" class="text-xs text-red-500" />
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label
            for="company_coc"
            class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
          >
            coc:
          </label>
          <UIInputText
            v-model="tenant.company_coc"
            name="company_coc"
            type="text"
            placeholder=""
            required
            autocomplete="off"
          />
          <ErrorMessage name="company_coc" as="span" class="text-xs text-red-500" />
        </div>
        <div>
          <label
            for="tax_nr"
            class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
          >
            tax number:
          </label>
          <UIInputText
            v-model="tenant.tax_nr"
            name="tax_nr"
            type="text"
            placeholder=""
            required
            autocomplete="off"
          />
          <ErrorMessage name="tax_nr" as="span" class="text-xs text-red-500" />
        </div>
      </div>
      <div>
        <label
            for="currency"
            class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
        >
          Currency:
        </label>
        <UISelector
            v-model="tenant.currency"
            name="currency"
            :options="currencies"
        />
      </div>
      <div v-if="!isEditing">
        <label
          for="file-upload"
          class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
        >
          Upload File
        </label>
        <div
          class="flex justify-center items-center p-4 w-full h-32 rounded-lg border-2 border-dashed cursor-pointer hover:border-blue-500 focus:border-blue-500"
          @dragover.prevent
          @dragenter.prevent
          @drop.prevent="handleFileDrop"
        >
          <input
            id="file-upload"
            ref="fileInput"
            type="file"
            class="hidden"
            @change="handleFileChange"
          />
          <button
            type="button"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded hover:bg-blue-600 focus:outline-none"
            @click="triggerFileInput"
          >
            Browse File
          </button>
          <div class="ml-4 text-gray-500">
            <span v-if="!tenant.logo">or drag and drop a file here</span>
            <div v-else class="flex gap-2 items-center">
              <span class="text-gray-700">
                {{ tenant.logo.name }} ({{ formatFileSize(tenant.logo.size) }})
              </span>
              <UIButton
                variant="link"
                class="text-xs !text-red-500 underline hover:text-red-600 hover:!bg-red-100"
                @click="clearFile"
              >
                Remove
              </UIButton>
            </div>
          </div>
        </div>
        <ErrorMessage name="logo" as="span" class="text-xs text-red-500" />
      </div>
      <section v-else class="w-full">
        <div class="relative w-3/12 bg-gray-200 mx-auto">
          <img
            :src="tenant.logo + `?ts=${Date.now()}`"
            alt="logo"
            class="object-contain w-full h-20 rounded border"
          />
          <button
            type="button"
            class="absolute -top-2 -right-2 px-1 text-gray-500 bg-white border rounded-full shadow-sm hover:bg-gray-100"
            @click="changeLogo = !changeLogo"
          >
            <font-awesome-icon
              :icon="['fal', !changeLogo ? 'pen' : 'circle-xmark']"
              class="w-4 h-4 text-gray-500 dark:text-gray-100"
            />
          </button>
        </div>
        <div v-if="changeLogo" class="flex flex-col gap-2 mt-2 ml-4 h-full">
          <div
            class="flex justify-center items-center p-4 w-full h-full rounded-lg border-2 border-dashed cursor-pointer hover:border-blue-500 focus:border-blue-500"
            @dragover.prevent
            @dragenter.prevent
            @drop.prevent="handleFileDrop"
          >
            <input
              id="file-upload"
              ref="fileInput"
              type="file"
              class="hidden"
              @change="handleFileChange"
            />
            <button
              type="button"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded hover:bg-blue-600 focus:outline-none"
              @click="triggerFileInput"
            >
              Browse File
            </button>
            <div class="ml-4 text-gray-500">
              <span v-if="!newFile">or drag and drop a file here</span>
              <div v-else class="flex gap-2 items-center">
                <span class="text-gray-700">
                  {{ newFile.name }} ({{ formatFileSize(newFile.size) }})
                </span>
                <UIButton
                  variant="link"
                  class="text-xs !text-red-500 underline hover:text-red-600 hover:!bg-red-100"
                  @click="clearFile"
                >
                  Remove
                </UIButton>
              </div>
            </div>
          </div>
          <ErrorMessage name="logo" as="span" class="text-xs text-red-500" />
        </div>
      </section>
    </fieldset>
  </div>
</template>

<script setup>
import { ref } from "vue";

const props = defineProps({
  tenant: Object,
  isEditing: {
    type: Boolean,
    default: false,
  },
  currencies: Array,
});
const emit = defineEmits(["update:image"]);
const newFile = ref(null);
const changeLogo = ref(false);
const fileInput = ref(null);

const triggerFileInput = () => fileInput.value.click();
const handleFileChange = (e) => {
  if (props.isEditing) {
    newFile.value = e.target.files[0];
    emit("update:image", newFile.value);
  } else {
    props.tenant.logo = e.target.files[0];
  }
};
const handleFileDrop = (e) => {
  if (props.isEditing) {
    newFile.value = e.dataTransfer.files[0];
    emit("update:image", newFile.value);
  } else {
    props.tenant.logo = e.dataTransfer.files[0];
  }
};
function clearFile() {
  if (props.isEditing) {
    newFile.value = null;
    emit("update:image", newFile.value);
  } else {
    props.tenant.logo = null;
  }
  fileInput.value.value = null;
}
function formatFileSize(size) {
  const i = size === 0 ? 0 : Math.floor(Math.log(size) / Math.log(1024));
  return (size / Math.pow(1024, i)).toFixed(2) * 1 + " " + ["B", "KB", "MB", "GB", "TB"][i];
}
</script>

<style scoped></style>
