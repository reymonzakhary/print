<template>
  <div>
    <fieldset class="grid grid-cols-2 gap-4 p-4 rounded-md border bg-white">
      <legend class="px-3 pb-1 mt-1 text-sm font-bold uppercase">Personal Information</legend>
      <div class="">
        <label
          for="gender"
          class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
        >
          Salutation
        </label>
        <div class="flex">
          <label
            for="male"
            class="flex items-center mr-2 text-sm capitalize default checked:font-bold checked:text-theme-500"
            :class="{
              'font-bold text-theme-500': tenant.gender === 'male',
            }"
          >
            <input
              id="male"
              v-model="tenant.gender"
              type="radio"
              name="gender"
              value="male"
              class="mr-1"
              checked
            />
            Mr.
          </label>
          <label
            for="female"
            class="flex items-center mr-2 text-sm capitalize default"
            :class="{
              'font-bold text-theme-500': tenant.gender === 'female',
            }"
          >
            <input
              id="female"
              v-model="tenant.gender"
              type="radio"
              name="gender"
              value="female"
              class="mr-1"
            />
            Ms.
          </label>
          <label
            for="other"
            class="flex items-center mr-2 text-sm capitalize default"
            :class="{
              'font-bold text-theme-500': tenant.gender === 'other',
            }"
          >
            <input
              id="other"
              v-model="tenant.gender"
              type="radio"
              name="gender"
              value="other"
              class="mr-1"
            />
            Other.
          </label>
        </div>
        <ErrorMessage name="gender" as="span" class="text-xs text-red-500" />
      </div>
      <div class="">
        <label class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase">
          User type
        </label>
        <div class="flex relative items-center">
          Reseller / Brandowner
          <div
            class="relative mx-2 w-10 h-4 rounded-full transition duration-200 ease-linear cursor-pointer"
            :class="[
              tenant.supplier && !initSupplierFlag ? 'bg-theme-400' : 'bg-gray-300',
              initSupplierFlag ? 'cursor-not-allowed bg-gray-500' : 'cursor-pointer',
            ]"
          >
            <label
              for="is-supplier"
              class="absolute left-0 mb-2 w-4 h-4 bg-white rounded-full border-2 transition duration-100 ease-linear transform cursor-pointer"
              :class="[
                tenant.supplier && !initSupplierFlag
                  ? 'translate-x-6 border-theme-500'
                  : 'translate-x-0 border-gray-300',
                initSupplierFlag
                  ? 'cursor-not-allowed translate-x-6 border-gray-500'
                  : 'cursor-pointer',
              ]"
            />
            <input
              id="is-supplier"
              v-model="tenant.supplier"
              type="checkbox"
              :disabled="initSupplierFlag"
              name="toggle"
              class="w-full h-full appearance-none active:outline-none focus:outline-none"
              @click="toggleSupplier"
            />
          </div>
          <FontAwesomeIcon :icon="['fal', 'parachute-box']" class="mr-2" />
          Supplier
        </div>
        <ErrorMessage name="gender" as="span" class="text-xs text-red-500" />
      </div>
      <div>
        <label
          for="first_name"
          class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
        >
          First name:
        </label>
        <UIInputText
          v-model="tenant.first_name"
          name="first_name"
          placeholder=""
          required
          autocomplete="off"
        />
      </div>
      <div>
        <label
          for="last_name"
          class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
        >
          Last name:
        </label>
        <UIInputText
          v-model="tenant.last_name"
          name="last_name"
          placeholder=""
          required
          autocomplete="off"
        />
        <ErrorMessage name="last_name" as="span" class="text-xs text-red-500" />
      </div>
      <div>
        <label
          for="phone"
          class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
        >
          dial code + phone:
        </label>
        <UIPhoneInput
          v-model="localPhoneObject"
          name="phone"
          placeholder=""
          required
          :countries="countries"
          autocomplete="off"
        />
        <ErrorMessage name="phone" as="span" class="text-xs text-red-500" />
      </div>
      <div class="">
        <label class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase">
          Is External
        </label>
        <div class="flex relative items-center" v-tooltip="!tenant.supplier && 'Must be supplier'">
          Internal
          <div
            class="relative mx-2 w-10 h-4 rounded-full transition duration-200 ease-linear cursor-pointer"
            :class="[
              tenant.is_external ? 'bg-theme-400' : 'bg-gray-300',
              !tenant.supplier ? 'cursor-not-allowed bg-gray-500' : 'cursor-pointer',
            ]"
          >
            <label
              for="is-external"
              class="absolute left-0 mb-2 w-4 h-4 bg-white rounded-full border-2 transition duration-100 ease-linear transform cursor-pointer"
              :class="[
                tenant.is_external
                  ? 'translate-x-6 border-theme-500'
                  : 'translate-x-0 border-gray-300',
                !tenant.supplier
                  ? 'cursor-not-allowed translate-x-0 border-gray-500'
                  : 'cursor-pointer',
              ]"
            />
            <input
              id="is-external"
              v-model="tenant.is_external"
              type="checkbox"
              :disabled="!tenant.supplier"
              name="toggle"
              class="w-full h-full appearance-none active:outline-none focus:outline-none"
              @click="tenant.is_external = !tenant.is_external"
            />
          </div>
          <!--          <FontAwesomeIcon :icon="['fal', 'parachute-box']" class="mr-2" />-->
          External
        </div>
      </div>
    </fieldset>
  </div>
</template>

<script setup>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { ref, computed } from "vue";

const props = defineProps({
  tenant: Object,
  countries: Array,
  tempPhoneObject: Object,
  initSupplierFlag: Boolean,
  isEditing: Boolean,
});

const emit = defineEmits(["update:tempPhoneObject"]);

// Create a local reactive copy
const localPhoneObject = ref(props.tempPhoneObject);

// Watch for changes and emit to parent
watch(
  localPhoneObject,
  (newValue) => {
    emit("update:tempPhoneObject", newValue);
  },
  { deep: true },
);

const toggleSupplier = () => {
  props.tenant.supplier = !props.tenant.supplier;
  if (!props.tenant.supplier) {
    props.tenant.is_external = false;
  }
};
</script>
